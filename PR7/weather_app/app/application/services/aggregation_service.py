from __future__ import annotations
from collections import Counter
from ...domain.entities.forecast import ForecastAggItem

class AggregationService:
    def aggregate(self, by_date: dict[str, list[tuple[int | None, str | None, float]]]) -> list[ForecastAggItem]:
        out: list[ForecastAggItem] = []

        for d, items in by_date.items():
            num = 0.0
            den = 0.0
            conds = []

            for t, c, w in items:
                if t is not None:
                    num += float(t) * w
                    den += float(w)
                if c:
                    conds.append(c)

            temp_avg = (num / den) if den > 0 else None
            cond_mode = Counter(conds).most_common(1)[0][0] if conds else None

            out.append(ForecastAggItem(
                date=d,
                temp_avg=temp_avg,
                condition_mode=cond_mode,
                sources_count=len(items),
            ))

        # сортировка по дате
        out.sort(key=lambda x: x.date)
        return out
