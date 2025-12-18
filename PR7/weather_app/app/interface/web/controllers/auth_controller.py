from __future__ import annotations
from flask import Blueprint, render_template, request, redirect, url_for, flash
from flask_login import login_user, logout_user, login_required

def create_blueprint(user_repo):
    bp = Blueprint("auth", __name__, url_prefix="/auth")

    @bp.get("/login")
    def login():
        return render_template("login.html")

    @bp.post("/login")
    def login_post():
        username = request.form.get("username", "").strip()
        password = request.form.get("password", "").strip()
        uid = user_repo.authenticate(username, password)
        if not uid:
            flash("Неверный логин или пароль")
            return redirect(url_for("auth.login"))

        user = user_repo.get_user(uid)
        login_user(user)
        return redirect(url_for("weather.dashboard"))

    @bp.get("/register")
    def register():
        return render_template("register.html")

    @bp.post("/register")
    def register_post():
        username = request.form.get("username", "").strip()
        password = request.form.get("password", "").strip()
        if not username or not password:
            flash("Введите логин и пароль")
            return redirect(url_for("auth.register"))
        try:
            uid = user_repo.create_user(username, password)
        except ValueError:
            flash("Пользователь уже существует")
            return redirect(url_for("auth.register"))

        user = user_repo.get_user(uid)
        login_user(user)
        return redirect(url_for("weather.dashboard"))

    @bp.post("/logout")
    @login_required
    def logout():
        logout_user()
        return redirect(url_for("index"))

    return bp
