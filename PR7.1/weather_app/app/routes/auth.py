from flask import Blueprint, render_template, request, redirect, url_for, flash
from flask_login import login_user, logout_user, login_required
from ..extensions import db
from ..models import User, UserPrefs

bp = Blueprint("auth", __name__, url_prefix="/auth")

@bp.get("/register")
def register_page():
    return render_template("register.html")

@bp.post("/register")
def register():
    username = request.form.get("username", "").strip()
    password = request.form.get("password", "").strip()
    if not username or not password:
        flash("Введите логин и пароль")
        return redirect(url_for("auth.register_page"))

    if db.session.query(User).filter_by(username=username).first():
        flash("Пользователь уже существует")
        return redirect(url_for("auth.register_page"))

    user = User(username=username)
    user.set_password(password)
    user.prefs = UserPrefs()  # дефолтные настройки
    db.session.add(user)
    db.session.commit()

    login_user(user)
    return redirect(url_for("weather.dashboard"))

@bp.get("/login")
def login_page():
    return render_template("login.html")

@bp.post("/login")
def login():
    username = request.form.get("username", "").strip()
    password = request.form.get("password", "").strip()
    user = db.session.query(User).filter_by(username=username).first()
    if not user or not user.check_password(password):
        flash("Неверный логин или пароль")
        return redirect(url_for("auth.login_page"))

    login_user(user)
    return redirect(url_for("weather.dashboard"))

@bp.post("/logout")
@login_required
def logout():
    logout_user()
    return redirect(url_for("index"))
