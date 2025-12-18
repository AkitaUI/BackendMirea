from __future__ import annotations
from dataclasses import dataclass

@dataclass(frozen=True)
class ForecastItem:
    date: str               # ISO YYYY-MM-DD
    temp_c: int | None
    condition: str | None

@dataclass(frozen=True)
class ForecastAggItem:
    date: str
    temp_avg: float | None
    condition_mode: str | None
    sources_count: int
