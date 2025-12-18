from __future__ import annotations
from flask import Blueprint, render_template, redirect, url_for, flash
from flask_login import login_required

def create_blueprint(rebuild_stats_uc):
    bp = Blueprint("stats", __name__, url_prefix="/stats")

    @bp.get("/")
    @login_required
    def page():
        files = {
            "line": "stats/chart_line_temp.png",
            "bar": "stats/chart_bar_avg_temp.png",
            "pie": "stats/chart_pie_sources.png",
        }
        return render_template("stats.html", files=files)

    @bp.post("/rebuild")
    @login_required
    def rebuild():
        rebuild_stats_uc.execute()
        flash("Фикстуры и графики обновлены")
        return redirect(url_for("stats.page"))

    return bp
