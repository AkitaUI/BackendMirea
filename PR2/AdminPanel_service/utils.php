<?php
// utils.php

function renderHtmlPage(array $info): string {
    $html = "<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        h1 { color: #333; }
        .card {
            background: #fff; padding: 15px; margin-bottom: 20px;
            border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        pre {
            background: #222; color: #0f0;
            padding: 10px; border-radius: 5px; overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Информационно-административная панель</h1>
";

    foreach ($info as $title => $output) {
        $html .= "<div class='card'>
            <h2>{$title}</h2>
            <pre>{$output}</pre>
        </div>";
    }

    $html .= "</body></html>";
    return $html;
}
