from __future__ import annotations
import os

from flask import current_app
from flask_login import current_user

from ...fixtures import generate_fixtures
from ...stats_charts import build_charts

class RebuildStatsUseCase:
    def __init__(self, session):
        self.s = session

    def execute(self) -> None:
        generate_fixtures(50)
        stats_dir = os.path.join(current_app.root_path, "static", "stats")
        wm = f"@{current_user.username} WeatherApp"
        build_charts(stats_dir, wm)
