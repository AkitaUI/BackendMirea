import requests
from .base import ForecastItem
from .utils import to_iso_date

class WttrJsonScraper:
    name = "wttr.in"

    def fetch(self, city: str) -> list[ForecastItem]:
        url = f"https://wttr.in/{city}?format=j1"
        r = requests.get(url, timeout=10, headers={"User-Agent": "weather-app/1.0"})
        r.raise_for_status()
        data = r.json()

        out: list[ForecastItem] = []
        for day in data.get("weather", [])[:7]:
            # day["date"] обычно "2025-12-18"
            d = to_iso_date(day.get("date", "today"))
            avg_c = None
            try:
                avg_c = int(float(day.get("avgtempC")))
            except Exception:
                pass

            cond = None
            hourly = day.get("hourly") or []
            if hourly:
                desc = hourly[0].get("weatherDesc") or []
                if desc:
                    cond = desc[0].get("value")
            out.append(ForecastItem(date=d, temp_c=avg_c, condition=cond))
        return out
