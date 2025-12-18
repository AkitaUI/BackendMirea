from __future__ import annotations

from flask_login import LoginManager
from flask_session import Session
from sqlalchemy import create_engine
from sqlalchemy.orm import scoped_session, sessionmaker, declarative_base

login_manager = LoginManager()
login_manager.login_view = "auth.login"

session = Session()

Base = declarative_base()
_db_session = None

class DB:
    def __init__(self):
        self.engine = None

    @property
    def session(self):
        global _db_session
        if _db_session is None:
            raise RuntimeError("DB not initialized")
        return _db_session

    def init_app(self, app):
        global _db_session
        self.engine = create_engine(app.config["SQLALCHEMY_DATABASE_URI"], pool_pre_ping=True)
        _db_session = scoped_session(sessionmaker(bind=self.engine))
        Base.metadata.bind = self.engine

    def create_all(self):
        if self.engine is None:
            raise RuntimeError("DB engine not initialized")
        Base.metadata.create_all(bind=self.engine)

db = DB()

@login_manager.user_loader
def load_user(user_id: str):
    # ВАЖНО: импорт модели User теперь из инфраструктуры
    from .infrastructure.db.models import User
    return db.session.get(User, int(user_id))
