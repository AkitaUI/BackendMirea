from __future__ import annotations
from typing import Protocol
from ..entities.forecast import ForecastItem

class WeatherScraper(Protocol):
    name: str
    def fetch(self, city: str, *, extra: dict | None = None) -> list[ForecastItem]: ...
