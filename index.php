<?php
/**
 * Главная страница (вход в игру).
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';

// НЕ сбрасываем $_SESSION['auth'] — иначе авторизованный игрок вылетит.

// Сколько всего пользователей зарегистрировано
$res = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `autoreg` = 0;");
$q = $res ? $res->fetch_assoc() : ['c' => 0];
$countreg = (int)$q['c'];

// Проверка кук
if (!empty($_COOKIE['id']) && !empty($_COOKIE['hash']) && !empty($_COOKIE['lgn'])) {
    $id    = (int)$_COOKIE['id'];
    $hash  = (string)$_COOKIE['hash'];
    $login = ekr($_COOKIE['lgn']);

    $res = $db->query("SELECT `login`, `pass` FROM `users` WHERE `id` = {$id} AND `login` = '{$login}' LIMIT 1;");
    $auth = $res ? $res->fetch_assoc() : null;

    if ($auth && !empty($auth['pass']) && $auth['pass'] === $hash) {
        $_SESSION['auth'] = 1;
    } else {
        session_unset();
        session_destroy();
        setcookie('id',   '', time() - 3600, '/');
        setcookie('hash', '', time() - 3600, '/');
        setcookie('lgn',  '', time() - 3600, '/');
    }
}

if (!empty($_SESSION['auth'])) {
    require_once __DIR__ . '/inc/check.php';
}

echo '<div class="verx"><img src="pic/logo.png" alt=""/></div>';

if (!empty($_SESSION['auth'])) {
    header('Location: loc.php');
    fin();
}

require_once __DIR__ . '/inc/head.php';

msg2('Это многопользовательская игра, в которой одновременно могут участвовать несколько тысяч персонажей, контролируемых людьми. 
Средневековый, сказочный мир, наполненный чудесами и опасностями, монстрами и героями откроется для Вас.');

echo '<div class="board">';
echo '<form action="start.php?auth" method="POST">
<input type="text" name="login" placeholder="Логин"/><br/>
<input type="password" name="pass" placeholder="Пароль"/><br/>
<input type="submit" value="Войти"/></form>
</div>';

if (!empty($settings['reg']) && $settings['reg'] == 1) {
    knopka('start.php?start', 'Начать новую игру');
}
knopka('pass.php', 'Восстановление пароля');
knopka('news.php', DateNews());
knopka('lib.php', 'Библиотека');
echo '<div class="board2"><small>Зарегистрировано ' . $countreg . ' игроков</small></div>';

fin();
