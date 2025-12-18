from __future__ import annotations
from ...domain.ports.pdf_storage import PdfStorage

class UploadPdfUseCase:
    def __init__(self, pdf_repo, storage: PdfStorage):
        self.repo = pdf_repo
        self.storage = storage

    def execute(self, user_id: int, file_storage, note: str | None) -> int:
        original = file_storage.filename
        if not original or not original.lower().endswith(".pdf"):
            raise ValueError("Only PDF allowed")

        stored = self.storage.save_pdf(file_storage, original)
        return self.repo.add_pdf(user_id, original, stored, note)
