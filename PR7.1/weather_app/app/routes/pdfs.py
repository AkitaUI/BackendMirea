import os, uuid
from werkzeug.utils import secure_filename
from flask import Blueprint, render_template, request, redirect, url_for, flash, send_from_directory
from flask_login import login_required, current_user
from ..extensions import db
from ..models import PdfFile
from flask import current_app

bp = Blueprint("pdfs", __name__, url_prefix="/pdf")

def allowed(filename: str) -> bool:
    return filename.lower().endswith(".pdf")

@bp.get("/")
@login_required
def list_page():
    files = db.session.query(PdfFile).filter_by(user_id=current_user.id).order_by(PdfFile.uploaded_at.desc()).all()
    return render_template("pdfs.html", files=files)

@bp.post("/upload")
@login_required
def upload():
    f = request.files.get("file")
    note = request.form.get("note")
    if not f or not f.filename:
        flash("Файл не выбран")
        return redirect(url_for("pdfs.list_page"))

    if not allowed(f.filename):
        flash("Разрешены только PDF")
        return redirect(url_for("pdfs.list_page"))

    upload_dir = current_app.config["UPLOAD_DIR"]
    os.makedirs(upload_dir, exist_ok=True)

    original = secure_filename(f.filename)
    stored = f"{uuid.uuid4().hex}.pdf"
    f.save(os.path.join(upload_dir, stored))

    rec = PdfFile(user_id=current_user.id, original_name=original, stored_name=stored, note=note)
    db.session.add(rec)
    db.session.commit()

    flash("PDF загружен")
    return redirect(url_for("pdfs.list_page"))

@bp.get("/<int:file_id>/download")
@login_required
def download(file_id: int):
    rec = db.session.query(PdfFile).filter_by(id=file_id, user_id=current_user.id).first()
    if not rec:
        flash("Файл не найден")
        return redirect(url_for("pdfs.list_page"))

    upload_dir = current_app.config["UPLOAD_DIR"]
    return send_from_directory(upload_dir, rec.stored_name, as_attachment=True, download_name=rec.original_name)
