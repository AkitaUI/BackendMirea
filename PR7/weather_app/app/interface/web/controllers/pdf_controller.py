from __future__ import annotations
from flask import Blueprint, render_template, request, redirect, url_for, flash, send_file
from flask_login import login_required, current_user

def create_blueprint(upload_pdf_uc, pdf_repo):
    bp = Blueprint("pdfs", __name__, url_prefix="/pdf")

    @bp.get("/")
    @login_required
    def list_page():
        files = pdf_repo.list_user_pdfs(current_user.id)
        return render_template("pdfs.html", files=files)

    @bp.post("/upload")
    @login_required
    def upload():
        f = request.files.get("file")
        note = request.form.get("note")
        if not f or not f.filename:
            flash("Файл не выбран")
            return redirect(url_for("pdfs.list_page"))
        try:
            upload_pdf_uc.execute(current_user.id, f, note)
            flash("PDF загружен")
        except ValueError as e:
            flash(str(e))
        return redirect(url_for("pdfs.list_page"))

    @bp.get("/<int:file_id>/download")
    @login_required
    def download(file_id: int):
        rec = pdf_repo.get_user_pdf(current_user.id, file_id)
        if not rec:
            flash("Файл не найден")
            return redirect(url_for("pdfs.list_page"))

        # путь хранит storage внутри use-case, но проще тут: используем UPLOAD_DIR напрямую
        from flask import current_app
        import os
        path = os.path.join(current_app.config["UPLOAD_DIR"], rec.stored_name)
        return send_file(path, as_attachment=True, download_name=rec.original_name)

    return bp
