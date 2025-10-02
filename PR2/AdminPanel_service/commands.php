<?php
// commands.php

function runCommand(string $cmd): string {
    $output = shell_exec($cmd . ' 2>&1');
    return htmlspecialchars($output ?: 'Ошибка выполнения команды');
}

function getServerInfo(): array {
    return [
        "Дата и время" => runCommand("date"),
        "Текущий пользователь" => runCommand("whoami"),
        "ID пользователя" => runCommand("id"),
        "Запущенные процессы" => runCommand("ps aux --width 40 | head -n 10"),
        "Файлы в текущей директории" => runCommand("ls -l /var/www/html"),
        "Рабочая директория" => runCommand("pwd"),
        "Аптайм системы" => runCommand("uptime"),
        "Сетевая конфигурация" => runCommand("ip addr | head -n 20")
    ];
}
