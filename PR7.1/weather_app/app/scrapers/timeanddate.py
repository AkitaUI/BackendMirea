import re
import requests
from bs4 import BeautifulSoup
from .base import ForecastItem
from .utils import to_iso_date, c_to_int

class TimeAndDateScraper:
    name = "timeanddate.com"

    # Вариант попроще: требуем от пользователя "country/city-slug" (например: "russia/moscow")
    # Это можно автоматизировать позже через поиск/словарь городов.
    def fetch(self, city_slug: str) -> list[ForecastItem]:
        url = f"https://www.timeanddate.com/weather/{city_slug}/ext"
        r = requests.get(url, timeout=10, headers={"User-Agent": "weather-app/1.0"})
        r.raise_for_status()
        soup = BeautifulSoup(r.text, "lxml")

        # На /ext есть таблица с днями. Обычно строки содержат дату (например "Dec 18") и High/Low.
        table = soup.select_one("table.zebra")
        if not table:
            return []

        out: list[ForecastItem] = []
        for tr in table.select("tr"):
            tds = tr.select("td")
            if len(tds) < 2:
                continue

            raw_date = tds[0].get_text(" ", strip=True)  # "Dec 18"
            iso = to_iso_date(raw_date)

            # Температуры часто в виде "33 / 29 °F" или "1 / -3 °C" зависит от страницы.
            temps = tds[1].get_text(" ", strip=True)
            # берём первое число (high) и используем как temp_c если страница в °C.
            # Если °F — это усложнение; для простоты рекомендуем добавить параметр страницы °C позже.
            m = re.search(r"(-?\d+)", temps)
            temp = c_to_int(m.group(1)) if m else None

            cond = None
            # описание часто в следующей ячейке
            if len(tds) >= 3:
                cond = tds[2].get_text(" ", strip=True) or None

            out.append(ForecastItem(date=iso, temp_c=temp, condition=cond))
        return out[:14]
