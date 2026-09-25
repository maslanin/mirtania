<?php
/**
 * Стартовая инициализация.
 * PHP 8.2-совместимая версия.
 */

date_default_timezone_set('Europe/Moscow');
$time_start = microtime(true);

// Для отладки. В проде выключить!
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Стартуем сессию (если ещё не)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Заголовки — не кешировать
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Content-Type: text/html; charset=utf-8');

// Локаль (на Mac может не быть ru_RU.UTF-8 — подавляем ошибку)
@setlocale(LC_CTYPE, 'ru_RU.UTF-8');

// Время запроса
$t = $_SERVER['REQUEST_TIME'] ?? time();

// Сжатие
require_once __DIR__ . '/gzip.php';

// База
require_once __DIR__ . '/../class/DBC.php';

// Функции (здесь же читается $settings)
require_once __DIR__ . '/func.php';

// Заголовок
if (!empty($title)) {
    $title = $title . ': ' . $settings['name'];
} else {
    $title = $settings['name'];
}

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Cache-Control" content="no-cache" forua="true"/>';

require_once __DIR__ . '/../dis/1.php';

echo '</head><body>';
