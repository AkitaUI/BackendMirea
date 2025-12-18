import os
import io
import pandas as pd
import matplotlib.pyplot as plt

from PIL import Image, ImageDraw, ImageFont

from .extensions import db
from .models import WeatherFixture

def _save_matplotlib_png(fig, out_path: str) -> None:
    buf = io.BytesIO()
    fig.savefig(buf, format="png", dpi=140, bbox_inches="tight")
    plt.close(fig)
    buf.seek(0)
    img = Image.open(buf).convert("RGBA")
    img.save(out_path)

def _add_watermark(path: str, text: str) -> None:
    img = Image.open(path).convert("RGBA")
    w, h = img.size

    overlay = Image.new("RGBA", img.size, (0, 0, 0, 0))
    draw = ImageDraw.Draw(overlay)

    # Шрифт: берём дефолтный (без внешних файлов)
    font = ImageFont.load_default()

    pad = 10
    tw, th = draw.textbbox((0, 0), text, font=font)[2:]
    x = w - tw - pad
    y = h - th - pad

    # Полупрозрачный белый текст
    draw.text((x, y), text, font=font, fill=(255, 255, 255, 120))

    out = Image.alpha_composite(img, overlay)
    out.save(path)

def build_charts(output_dir: str, watermark_text: str) -> dict[str, str]:
    os.makedirs(output_dir, exist_ok=True)

    q = db.session.query(WeatherFixture).all()
    if not q:
        return {}

    df = pd.DataFrame([{
        "city": r.city,
        "date": r.date,
        "temp_c": r.temp_c,
        "humidity": r.humidity,
        "wind_kph": r.wind_kph,
        "pressure_hpa": r.pressure_hpa,
        "source": r.source,
    } for r in q])

    results = {}

    # 1) line: temp by date for top city
    top_city = df["city"].value_counts().index[0]
    d1 = df[df["city"] == top_city].sort_values("date")
    fig = plt.figure()
    plt.plot(d1["date"], d1["temp_c"])
    plt.xticks(rotation=45, ha="right")
    plt.title(f"Temperature trend: {top_city}")
    plt.xlabel("Date")
    plt.ylabel("Temp (C)")
    p1 = os.path.join(output_dir, "chart_line_temp.png")
    _save_matplotlib_png(fig, p1)
    _add_watermark(p1, watermark_text)
    results["line"] = "chart_line_temp.png"

    # 2) bar: avg temp by city (top 6)
    d2 = df.groupby("city")["temp_c"].mean().sort_values(ascending=False).head(6)
    fig = plt.figure()
    plt.bar(d2.index, d2.values)
    plt.xticks(rotation=30, ha="right")
    plt.title("Average temperature by city (top 6)")
    plt.xlabel("City")
    plt.ylabel("Avg Temp (C)")
    p2 = os.path.join(output_dir, "chart_bar_avg_temp.png")
    _save_matplotlib_png(fig, p2)
    _add_watermark(p2, watermark_text)
    results["bar"] = "chart_bar_avg_temp.png"

    # 3) pie: sources distribution
    d3 = df["source"].value_counts()
    fig = plt.figure()
    plt.pie(d3.values, labels=d3.index, autopct="%1.1f%%")
    plt.title("Sources distribution")
    p3 = os.path.join(output_dir, "chart_pie_sources.png")
    _save_matplotlib_png(fig, p3)
    _add_watermark(p3, watermark_text)
    results["pie"] = "chart_pie_sources.png"

    return results
