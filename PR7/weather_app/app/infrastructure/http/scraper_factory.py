from __future__ import annotations
from .scrapers.wttr_json import WttrJsonScraper
from .scrapers.timeanddate import TimeAndDateScraper
from .scrapers.meteofor import MeteoforScraper

class ScraperFactory:
    def build_all(self):
        # Возвращаем список стратегий
        return [WttrJsonScraper(), TimeAndDateScraper(), MeteoforScraper()]
