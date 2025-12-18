from __future__ import annotations
from ...domain.ports.user_repository import UserRepository
from ...domain.entities.user_prefs import UserPrefsData

class UpdatePrefsUseCase:
    def __init__(self, users: UserRepository):
        self.users = users

    def execute(self, user_id: int, theme: str, lang: str, home_city: str) -> None:
        prefs = UserPrefsData(theme=theme, lang=lang, home_city=home_city)
        self.users.update_prefs(user_id, prefs)
