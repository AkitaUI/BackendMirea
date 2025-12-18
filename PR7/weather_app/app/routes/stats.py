import os
from flask import Blueprint, render_template, current_app, redirect, url_for, flash
from flask_login import login_required, current_user

from ..fixtures import generate_fixtures
from ..stats_charts import build_charts

bp = Blueprint("stats", __name__, url_prefix="/stats")

@bp.get("/")
@login_required
def page():
    stats_dir = os.path.join(current_app.root_path, "static", "stats")
    files = {
        "line": "stats/chart_line_temp.png",
        "bar": "stats/chart_bar_avg_temp.png",
        "pie": "stats/chart_pie_sources.png",
    }
    return render_template("stats.html", files=files)

@bp.post("/rebuild")
@login_required
def rebuild():
    # гарантируем минимум 50 фикстур
    generate_fixtures(50)

    stats_dir = os.path.join(current_app.root_path, "static", "stats")
    wm = f"@{current_user.username} WeatherApp"
    built = build_charts(stats_dir, wm)
    if not built:
        flash("Нет данных для графиков")
    else:
        flash("Фикстуры и графики обновлены")
    return redirect(url_for("stats.page"))
