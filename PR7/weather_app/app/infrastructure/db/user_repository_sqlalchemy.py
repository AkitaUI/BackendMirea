from __future__ import annotations
from sqlalchemy.orm import Session

from ...domain.entities.user_prefs import UserPrefsData
from .models import User, UserPrefs

class UserRepositorySQLA:
    def __init__(self, session: Session):
        self.s = session

    def create_user(self, username: str, password: str) -> int:
        if self.s.query(User).filter_by(username=username).first():
            raise ValueError("User already exists")
        u = User(username=username)
        u.set_password(password)
        u.prefs = UserPrefs()
        self.s.add(u)
        self.s.commit()
        return u.id

    def authenticate(self, username: str, password: str) -> int | None:
        u = self.s.query(User).filter_by(username=username).first()
        if not u or not u.check_password(password):
            return None
        return u.id

    def get_user(self, user_id: int):
        return self.s.query(User).filter_by(id=user_id).first()

    def get_prefs(self, user_id: int) -> UserPrefsData:
        u = self.get_user(user_id)
        if not u or not u.prefs:
            return UserPrefsData(theme="light", lang="ru", home_city="Moscow")
        return UserPrefsData(theme=u.prefs.theme, lang=u.prefs.lang, home_city=u.prefs.home_city)

    def update_prefs(self, user_id: int, prefs: UserPrefsData) -> None:
        u = self.get_user(user_id)
        if not u:
            return
        if not u.prefs:
            u.prefs = UserPrefs()
        u.prefs.theme = prefs.theme
        u.prefs.lang = prefs.lang
        u.prefs.home_city = prefs.home_city
        self.s.commit()
