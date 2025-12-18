from collections import Counter
from sqlalchemy.orm import aliased
from ..extensions import db
from ..models import Forecast, ForecastSource, ForecastAgg

def rebuild_agg(city: str) -> None:
    # Собираем прогнозы по городу
    S = aliased(ForecastSource)

    rows = (
        db.session.query(Forecast.date, Forecast.temp_c, Forecast.condition, S.weight)
        .join(S, S.id == Forecast.source_id)
        .filter(Forecast.city == city)
        .all()
    )
    if not rows:
        return

    by_date: dict[str, list[tuple[int|None, str|None, float]]] = {}
    for d, t, c, w in rows:
        by_date.setdefault(d, []).append((t, c, float(w or 1.0)))

    # Удалим старые агрегаты для города
    db.session.query(ForecastAgg).filter(ForecastAgg.city == city).delete()

    for d, items in by_date.items():
        # Взвешенное среднее температуры
        num = 0.0
        den = 0.0
        conds = []
        for t, c, w in items:
            if t is not None:
                num += float(t) * w
                den += w
            if c:
                conds.append(c)

        temp_avg = (num / den) if den > 0 else None
        condition_mode = None
        if conds:
            condition_mode = Counter(conds).most_common(1)[0][0]

        agg = ForecastAgg(
            city=city,
            date=d,
            temp_avg=temp_avg,
            condition_mode=condition_mode,
            sources_count=len(items),
        )
        db.session.add(agg)

    db.session.commit()
