from __future__ import annotations
from dataclasses import dataclass

@dataclass
class UserPrefsData:
    theme: str
    lang: str
    home_city: str
