from __future__ import annotations
from flask import Blueprint, render_template, request, redirect, url_for, flash
from flask_login import login_required, current_user

from app.application.use_cases.fetch_weather import FetchParams

def create_blueprint(fetch_weather_uc, get_weather_uc):
    bp = Blueprint("weather", __name__)

    @bp.get("/dashboard")
    @login_required
    def dashboard():
        prefs = current_user.prefs
        return render_template("dashboard.html", prefs=prefs)

    @bp.get("/weather")
    @login_required
    def weather_page():
        city = request.args.get("city") or current_user.prefs.home_city
        date = request.args.get("date") or None
        data = get_weather_uc.execute(city, date=date)
        return render_template("weather.html", city=city, date=date, rows=data["raw"], agg_rows=data["aggs"])

    @bp.post("/weather/fetch")
    @login_required
    def fetch_weather():
        city = request.form.get("city") or current_user.prefs.home_city
        params = FetchParams(
            timeanddate_slug=request.form.get("timeanddate_slug") or "russia/moscow",
            meteofor_url=request.form.get("meteofor_url") or "https://meteofor.com/weather-moscow-4368/10-days/",
        )
        result = fetch_weather_uc.execute(city, params)

        if result["errors"]:
            for e in result["errors"]:
                flash(f"Источник: ошибка — {e}")
        flash(f"Добавлено записей: {result['added']}")
        return redirect(url_for("weather.weather_page", city=city))

    return bp
