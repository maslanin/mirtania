<?php
/**
 * Предупреждение перед переходом по внешней ссылке.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: убран substr($_SERVER['QUERY_STRING'], 4) → $_GET['url'].
 */

$url = isset($_GET['url']) ? (string)$_GET['url'] : '';
$go = isset($_REQUEST['go']) ? $_REQUEST['go'] : 0;

// Если URL пустой — на главную
if ($url === '') {
    header('Location: index.php');
    exit;
}

// Разрешаем только http(s)
if (!preg_match('#^https?://#i', $url)) {
    header('Location: index.php');
    exit;
}

// Если ссылаемся на собственный сайт, то без подтверждения
$server = $_SERVER['SERVER_NAME'] ?? '';
if ($server !== '' && (stripos($url, 'http://' . $server) === 0 || stripos($url, 'https://' . $server) === 0)) {
    $go = 1;
}

if (empty($go)) {
    require_once __DIR__ . '/inc/top.php';
    if (!empty($_SESSION['auth'])) require_once __DIR__ . '/inc/check.php';
    require_once __DIR__ . '/inc/head.php';
    if (!empty($_SESSION['auth'])) require_once __DIR__ . '/inc/hpstring.php';

    $url_esc = htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $server_esc = htmlspecialchars('http://' . $server, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    msg('ВНИМАНИЕ!<br/>Вы собираетесь покинуть сайт и перейти по внешней ссылке:<br/>
    <span style="color:red">' . $url_esc . '</span><br/><br/>
    Администрация нашего ресурса не несёт ответственности за контент постороннего сайта.<br/>
    Рекомендуется не указывать ваши данные, имеющие отношение к <span style="color:red">' . $server_esc . '</span> (имя пользователя, пароль), на сторонних сайтах.<br/>');

    echo '<div class="board">';
    echo '<form action="out.php?url=' . urlencode($url) . '" method="POST">';
    echo '<input type="submit" name="go" value="Перейти по ссылке"/></form></div>';
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    fin();
}

header('Location: ' . $url);
exit;
