import os

class Config:
    SECRET_KEY = os.getenv("FLASK_SECRET_KEY", "dev_secret_change_me")

    SQLALCHEMY_DATABASE_URI = os.getenv("DATABASE_URL")
    SQLALCHEMY_TRACK_MODIFICATIONS = False

    SESSION_TYPE = "redis"
    SESSION_REDIS = os.getenv("REDIS_URL")
    SESSION_PERMANENT = False
    SESSION_USE_SIGNER = True
    SESSION_COOKIE_HTTPONLY = True
    SESSION_COOKIE_SAMESITE = "Lax"

    UPLOAD_DIR = os.getenv("UPLOAD_DIR", "/app/uploads")
    MAX_CONTENT_LENGTH = 20 * 1024 * 1024  # 20MB
