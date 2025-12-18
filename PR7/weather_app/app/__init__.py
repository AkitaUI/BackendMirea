from __future__ import annotations

from flask import Flask, render_template
import redis

from .config import Config
from .extensions import db, login_manager, session

def create_app() -> Flask:
    app = Flask(__name__)
    app.config.from_object(Config)

    # Flask extensions
    db.init_app(app)
    login_manager.init_app(app)

    # Redis sessions: Flask-Session ждёт redis client, а не строку
    app.config["SESSION_REDIS"] = redis.Redis.from_url(app.config["SESSION_REDIS"])
    session.init_app(app)

    # Create DB tables
    with app.app_context():
        # Импортируем модели, чтобы Base "увидел" таблицы
        from .infrastructure.db import models as _models  # noqa: F401
        db.create_all()

    # ---------- DI: собираем зависимости ----------
    from .infrastructure.db.forecast_repository_sqlalchemy import ForecastRepositorySQLA
    from .infrastructure.db.user_repository_sqlalchemy import UserRepositorySQLA
    from .infrastructure.db.pdf_repository_sqlalchemy import PdfRepositorySQLA
    from .infrastructure.storage.fs_pdf_storage import FsPdfStorage
    from .infrastructure.http.scraper_factory import ScraperFactory

    from .application.services.aggregation_service import AggregationService
    from .application.use_cases.fetch_weather import FetchWeatherUseCase
    from .application.use_cases.get_weather import GetWeatherUseCase
    from .application.use_cases.update_prefs import UpdatePrefsUseCase
    from .application.use_cases.upload_pdf import UploadPdfUseCase
    from .application.use_cases.rebuild_stats import RebuildStatsUseCase

    forecast_repo = ForecastRepositorySQLA(db.session)
    user_repo = UserRepositorySQLA(db.session)
    pdf_repo = PdfRepositorySQLA(db.session)
    pdf_storage = FsPdfStorage(upload_dir=app.config["UPLOAD_DIR"])
    scrapers = ScraperFactory()
    aggregation = AggregationService()

    fetch_weather_uc = FetchWeatherUseCase(forecast_repo, scrapers, aggregation)
    get_weather_uc = GetWeatherUseCase(forecast_repo)
    update_prefs_uc = UpdatePrefsUseCase(user_repo)
    upload_pdf_uc = UploadPdfUseCase(pdf_repo, pdf_storage)
    rebuild_stats_uc = RebuildStatsUseCase(db.session)  # статистика остаётся на фикстурах (ПР6)

    # ---------- MVC Controllers (Flask blueprints) ----------
    from .interface.web.controllers.auth_controller import create_blueprint as auth_bp
    from .interface.web.controllers.weather_controller import create_blueprint as weather_bp
    from .interface.web.controllers.prefs_controller import create_blueprint as prefs_bp
    from .interface.web.controllers.pdf_controller import create_blueprint as pdf_bp
    from .interface.web.controllers.stats_controller import create_blueprint as stats_bp

    app.register_blueprint(auth_bp(user_repo))
    app.register_blueprint(weather_bp(fetch_weather_uc, get_weather_uc))
    app.register_blueprint(prefs_bp(update_prefs_uc))
    app.register_blueprint(pdf_bp(upload_pdf_uc, pdf_repo))
    app.register_blueprint(stats_bp(rebuild_stats_uc))

    @app.get("/")
    def index():
        return render_template("index.html")

    return app
