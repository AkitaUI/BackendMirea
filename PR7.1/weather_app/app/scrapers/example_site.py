import requests
from bs4 import BeautifulSoup
from .base import ForecastItem

class ExampleHtmlSiteScraper:
    name = "example-html-site"

    def fetch(self, city: str) -> list[ForecastItem]:
        # Заготовка: сюда вставляется конкретный URL и CSS-селекторы сайта-источника.
        # На следующем этапе я под ваши выбранные сайты сделаю рабочие селекторы.
        url = f"https://example.com/weather/{city}"
        r = requests.get(url, timeout=10, headers={"User-Agent": "weather-app/1.0"})
        r.raise_for_status()
        soup = BeautifulSoup(r.text, "lxml")

        # TODO: распарсить даты/температуры/состояние
        return []
