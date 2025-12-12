<?php
// Настройки для сессий в Redis (важно сделать до session_start)
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://redis:6379');

// Стартуем сессию
session_start();

// Счётчик посещений через сессию (чтобы показать, что сессии работают)
if (!isset($_SESSION['visits'])) {
    $_SESSION['visits'] = 0;
}
$_SESSION['visits']++;

// ----- Персонализация через cookie -----

// Значения по умолчанию
$username = $_COOKIE['username'] ?? '';
$theme    = $_COOKIE['theme']    ?? 'light';
$lang     = $_COOKIE['lang']     ?? 'ru';

// Обработка формы персонализации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings_form'])) {
    $username = trim($_POST['username'] ?? '');
    $theme    = $_POST['theme'] ?? 'light';
    $lang     = $_POST['lang']  ?? 'ru';

    // Срок жизни cookie — 30 дней
    $cookieLifetime = time() + 60 * 60 * 24 * 30;

    setcookie('username', $username, $cookieLifetime, '/');
    setcookie('theme', $theme, $cookieLifetime, '/');
    setcookie('lang', $lang, $cookieLifetime, '/');

    // Чтобы значения сразу обновились без перезагрузки, просто обновили переменные выше
}

// ----- Тексты в зависимости от языка -----
function t($key, $lang)
{
    $texts = [
        'ru' => [
            'title'      => 'Личный кабинет',
            'welcome'    => 'Добро пожаловать',
            'no_name'    => 'Гость',
            'settings'   => 'Настройки пользователя',
            'username'   => 'Логин',
            'theme'      => 'Тема оформления',
            'lang'       => 'Язык интерфейса',
            'save'       => 'Сохранить',
            'session'    => 'Информация о сессии',
            'visits'     => 'Количество посещений в текущей сессии',
            'session_id' => 'ID сессии',
            'upload'     => 'Загрузка PDF файлов',
            'select_pdf' => 'Выберите PDF файл',
            'upload_btn' => 'Загрузить',
            'uploaded_list' => 'Список загруженных PDF',
            'no_files'   => 'Файлы ещё не загружены',
        ],
        'en' => [
            'title'      => 'User Dashboard',
            'welcome'    => 'Welcome',
            'no_name'    => 'Guest',
            'settings'   => 'User Settings',
            'username'   => 'Username',
            'theme'      => 'Theme',
            'lang'       => 'Language',
            'save'       => 'Save',
            'session'    => 'Session Info',
            'visits'     => 'Visits in this session',
            'session_id' => 'Session ID',
            'upload'     => 'PDF Upload',
            'select_pdf' => 'Select PDF file',
            'upload_btn' => 'Upload',
            'uploaded_list' => 'Uploaded PDF files',
            'no_files'   => 'No files uploaded yet',
        ],
        'de' => [
            'title'      => 'Benutzerbereich',
            'welcome'    => 'Willkommen',
            'no_name'    => 'Gast',
            'settings'   => 'Benutzereinstellungen',
            'username'   => 'Login',
            'theme'      => 'Theme',
            'lang'       => 'Sprache',
            'save'       => 'Speichern',
            'session'    => 'Sitzungsinformationen',
            'visits'     => 'Besuche in dieser Sitzung',
            'session_id' => 'Sitzungs-ID',
            'upload'     => 'PDF Hochladen',
            'select_pdf' => 'Wählen Sie eine PDF-Datei',
            'upload_btn' => 'Hochladen',
            'uploaded_list' => 'Hochgeladene PDF-Dateien',
            'no_files'   => 'Noch keine Dateien hochgeladen',
        ],
    ];

    if (!isset($texts[$lang])) {
        $lang = 'ru';
    }

    return $texts[$lang][$key] ?? $key;
}

// ----- Загрузка PDF -----
$upload_error = '';
$upload_success = '';

$uploadDir = __DIR__ . '/uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_form'])) {
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        $upload_error = 'Ошибка загрузки файла.';
    } else {
        $fileTmp  = $_FILES['pdf_file']['tmp_name'];
        $fileName = $_FILES['pdf_file']['name'];

        // Проверим MIME тип и расширение
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $fileTmp);
        finfo_close($fileInfo);

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($mimeType !== 'application/pdf' || $extension !== 'pdf') {
            $upload_error = 'Можно загружать только PDF файлы.';
        } else {
            // Очистим имя файла
            $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
            $newName  = time() . '_' . $safeName;
            $destPath = $uploadDir . '/' . $newName;

            if (move_uploaded_file($fileTmp, $destPath)) {
                $upload_success = 'Файл успешно загружен.';
            } else {
                $upload_error = 'Не удалось сохранить файл на сервере.';
            }
        }
    }
}

// Список уже загруженных файлов
$pdfFiles = glob($uploadDir . '/*.pdf');

// Класс темы для body
$bodyClass = 'theme-' . $theme;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars(t('title', $lang)) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <div class="container">
        <header>
            <h1><?= htmlspecialchars(t('title', $lang)) ?></h1>
            <p>
                <?= htmlspecialchars(t('welcome', $lang)) ?>,
                <strong><?= htmlspecialchars($username !== '' ? $username : t('no_name', $lang)) ?></strong>!
            </p>
            <nav>
                <a href="index.php">Главная</a> |
                <a href="stats.php">Статистика (ПР6)</a>
            </nav>
        </header>

        <section class="card">
            <h2><?= htmlspecialchars(t('settings', $lang)) ?></h2>
            <form method="post">
                <input type="hidden" name="settings_form" value="1">
                <div class="form-row">
                    <label for="username"><?= htmlspecialchars(t('username', $lang)) ?>:</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>">
                </div>

                <div class="form-row">
                    <label for="theme"><?= htmlspecialchars(t('theme', $lang)) ?>:</label>
                    <select id="theme" name="theme">
                        <option value="light" <?= $theme === 'light' ? 'selected' : '' ?>>Light</option>
                        <option value="dark" <?= $theme === 'dark' ? 'selected' : '' ?>>Dark</option>
                        <option value="colorblind" <?= $theme === 'colorblind' ? 'selected' : '' ?>>Colorblind</option>
                    </select>
                </div>

                <div class="form-row">
                    <label for="lang"><?= htmlspecialchars(t('lang', $lang)) ?>:</label>
                    <select id="lang" name="lang">
                        <option value="ru" <?= $lang === 'ru' ? 'selected' : '' ?>>Русский</option>
                        <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>English</option>
                        <option value="de" <?= $lang === 'de' ? 'selected' : '' ?>>Deutsch</option>
                    </select>
                </div>

                <button type="submit"><?= htmlspecialchars(t('save', $lang)) ?></button>
            </form>
        </section>

        <section class="card">
            <h2><?= htmlspecialchars(t('session', $lang)) ?></h2>
            <p><?= htmlspecialchars(t('visits', $lang)) ?>: <strong><?= (int)$_SESSION['visits'] ?></strong></p>
            <p><?= htmlspecialchars(t('session_id', $lang)) ?>: <code><?= session_id() ?></code></p>
        </section>

        <section class="card">
            <h2><?= htmlspecialchars(t('upload', $lang)) ?></h2>

            <?php if ($upload_error): ?>
                <div class="message error"><?= htmlspecialchars($upload_error) ?></div>
            <?php endif; ?>

            <?php if ($upload_success): ?>
                <div class="message success"><?= htmlspecialchars($upload_success) ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="upload_form" value="1">
                <div class="form-row">
                    <label for="pdf_file"><?= htmlspecialchars(t('select_pdf', $lang)) ?>:</label>
                    <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf">
                </div>
                <button type="submit"><?= htmlspecialchars(t('upload_btn', $lang)) ?></button>
            </form>

            <h3><?= htmlspecialchars(t('uploaded_list', $lang)) ?></h3>
            <?php if (!$pdfFiles): ?>
                <p><?= htmlspecialchars(t('no_files', $lang)) ?></p>
            <?php else: ?>
                <ul class="file-list">
                    <?php foreach ($pdfFiles as $filePath): ?>
                        <?php
                        $base = basename($filePath);
                        // Пытаемся выделить исходное имя (после timestamp_)
                        $originalName = preg_replace('/^\d+_/', '', $base);
                        ?>
                        <li>
                            <a href="download.php?file=<?= urlencode($base) ?>" target="_blank">
                                <?= htmlspecialchars($originalName) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
