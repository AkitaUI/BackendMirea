<?php
// bootstrap.php — подключать require_once __DIR__.'/bootstrap.php' в начале страниц

// Настройки Redis из окружения
$redisHost = getenv('REDIS_HOST') ?: 'redis';
$redisPort = getenv('REDIS_PORT') ?: 6379;

// Настроим PHP sessions на Redis
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', "tcp://{$redisHost}:{$redisPort}");

// Доп. параметры session cookie
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax'); // или 'Strict'
session_start(); // запускаем сессию
