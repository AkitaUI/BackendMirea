from flask import Blueprint, redirect, url_for, request
from flask_login import login_required, current_user
from ..extensions import db

bp = Blueprint("prefs", __name__, url_prefix="/prefs")

@bp.post("/update")
@login_required
def update():
    theme = request.form.get("theme", "light")
    lang = request.form.get("lang", "ru")
    home_city = request.form.get("home_city", "Moscow")

    current_user.prefs.theme = theme
    current_user.prefs.lang = lang
    current_user.prefs.home_city = home_city
    db.session.commit()

    # параметры используются на сервере при рендере шаблонов
    return redirect(url_for("weather.dashboard"))
