from flask import Blueprint, render_template, redirect, url_for, request, flash
from flask_login import login_required, current_user
from ..extensions import db
from ..models import ForecastSource, Forecast, ForecastAgg
from ..services.aggregate import rebuild_agg

from ..scrapers.wttr_json import WttrJsonScraper
from ..scrapers.timeanddate import TimeAndDateScraper
from ..scrapers.meteofor import MeteoforScraper

bp = Blueprint("weather", __name__)

SCRAPERS = [
    ("wttr", WttrJsonScraper()),
    ("timeanddate", TimeAndDateScraper()),
    ("meteofor", MeteoforScraper()),
]

@bp.get("/weather")
@login_required
def weather_page():
    city = request.args.get("city") or current_user.prefs.home_city
    date = request.args.get("date")  # ISO YYYY-MM-DD

    q = db.session.query(Forecast, ForecastSource).join(ForecastSource, ForecastSource.id == Forecast.source_id)
    q = q.filter(Forecast.city == city)
    if date:
        q = q.filter(Forecast.date == date)

    rows = q.order_by(Forecast.created_at.desc()).limit(200).all()

    agg_q = db.session.query(ForecastAgg).filter(ForecastAgg.city == city)
    if date:
        agg_q = agg_q.filter(ForecastAgg.date == date)
    agg_rows = agg_q.order_by(ForecastAgg.date.asc()).limit(30).all()

    return render_template("weather.html", city=city, date=date, rows=rows, agg_rows=agg_rows)

@bp.post("/weather/fetch")
@login_required
def fetch_weather():
    city = request.form.get("city") or current_user.prefs.home_city

    # Примечание: для timeanddate/meteofor нужны "slug url".
    # Чтобы не ломать UX, мы принимаем доп. поля, но если их нет — используем дефолтные.
    timeanddate_slug = request.form.get("timeanddate_slug") or "russia/moscow"
    meteofor_url = request.form.get("meteofor_url") or "https://meteofor.com/weather-moscow-4368/10-days/"

    for key, scraper in SCRAPERS:
        source = db.session.query(ForecastSource).filter_by(name=scraper.name).first()
        if not source:
            source = ForecastSource(name=scraper.name, weight=1.0)
            db.session.add(source)
            db.session.commit()

        try:
            if key == "wttr":
                items = scraper.fetch(city)
            elif key == "timeanddate":
                items = scraper.fetch(timeanddate_slug)
            else:
                items = scraper.fetch(meteofor_url)
        except Exception as e:
            flash(f"Источник {scraper.name}: ошибка ({e})")
            continue

        for it in items:
            db.session.add(Forecast(city=city, date=it.date, temp_c=it.temp_c, condition=it.condition, source_id=source.id))

        db.session.commit()

    # Перестраиваем агрегат
    rebuild_agg(city)

    flash("Обновление выполнено: данные добавлены + агрегат обновлён")
    return redirect(url_for("weather.weather_page", city=city))
