from __future__ import annotations
from datetime import date, datetime
from dateutil import parser

def to_iso_date(value: str, *, today: date | None = None) -> str:
    today = today or date.today()
    v = (value or "").strip().lower()
    if v in {"today", "сегодня"}:
        return today.isoformat()
    if v in {"tomorrow", "завтра"}:
        return (today.fromordinal(today.toordinal() + 1)).isoformat()
    dt = parser.parse(value, default=datetime(today.year, 1, 1))
    return dt.date().isoformat()

def c_to_int(temp: str) -> int | None:
    if temp is None:
        return None
    t = temp.strip().replace("°", "").replace("℃", "").replace("C", "").replace("c", "")
    t = t.replace("+", "")
    try:
        return int(float(t))
    except Exception:
        return None
