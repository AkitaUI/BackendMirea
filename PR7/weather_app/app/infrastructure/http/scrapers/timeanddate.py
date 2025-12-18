from __future__ import annotations
import re
import requests
from bs4 import BeautifulSoup
from .utils import to_iso_date, c_to_int
from ....domain.entities.forecast import ForecastItem

class TimeAndDateScraper:
    name = "timeanddate.com"

    def fetch(self, city: str, *, extra: dict | None = None) -> list[ForecastItem]:
        # extra может содержать slug: {"slug": "russia/moscow"}
        slug = (extra or {}).get("slug")
        if not slug:
            return []

        url = f"https://www.timeanddate.com/weather/{slug}/ext"
        r = requests.get(url, timeout=10, headers={"User-Agent": "weather-app/1.0"})
        r.raise_for_status()
        soup = BeautifulSoup(r.text, "lxml")
        table = soup.select_one("table.zebra")
        if not table:
            return []

        out: list[ForecastItem] = []
        for tr in table.select("tr"):
            tds = tr.select("td")
            if len(tds) < 2:
                continue

            raw_date = tds[0].get_text(" ", strip=True)
            iso = to_iso_date(raw_date)

            temps = tds[1].get_text(" ", strip=True)
            m = re.search(r"(-?\d+)", temps)
            temp = c_to_int(m.group(1)) if m else None

            cond = None
            if len(tds) >= 3:
                cond = tds[2].get_text(" ", strip=True) or None

            out.append(ForecastItem(date=iso, temp_c=temp, condition=cond))
        return out[:14]
