from dataclasses import dataclass
from typing import Protocol

@dataclass
class ForecastItem:
    date: str         # YYYY-MM-DD
    temp_c: int | None
    condition: str | None

class WeatherScraper(Protocol):
    name: str
    def fetch(self, city: str) -> list[ForecastItem]: ...
