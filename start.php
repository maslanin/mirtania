<?php
/**
 * Авторизация, регистрация, выход.
 * PHP 8.2-совместимая версия.
 */

$title = 'Работа с аккаунтом';
require_once __DIR__ . '/inc/top.php';

if (!empty($_SESSION['auth'])) {
    require_once __DIR__ . '/inc/check.php';
}
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';

$login = isset($_REQUEST['login']) ? $_REQUEST['login'] : '';
$pass  = isset($_REQUEST['pass'])  ? $_REQUEST['pass']  : '';
$ip    = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
$ip    = ekr($ip);
$soft  = isset($_SERVER['HTTP_USER_AGENT']) ? ekr($_SERVER['HTTP_USER_AGENT']) : '';
$host  = isset($_SERVER['HTTP_X_OPERAMINI_PHONE']) ? ekr($_SERVER['HTTP_X_OPERAMINI_PHONE']) : '';

/**
 * Генерация пароля.
 */
function generatePassword($length = 4, $strength = 4)
{
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength >= 1) $consonants .= 'BDGHJLMNPQRSTVWXZ';
    if ($strength >= 2) $vowels .= 'AEUY';
    if ($strength >= 4) $consonants .= '23456789';
    if ($strength >= 8) $vowels .= '@#$%';

    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++) {
        if ($alt == 1) {
            $password .= $consonants[random_int(0, strlen($consonants) - 1)];
            $alt = 0;
        } else {
            $password .= $vowels[random_int(0, strlen($vowels) - 1)];
            $alt = 1;
        }
    }
    return $password;
}

// --- Авторизация ---
if (isset($_REQUEST['auth'])) {
    if (!empty($_SESSION['auth'])) {
        msg('Вы уже авторизованы.<br/><a href="loc.php">В игру</a>', 1);
    }

    if (empty($login) || preg_match('/[^a-zA-Z0-9_а-яА-ЯёЁ]/u', $login) || empty($pass) || preg_match('/[^a-zA-Z0-9]/', $pass)) {
        msg2('Неверный логин или пароль!');
        knopka('index.php', 'На главную');
        fin();
    }

    $login_esc = $db->real_escape_string($login);
    $q = $db->query("SELECT `pass`, `id`, `login`, `soft`, `host`, `ip` FROM `users` WHERE `login` = '{$login_esc}' LIMIT 1;");
    if ($q === false || $q->num_rows === 0) {
        msg2('Такой логин не зарегистрирован!');
        knopka('index.php', 'На главную');
        fin();
    }
    $f = $q->fetch_assoc();

    if ($f['pass'] !== md5($pass)) {
        msg2('Неверный пароль!');
        knopka('index.php', 'На главную');
        fin();
    }

    setcookie('id',   '', time() - 3600, '/');
    setcookie('hash', '', time() - 3600, '/');
    setcookie('lgn',  '', time() - 3600, '/');
    setcookie('id',   (string)$f['id'], time() + 86400 * 365, '/');
    setcookie('hash', md5($pass),       time() + 86400 * 365, '/');
    setcookie('lgn',  $login,           time() + 86400 * 365, '/');

    if ($f['soft'] !== $soft || $f['ip'] !== $ip) {
        $q = $db->query("SELECT * FROM `ipsoft` WHERE `ip` = '{$ip}' AND `login` <> '{$f['login']}' GROUP BY `login` ORDER BY `date` DESC;");
        if ($q && $q->num_rows > 0) {
            $logins = '';
            while ($a = $q->fetch_assoc()) {
                $logins .= '<a href="infa.php?mod=uzinfa&lgn=' . $a['login'] . '">' . $a['login'] . '</a> - ' . date('d.m.Y H:i', (int)$a['date']) . '<br/>';
            }
            $log = 'У игрока ' . $f['login'] . ' совпал IP <b>' . $ip . '</b> со следующими персонажами:<br/>' . $logins;
            $db->query("INSERT INTO `log_ipsoft` VALUES (0, '{$f['login']}', '{$t}', '{$log}');");
        }
        $sid = session_id();
        $db->query("INSERT INTO `ipsoft` VALUES (0, '{$f['login']}', '{$t}', '{$ip}', '{$host}', '{$soft}', '{$sid}');");
        $db->query("UPDATE `users` SET `soft` = '{$soft}', `host` = '{$host}', `ip` = '{$ip}', `session` = '{$sid}' WHERE `login` = '{$f['login']}' LIMIT 1;");
    }

    $_SESSION['auth'] = 1;
    header('Location: loc.php');
    fin();
}

// --- Выход ---
if (isset($_REQUEST['exit']) && !empty($_SESSION['auth'])) {
    if (empty($_REQUEST['ok'])) {
        msg2('Вы действительно хотите выйти из игры?');
        knopka('start.php?exit&ok=1', 'Выйти из игры');
        knopka('javascript:history.go(-1)', 'Вернуться');
        fin();
    }
    setcookie('id',   '', time() - 3600, '/');
    setcookie('hash', '', time() - 3600, '/');
    setcookie('lgn',  '', time() - 3600, '/');
    session_unset();
    session_destroy();
    msg2('Вы успешно покинули игру!');
    knopka('index.php', 'На главную');
    fin();
}

// --- Регистрация (создание временного персонажа) ---
if (isset($_REQUEST['start'])) {
    if (!empty($_SESSION['auth'])) {
        msg('Вы уже авторизованы.<br/><a href="loc.php">В игру</a>', 1);
    }
    if (empty($settings['reg'])) {
        msg2('Регистрация временно отключена');
        knopka('index.php', 'На главную');
        fin();
    }

    $r = isset($_REQUEST['r']) ? (int)$_REQUEST['r'] : 1;
    $q = $db->query("SELECT `id` FROM `users` WHERE `id` = {$r} LIMIT 1;");
    if ($q === false || $q->num_rows === 0) $r = 1;

    $pass = generatePassword();
    $md5pass = md5($pass);
    $soft = isset($_SERVER['HTTP_USER_AGENT']) ? ekr($_SERVER['HTTP_USER_AGENT']) : '';
    if (empty($soft)) {
        msg2('Можно регистрироваться только с реальных браузеров, регистрация с помощью скриптов запрещена!', 1);
    }
    $host = isset($_SERVER['HTTP_X_OPERAMINI_PHONE']) ? ekr($_SERVER['HTTP_X_OPERAMINI_PHONE']) : '';

    $q = $db->query("INSERT INTO `users` (`id`, `login`, `pass`, `sex`, `ip`, `soft`, `host`, `regdate`, `lastdate`, `autoreg`, `ref`, `hpnow`, `hpmax`, `money`, `sila`, `lovka`, `inta`, `intel`, `zdor`) VALUES (0, '', '{$md5pass}', 1, '{$ip}', '{$soft}', '{$host}', '{$t}', '{$t}', '1', '{$r}', 10, 10, 100, 3, 3, 3, 1, 1);");
    $id = $db->insert_id();
    $login = 'Путник_' . $id;
    $db->query("UPDATE `users` SET `login` = '{$login}' WHERE `id` = {$id} LIMIT 1;");
    $sid = session_id();
    $db->query("INSERT INTO `ipsoft` VALUES (0, '{$login}', '{$t}', '{$ip}', '{$host}', '{$soft}', '{$sid}');");

    $iid = $items->add_item($login, 195, 1);
    $items->equip_item($login, $iid);
    $iid = $items->add_item($login, 196, 1);
    $items->equip_item($login, $iid);
    $iid = $items->add_item($login, 197, 1);
    $items->equip_item($login, $iid);
    $iid = $items->add_item($login, 198, 1);
    $items->equip_item($login, $iid);
    $iid = $items->add_item($login, 199, 1);
    $items->equip_item($login, $iid);

    $q = $db->query("SELECT * FROM `users` WHERE `id` = {$id} LIMIT 1;");
    $f = $q->fetch_assoc();
    $f = calcparam($f);

    setcookie('id',   '', time() - 3600, '/');
    setcookie('hash', '', time() - 3600, '/');
    setcookie('lgn',  '', time() - 3600, '/');
    setcookie('id',   (string)$id, time() + 86400 * 365, '/');
    setcookie('hash', md5($pass),  time() + 86400 * 365, '/');
    setcookie('lgn',  $login,      time() + 86400 * 365, '/');

    header('Location: loc.php');
    fin();
}

// --- Завершение регистрации (придумать логин/пароль/email) ---
if (!empty($_SESSION['auth'])) {
    if ($f['autoreg'] == 0) {
        msg2('Вы уже сохранили своего персонажа раньше.<br/><a href="loc.php">В игру</a>', 1);
    }

    $go    = isset($_REQUEST['go'])    ? $_REQUEST['go']    : 0;
    $pass2 = isset($_REQUEST['pass2']) ? $_REQUEST['pass2'] : '';
    $name  = isset($_REQUEST['name'])  ? $_REQUEST['name']  : '';
    $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
    $pol   = isset($_REQUEST['pol'])   ? (int)$_REQUEST['pol'] : 0;

    if (empty($go)) {
        msg2('<b>Внимание!<br />Все поля обязательны для заполнения</b>!');
        echo '<div class="board"><br /><center><form action="start.php?go=1" method="POST">
        <input type="text" placeholder="Введите Логин" name="login" size="20" maxlength="30"><br/>
        <input type="password" placeholder="Введите Пароль" name="pass" size="20" maxlength="30"><br/>
        <input type="password" placeholder="Повторите Пароль" name="pass2" size="20" maxlength="30"><br/>
        <input type="text" name="email" placeholder="Веедите E-Mail" size="20" maxlength="40"><br/>
        <select name="pol">
            <option value="1">Пол: Мужской</option>
            <option value="2">Пол: Женский</option>
        </select><br/>
        <input type="submit" value="Готово"></form></center></div>';
        knopka('javascript:history.go(-1)', 'Вернуться');
        fin();
    }

    // Проверки
    $err = '';
    if (30 < mb_strlen($login, 'UTF-8') || mb_strlen($login, 'UTF-8') < 2) $err .= 'Логин должен быть от 2 до 30 символов!<br/><br/>';
    if (mb_substr(mb_strtolower($login, 'UTF-8'), 0, 7, 'UTF-8') === 'путник_') $err .= 'Логин не может начинаться на "Путник_"!<br/><br/>';
    if (20 < mb_strlen($pass, 'UTF-8') || mb_strlen($pass, 'UTF-8') < 9) $err .= 'Пароль должен быть от 9 до 20 символов!<br/><br/>';
    if ($pass !== $pass2) $err .= 'Пароли не совпадают!<br/><br/>';
    if (empty($login) || preg_match('/[^a-zA-Z0-9_а-яА-ЯёЁ]/u', $login)) $err .= 'Неверно набран логин. Допустимые символы <span style="color:darkgreen">а-Я a-Z 0-9 _</span><br/><br/>';
    if (empty($pass) || preg_match('/[^a-zA-Z0-9]/', $pass)) $err .= 'Неверно набран пароль. Допустимые символы <span style="color:darkgreen">a-Z 0-9</span><br/><br/>';
    if (!preg_match('/^[a-z0-9]+([-_\.]?[a-z0-9])+@[a-z0-9]+([-_\.]?[a-z0-9])+\.[a-z]{2,4}/i', $email) || 40 < mb_strlen($email, 'UTF-8')) $err .= 'Неверно набран e-mail. Пример: <span style="color:darkgreen"><b>admin@hmr.su</b></span><br/><br/>';

    if (!empty($err)) {
        msg2('<span style="color:red"><b>' . $err . '</b></span>');
        knopka('javascript:history.go(-1)', 'Вернуться');
        fin();
    }

    if ($pol != 1 && $pol != 2) $pol = 1;

    $login_esc = $db->real_escape_string($login);
    $email_esc = $db->real_escape_string($email);

    $q = $db->query("SELECT `email` FROM `users` WHERE `email` = '{$email_esc}' LIMIT 1;");
    if ($q && $q->num_rows > 0) {
        msg2('<span style="color:red"><b>Такой адрес email уже есть в базе!</b></span>');
        knopka('javascript:history.go(-1)', 'Вернуться');
        fin();
    }
    $q = $db->query("SELECT `login` FROM `users` WHERE `login` = '{$login_esc}' LIMIT 1;");
    if ($q && $q->num_rows > 0) {
        msg2('<span style="color:red"><b>Логин ' . $login . ' уже занят. Выберите другой.</b></span>');
        knopka('javascript:history.go(-1)', 'Вернуться');
        fin();
    }

    $md5pass = md5($pass);

    // Переименование во всех таблицах
    $old = $f['login'];
    $old_esc = $db->real_escape_string($old);
    $new_esc = $db->real_escape_string($login);
    $db->query("UPDATE `chat`         SET `login`       = '{$new_esc}' WHERE `login`       = '{$old_esc}';");
    $db->query("UPDATE `chat`         SET `privat`      = '{$new_esc}' WHERE `privat`      = '{$old_esc}';");
    $db->query("UPDATE `combat`       SET `login`       = '{$new_esc}' WHERE `login`       = '{$old_esc}';");
    $db->query("UPDATE `forum_comm`   SET `login`       = '{$new_esc}' WHERE `login`       = '{$old_esc}';");
    $db->query("UPDATE `forum_topic`  SET `login`       = '{$new_esc}' WHERE `login`       = '{$old_esc}';");
    $db->query("UPDATE `invent`       SET `login`       = '{$new_esc}' WHERE `login`       = '{$old_esc}';");
    $db->query("UPDATE `invent`       SET `arenda_login`= '{$new_esc}' WHERE `arenda_login`= '{$old_esc}';");
    $db->query("UPDATE `ipsoft`       SET `login`       = '{$new_esc}' WHERE `login`       = '{$old_esc}';");
    $db->query("UPDATE `letters`      SET `login`       = '{$new_esc}' WHERE `login`       = '{$old_esc}';");
    $db->query("UPDATE `letters`      SET `login_from`  = '{$new_esc}' WHERE `login_from`  = '{$old_esc}';");
    $db->query("UPDATE `log_peredach` SET `login`       = '{$new_esc}' WHERE `login`       = '{$old_esc}';");
    $db->query("UPDATE `log_peredach` SET `login_per`   = '{$new_esc}' WHERE `login_per`   = '{$old_esc}';");
    $db->query("UPDATE `magic`        SET `login`       = '{$new_esc}' WHERE `login`       = '{$old_esc}';");
    $db->query("UPDATE `users`        SET `login`       = '{$new_esc}', `email` = '{$email_esc}', `pass` = '{$md5pass}', `sex` = {$pol}, `autoreg` = 0 WHERE `id` = {$f['id']} LIMIT 1;");

    $mess = '[' . date('H:i:s') . '] Зарегистрирован новый пользователь. Логин: ' . $login;
    $db->query("INSERT INTO `letters` VALUES (0, 0, '{$t}', '{$admin}', '{$settings['bot']}', '{$mess}', 0, 0);");

    session_unset();
    session_destroy();
    setcookie('id',   '', time() - 3600, '/');
    setcookie('hash', '', time() - 3600, '/');
    setcookie('lgn',  '', time() - 3600, '/');
    header('Location: start.php?auth&login=' . urlencode($login) . '&pass=' . urlencode($pass));
    fin();
}

fin();
