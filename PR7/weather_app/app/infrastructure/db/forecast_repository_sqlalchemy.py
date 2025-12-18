from __future__ import annotations
from collections import defaultdict
from sqlalchemy.orm import Session

from ...domain.entities.forecast import ForecastItem, ForecastAggItem
from .models import Forecast, ForecastSource, ForecastAgg

class ForecastRepositorySQLA:
    def __init__(self, session: Session):
        self.s = session

    def get_or_create_source(self, name: str, weight: float = 1.0) -> int:
        src = self.s.query(ForecastSource).filter_by(name=name).first()
        if not src:
            src = ForecastSource(name=name, weight=weight)
            self.s.add(src)
            self.s.commit()
        return src.id

    def add_forecasts(self, city: str, source_id: int, items: list[ForecastItem]) -> int:
        count = 0
        for it in items:
            self.s.add(Forecast(
                city=city,
                date=it.date,
                temp_c=it.temp_c,
                condition=it.condition,
                source_id=source_id
            ))
            count += 1
        self.s.commit()
        return count

    def list_forecasts(self, city: str, date: str | None = None) -> list[dict]:
        q = self.s.query(Forecast, ForecastSource).join(ForecastSource, ForecastSource.id == Forecast.source_id)
        q = q.filter(Forecast.city == city)
        if date:
            q = q.filter(Forecast.date == date)
        q = q.order_by(Forecast.created_at.desc()).limit(200)

        out = []
        for f, src in q.all():
            out.append({
                "source": src.name,
                "date": f.date,
                "temp_c": f.temp_c,
                "condition": f.condition,
                "created_at": f.created_at,
            })
        return out

    def save_aggregates(self, city: str, aggs: list[ForecastAggItem]) -> None:
        self.s.query(ForecastAgg).filter(ForecastAgg.city == city).delete()
        for a in aggs:
            self.s.add(ForecastAgg(
                city=city,
                date=a.date,
                temp_avg=a.temp_avg,
                condition_mode=a.condition_mode,
                sources_count=a.sources_count
            ))
        self.s.commit()

    def list_aggregates(self, city: str, date: str | None = None) -> list[ForecastAggItem]:
        q = self.s.query(ForecastAgg).filter(ForecastAgg.city == city)
        if date:
            q = q.filter(ForecastAgg.date == date)
        q = q.order_by(ForecastAgg.date.asc()).limit(30)
        return [
            ForecastAggItem(
                date=r.date,
                temp_avg=r.temp_avg,
                condition_mode=r.condition_mode,
                sources_count=r.sources_count
            )
            for r in q.all()
        ]

    def _raw_for_agg(self, city: str) -> dict[str, list[tuple[int | None, str | None, float]]]:
        q = (
            self.s.query(Forecast.date, Forecast.temp_c, Forecast.condition, ForecastSource.weight)
            .join(ForecastSource, ForecastSource.id == Forecast.source_id)
            .filter(Forecast.city == city)
        )
        by_date = defaultdict(list)
        for d, t, c, w in q.all():
            by_date[d].append((t, c, float(w or 1.0)))
        return by_date
