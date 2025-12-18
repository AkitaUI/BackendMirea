from __future__ import annotations
from flask import Blueprint, request, redirect, url_for
from flask_login import login_required, current_user

def create_blueprint(update_prefs_uc):
    bp = Blueprint("prefs", __name__, url_prefix="/prefs")

    @bp.post("/update")
    @login_required
    def update():
        theme = request.form.get("theme", "light")
        lang = request.form.get("lang", "ru")
        home_city = request.form.get("home_city", "Moscow")
        update_prefs_uc.execute(current_user.id, theme, lang, home_city)
        return redirect(url_for("weather.dashboard"))

    return bp
