from __future__ import annotations
import requests
from bs4 import BeautifulSoup
from .utils import to_iso_date, c_to_int
from ....domain.entities.forecast import ForecastItem

class MeteoforScraper:
    name = "meteofor.com"

    def fetch(self, city: str, *, extra: dict | None = None) -> list[ForecastItem]:
        # extra содержит url: {"url": "https://meteofor.com/weather-moscow-4368/10-days/"}
        url = (extra or {}).get("url")
        if not url:
            return []

        r = requests.get(url, timeout=10, headers={"User-Agent": "weather-app/1.0"})
        r.raise_for_status()
        soup = BeautifulSoup(r.text, "lxml")

        out: list[ForecastItem] = []
        cards = soup.select("[class*=day], [class*=forecast]")
        for c in cards:
            date_el = c.select_one("time") or c.select_one("[class*=date]")
            temp_el = c.select_one("[class*=temp]")
            if not date_el or not temp_el:
                continue

            iso = to_iso_date(date_el.get_text(" ", strip=True))
            temp = c_to_int(temp_el.get_text(" ", strip=True))
            cond_el = c.select_one("[class*=desc], [class*=text], [class*=phrase]")
            cond = cond_el.get_text(" ", strip=True) if cond_el else None

            out.append(ForecastItem(date=iso, temp_c=temp, condition=cond))

        # уникальные даты
        uniq, seen = [], set()
        for it in out:
            if it.date in seen:
                continue
            seen.add(it.date)
            uniq.append(it)
        return uniq[:10]
