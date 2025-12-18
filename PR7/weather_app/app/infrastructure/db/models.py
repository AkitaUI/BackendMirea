from __future__ import annotations
from datetime import datetime

from flask_login import UserMixin
from sqlalchemy import String, Integer, DateTime, Text, ForeignKey, UniqueConstraint, Float
from sqlalchemy.orm import Mapped, mapped_column, relationship
from werkzeug.security import generate_password_hash, check_password_hash

from ...extensions import Base

class User(Base, UserMixin):
    __tablename__ = "users"
    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    username: Mapped[str] = mapped_column(String(80), unique=True, nullable=False)
    password_hash: Mapped[str] = mapped_column(String(255), nullable=False)

    prefs = relationship("UserPrefs", back_populates="user", uselist=False, cascade="all, delete-orphan")

    def set_password(self, password: str) -> None:
        self.password_hash = generate_password_hash(password)

    def check_password(self, password: str) -> bool:
        return check_password_hash(self.password_hash, password)

class UserPrefs(Base):
    __tablename__ = "user_prefs"
    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"), unique=True, nullable=False)
    theme: Mapped[str] = mapped_column(String(20), default="light")
    lang: Mapped[str] = mapped_column(String(10), default="ru")
    home_city: Mapped[str] = mapped_column(String(100), default="Moscow")
    user = relationship("User", back_populates="prefs")

class ForecastSource(Base):
    __tablename__ = "forecast_sources"
    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    name: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    weight: Mapped[float] = mapped_column(Float, default=1.0)

class Forecast(Base):
    __tablename__ = "forecasts"
    __table_args__ = (UniqueConstraint("city", "date", "source_id", "created_at", name="uq_city_date_source_created"),)
    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    city: Mapped[str] = mapped_column(String(100), nullable=False)
    date: Mapped[str] = mapped_column(String(20), nullable=False)  # ISO YYYY-MM-DD
    temp_c: Mapped[int] = mapped_column(Integer, nullable=True)
    condition: Mapped[str] = mapped_column(String(200), nullable=True)
    source_id: Mapped[int] = mapped_column(ForeignKey("forecast_sources.id"), nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)

class ForecastAgg(Base):
    __tablename__ = "forecast_agg"
    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    city: Mapped[str] = mapped_column(String(100), nullable=False)
    date: Mapped[str] = mapped_column(String(20), nullable=False)
    temp_avg: Mapped[float] = mapped_column(Float, nullable=True)
    condition_mode: Mapped[str] = mapped_column(String(200), nullable=True)
    sources_count: Mapped[int] = mapped_column(Integer, default=0)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)

class PdfFile(Base):
    __tablename__ = "pdf_files"
    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    user_id: Mapped[int] = mapped_column(ForeignKey("users.id"), nullable=False)
    original_name: Mapped[str] = mapped_column(String(255), nullable=False)
    stored_name: Mapped[str] = mapped_column(String(255), nullable=False)
    uploaded_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
    note: Mapped[str] = mapped_column(Text, nullable=True)

# ПР6 фикстуры
class WeatherFixture(Base):
    __tablename__ = "weather_fixtures"
    id: Mapped[int] = mapped_column(Integer, primary_key=True)
    city: Mapped[str] = mapped_column(String(100), nullable=False)
    date: Mapped[str] = mapped_column(String(20), nullable=False)
    temp_c: Mapped[float] = mapped_column(Float, nullable=False)
    humidity: Mapped[int] = mapped_column(Integer, nullable=False)
    wind_kph: Mapped[float] = mapped_column(Float, nullable=False)
    pressure_hpa: Mapped[int] = mapped_column(Integer, nullable=False)
    source: Mapped[str] = mapped_column(String(100), nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
