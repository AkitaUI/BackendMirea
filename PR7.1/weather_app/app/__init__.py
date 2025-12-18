from flask import Flask
from .config import Config
from .extensions import db, login_manager, session
from .models import User  # noqa

def create_app() -> Flask:
    app = Flask(__name__)
    app.config.from_object(Config)

    import redis
    app.config["SESSION_REDIS"] = redis.Redis.from_url(app.config["SESSION_REDIS"])


    db.init_app(app)
    login_manager.init_app(app)
    session.init_app(app)

    from .routes.auth import bp as auth_bp
    from .routes.weather import bp as weather_bp
    from .routes.prefs import bp as prefs_bp
    from .routes.pdfs import bp as pdfs_bp

    from .routes.stats import bp as stats_bp
    app.register_blueprint(stats_bp)

    app.register_blueprint(auth_bp)
    app.register_blueprint(weather_bp)
    app.register_blueprint(prefs_bp)
    app.register_blueprint(pdfs_bp)

    with app.app_context():
        db.create_all()

    @app.get("/")
    def index():
        from flask import render_template
        return render_template("index.html")

    return app
