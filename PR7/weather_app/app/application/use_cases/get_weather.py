from __future__ import annotations
from ...domain.ports.forecast_repository import ForecastRepository

class GetWeatherUseCase:
    def __init__(self, repo: ForecastRepository):
        self.repo = repo

    def execute(self, city: str, date: str | None = None) -> dict:
        raw = self.repo.list_forecasts(city, date)
        aggs = self.repo.list_aggregates(city, date)
        return {"raw": raw, "aggs": aggs}
