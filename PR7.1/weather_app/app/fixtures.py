from faker import Faker
from random import randint, uniform, choice
from datetime import date, timedelta

from .extensions import db
from .models import WeatherFixture

fake = Faker()

SOURCES = ["wttr.in", "gismeteo", "yandex-weather", "open-meteo-html"]

def generate_fixtures(n: int = 50) -> int:
    cities = [fake.city() for _ in range(8)]
    base = date.today()

    rows = []
    for _ in range(n):
        d = base - timedelta(days=randint(0, 14))
        rows.append(
            WeatherFixture(
                city=choice(cities),
                date=d.isoformat(),
                temp_c=round(uniform(-15, 35), 1),
                humidity=randint(10, 100),
                wind_kph=round(uniform(0, 45), 1),
                pressure_hpa=randint(960, 1045),
                source=choice(SOURCES),
            )
        )

    db.session.add_all(rows)
    db.session.commit()
    return n
