<?php
// admin.php
declare(strict_types=1);

require_once __DIR__ . '/commands.php';
require_once __DIR__ . '/utils.php';

$info = getServerInfo();

echo renderHtmlPage($info);
