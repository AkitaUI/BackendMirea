from __future__ import annotations
from sqlalchemy.orm import Session
from .models import PdfFile

class PdfRepositorySQLA:
    def __init__(self, session: Session):
        self.s = session

    def add_pdf(self, user_id: int, original_name: str, stored_name: str, note: str | None) -> int:
        rec = PdfFile(user_id=user_id, original_name=original_name, stored_name=stored_name, note=note)
        self.s.add(rec)
        self.s.commit()
        return rec.id

    def list_user_pdfs(self, user_id: int):
        return self.s.query(PdfFile).filter_by(user_id=user_id).order_by(PdfFile.uploaded_at.desc()).all()

    def get_user_pdf(self, user_id: int, file_id: int):
        return self.s.query(PdfFile).filter_by(user_id=user_id, id=file_id).first()
