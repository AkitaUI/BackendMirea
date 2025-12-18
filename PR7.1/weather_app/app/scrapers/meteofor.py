import requests
from bs4 import BeautifulSoup
from .base import ForecastItem
from .utils import to_iso_date, c_to_int

class MeteoforScraper:
    name = "meteofor.com"

    # meteofor использует id города в URL, например:
    # https://meteofor.com/weather-moscow-4368/10-days/
    def fetch(self, city_url: str) -> list[ForecastItem]:
        r = requests.get(city_url, timeout=10, headers={"User-Agent": "weather-app/1.0"})
        r.raise_for_status()
        soup = BeautifulSoup(r.text, "lxml")

        out: list[ForecastItem] = []

        # Структура у meteofor меняется, но обычно есть блоки/карточки дней
        # Ищем элементы, где есть дата и температура.
        cards = soup.select("[class*=day], [class*=forecast]")  # мягкий селектор
        for c in cards:
            date_el = c.select_one("time") or c.select_one("[class*=date]")
            temp_el = c.select_one("[class*=temp]")

            if not date_el or not temp_el:
                continue

            raw_date = date_el.get_text(" ", strip=True)
            raw_temp = temp_el.get_text(" ", strip=True)

            iso = to_iso_date(raw_date)
            temp = c_to_int(raw_temp)
            cond_el = c.select_one("[class*=desc], [class*=text], [class*=phrase]")
            cond = cond_el.get_text(" ", strip=True) if cond_el else None

            out.append(ForecastItem(date=iso, temp_c=temp, condition=cond))

        # если селекторы дали шум — берём первые 10 уникальных дат
        uniq = []
        seen = set()
        for it in out:
            if it.date in seen:
                continue
            seen.add(it.date)
            uniq.append(it)
        return uniq[:10]
