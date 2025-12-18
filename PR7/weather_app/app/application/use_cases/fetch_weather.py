from __future__ import annotations
from dataclasses import dataclass

from ...domain.ports.forecast_repository import ForecastRepository
from ...domain.entities.forecast import ForecastItem
from ..services.aggregation_service import AggregationService

@dataclass
class FetchParams:
    timeanddate_slug: str | None = None
    meteofor_url: str | None = None

class FetchWeatherUseCase:
    def __init__(self, repo: ForecastRepository, scrapers_factory, aggregation: AggregationService):
        self.repo = repo
        self.factory = scrapers_factory
        self.aggregation = aggregation

    def execute(self, city: str, params: FetchParams) -> dict:
        scrapers = self.factory.build_all()
        added_total = 0
        errors: list[str] = []

        for sc in scrapers:
            source_id = self.repo.get_or_create_source(sc.name, weight=1.0)

            extra = None
            if sc.name == "timeanddate.com":
                extra = {"slug": params.timeanddate_slug}
            elif sc.name == "meteofor.com":
                extra = {"url": params.meteofor_url}

            try:
                items: list[ForecastItem] = sc.fetch(city, extra=extra)
            except Exception as e:
                errors.append(f"{sc.name}: {e}")
                continue

            if not items:
                continue

            added_total += self.repo.add_forecasts(city, source_id, items)

        # агрегация строится из raw данных (через приватный метод repo impl)
        by_date = getattr(self.repo, "_raw_for_agg")(city)
        aggs = self.aggregation.aggregate(by_date)
        self.repo.save_aggregates(city, aggs)

        return {"added": added_total, "errors": errors}
