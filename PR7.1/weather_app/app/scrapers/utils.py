from __future__ import annotations
from datetime import date, datetime
from dateutil import parser

def to_iso_date(value: str, *, today: date | None = None) -> str:
    """
    Приводит дату из разных источников к ISO YYYY-MM-DD.
    Поддерживает варианты: "Dec 18", "18 Dec", "2025-12-18", "today", и т.п.
    """
    today = today or date.today()

    v = (value or "").strip().lower()
    if v in {"today", "сегодня"}:
        return today.isoformat()
    if v in {"tomorrow", "завтра"}:
        return (today.fromordinal(today.toordinal() + 1)).isoformat()

    # dateutil сам парсит много форматов; year подставим текущий, если отсутствует
    dt = parser.parse(value, default=datetime(today.year, 1, 1))
    return dt.date().isoformat()

def c_to_int(temp: str) -> int | None:
    """
    Преобразует "+3°C", "-10", "3" -> int.
    """
    if temp is None:
        return None
    t = temp.strip().replace("°", "").replace("c", "").replace("C", "")
    t = t.replace("°C", "").replace("℃", "")
    t = t.replace("+", "")
    try:
        return int(float(t))
    except Exception:
        return None
