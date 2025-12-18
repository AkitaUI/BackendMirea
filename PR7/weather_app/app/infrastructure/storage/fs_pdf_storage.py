from __future__ import annotations
import os, uuid
from werkzeug.utils import secure_filename

class FsPdfStorage:
    def __init__(self, upload_dir: str):
        self.upload_dir = upload_dir

    def save_pdf(self, stream, original_name: str) -> str:
        os.makedirs(self.upload_dir, exist_ok=True)
        _ = secure_filename(original_name)  # имя для download_name хранится в БД
        stored = f"{uuid.uuid4().hex}.pdf"
        path = os.path.join(self.upload_dir, stored)
        stream.save(path)  # stream это FileStorage из Flask
        return stored

    def get_pdf_path(self, stored_name: str) -> str:
        return os.path.join(self.upload_dir, stored_name)
