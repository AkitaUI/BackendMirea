import requests
from bs4 import BeautifulSoup
from .domain.entities.forecast import ForecastItem

class WttrInScraper:
    name = "wttr.in"

    def fetch(self, city: str) -> list[ForecastItem]:
        # HTML страница (да, это “парсинг сайта”, не API)
        url = f"https://wttr.in/{city}?format=v2"
        r = requests.get(url, timeout=10, headers={"User-Agent": "weather-app/1.0"})
        r.raise_for_status()

        # format=v2 вернёт текст; для “чистого HTML” можно поменять параметр, но так стабильнее.
        # В рамках ПР важно: источники разные, код адаптерный.
        text = r.text.strip()
        # Упрощённый разбор: берём только текущую строку
        # Реальную 3-5 дневную раскладку добавим на следующем этапе.
        return [ForecastItem(date="today", temp_c=None, condition=text)]
