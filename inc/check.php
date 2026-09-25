<?php
/**
 * Проверка авторизации и обновление состояния персонажа.
 * PHP 8.2-совместимая версия.
 */

$admin = $settings['admin'] ?? '';

// Проверка кук
if (!empty($_COOKIE['id']) && !empty($_COOKIE['hash']) && !empty($_COOKIE['lgn'])) {
    $id    = (int)$_COOKIE['id'];
    $hash  = (string)$_COOKIE['hash'];
    $login = ekr($_COOKIE['lgn']);
} else {
    session_unset();
    session_destroy();
    setcookie('id',   '', time() - 3600, '/');
    setcookie('hash', '', time() - 3600, '/');
    setcookie('lgn',  '', time() - 3600, '/');
    header('Location: index.php');
    fin();
}

$q = $db->query("SELECT * FROM `users` WHERE `id` = {$id} AND `login` = '{$login}' LIMIT 1;");
$f = $q ? $q->fetch_assoc() : null;

if (!$f || $f['pass'] !== $hash) {
    session_unset();
    session_destroy();
    setcookie('id',   '', time() - 3600, '/');
    setcookie('hash', '', time() - 3600, '/');
    setcookie('lgn',  '', time() - 3600, '/');
    header('Location: index.php');
    fin();
}
$_SESSION['auth'] = 1;

// --- Проверка IP/SOFT ---
$ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '');
$soft = $_SERVER['HTTP_USER_AGENT'] ?? '';
$host = $_SERVER['HTTP_X_OPERAMINI_PHONE'] ?? '';
$ip   = ekr($ip);
$soft = ekr($soft);
$host = ekr($host);

if ($f['soft'] !== $soft || $f['host'] !== $host || $f['ip'] !== $ip) {
    // Обновим данные о заходе
    $sid = session_id();
    $db->query("INSERT INTO `ipsoft` VALUES (0, '{$f['login']}', '{$t}', '{$ip}', '{$host}', '{$soft}', '{$sid}');");
    $db->query("UPDATE `users` SET `soft` = '{$soft}', `host` = '{$host}', `ip` = '{$ip}', `session` = '{$sid}' WHERE `id` = {$f['id']} LIMIT 1;");
}

// --- Удаление старых авторегов (старше 1 суток) ---
$timer = $t - 86400;
$q = $db->query("SELECT `login` FROM `users` WHERE `autoreg` = 1 AND `regdate` < '{$timer}';");
if ($q !== false) {
    while ($del = $q->fetch_assoc()) {
        $lgn_esc = $db->real_escape_string($del['login']);
        $db->query("DELETE FROM `chat` WHERE `login` = '{$lgn_esc}';");
        $db->query("DELETE FROM `chat` WHERE `privat` = '{$lgn_esc}';");
        $db->query("DELETE FROM `combat` WHERE `login` = '{$lgn_esc}';");
        $db->query("DELETE FROM `forum_comm` WHERE `login` = '{$lgn_esc}';");
        $db->query("DELETE FROM `forum_topic` WHERE `login` = '{$lgn_esc}';");
        $db->query("DELETE FROM `invent` WHERE `login` = '{$lgn_esc}';");
        $db->query("DELETE FROM `ipsoft` WHERE `login` = '{$lgn_esc}';");
        $db->query("DELETE FROM `letters` WHERE `login` = '{$lgn_esc}';");
        $db->query("DELETE FROM `letters` WHERE `login_from` = '{$lgn_esc}';");
        $db->query("DELETE FROM `log_peredach` WHERE `login` = '{$lgn_esc}';");
        $db->query("DELETE FROM `log_peredach` WHERE `login_per` = '{$lgn_esc}';");
        $db->query("DELETE FROM `magic` WHERE `login` = '{$lgn_esc}';");
        $db->query("DELETE FROM `users` WHERE `login` = '{$lgn_esc}' LIMIT 1;");
    }
}

// --- Разблокировка по времени ---
if ($f['ban'] != 0 && $f['ban'] < time() && $f['flag_blok'] == 1) {
    $f['ban'] = 0;
    $f['flag_blok'] = 0;
    $f['zachto_blok'] = '';
    $db->query("UPDATE `users` SET `ban` = 0, `flag_blok` = 0, `zachto_blok` = '' WHERE `id` = {$f['id']} LIMIT 1;");
}
if ($f['ban'] != 0 && $f['ban'] < time() && $f['flag_blok'] == 0) {
    $f['ban'] = 0;
    $f['zachto_blok'] = '';
    $db->query("UPDATE `users` SET `ban` = 0, `zachto_blok` = '' WHERE `id` = {$f['id']} LIMIT 1;");
}

// --- Проверка блока ---
if ($f['flag_blok'] == 1) {
    echo 'Ваш персонаж заблокирован.<br/>';
    echo 'Причина: ' . $f['zachto_blok'] . '<br/>';
    if ($f['ban'] > 0) echo 'Блок до ' . date('d.m.Y H:i', (int)$f['ban']);
    fin();
}

// --- Восстановление HP ---
$hp_plus = $f['hpmax'] * 0.07;
if (!empty($f['klan'])) $hp_plus = $f['hpmax'] * 0.09;
$hp_plus = (int)ceil($hp_plus);
if ($f['status'] != 1 && $f['hpnow'] < $f['hpmax']) {
    $minutes = (int)floor((time() - (int)$f['hptime']) / 60);
    $hp_plus1 = $hp_plus * $minutes;
    $plus = $f['hpnow'] + $hp_plus1;
    if ($plus > $f['hpmax']) $plus = $f['hpmax'];
    if ($plus > $f['hpnow']) {
        $f['hpnow'] = $plus;
        $db->query("UPDATE `users` SET `hpnow` = {$plus}, `hptime` = '{$t}' WHERE `id` = {$f['id']} LIMIT 1;");
    }
}

// --- Восстановление MP ---
$mp_plus = $f['manamax'] * 0.07;
if (!empty($f['klan'])) $mp_plus = $f['manamax'] * 0.09;
$mp_plus = (int)ceil($mp_plus);
if ($f['status'] != 1 && $f['mananow'] < $f['manamax']) {
    $minutes = (int)floor((time() - (int)$f['manatime']) / 60);
    $mp_plus1 = $mp_plus * $minutes;
    $plus = $f['mananow'] + $mp_plus1;
    if ($plus > $f['manamax']) $plus = $f['manamax'];
    if ($plus > $f['mananow']) {
        $f['mananow'] = $plus;
        $db->query("UPDATE `users` SET `mananow` = {$plus}, `manatime` = '{$t}' WHERE `id` = {$f['id']} LIMIT 1;");
    }
}

// --- Обрезка HP/MP ---
if ($f['hpnow'] > $f['hpmax']) {
    $f['hpnow'] = $f['hpmax'];
    $db->query("UPDATE `users` SET `hpnow` = '{$f['hpnow']}', `hptime` = '{$t}' WHERE `id` = {$f['id']} LIMIT 1;");
}
if ($f['mananow'] > $f['manamax']) {
    $f['mananow'] = $f['manamax'];
    $db->query("UPDATE `users` SET `mananow` = {$f['mananow']}, `manatime` = '{$t}' WHERE `id` = {$f['id']} LIMIT 1;");
}
if ($f['hpnow'] < 0) {
    $f['hpnow'] = 0;
    $db->query("UPDATE `users` SET `hpnow` = 0, `hptime` = '{$t}' WHERE `id` = {$f['id']} LIMIT 1;");
}
if ($f['mananow'] < 0) {
    $f['mananow'] = 0;
    $db->query("UPDATE `users` SET `mananow` = 0, `manatime` = '{$t}' WHERE `id` = {$f['id']} LIMIT 1;");
}

// --- Взятие уровня ---
require_once __DIR__ . '/exp.php';
if ($tolev < 1 && $f['status'] != 1) {
    $f['lvl'] += 1;
    $f['exp'] = (-1) * $tolev;
    if (!empty($f['klan'])) klan_points($f['klan'], $f['lvl']);
    $db->query("UPDATE `users` SET `lvl` = {$f['lvl']}, `exp` = '{$f['exp']}' WHERE `id` = {$f['id']} LIMIT 1;");
    $f = calcparam($f);
    if ($f['lvl'] == 2) {
        msg2('Поздравляем! Вы только что получили новый уровень. Теперь вам нужно переместиться в один из лагерей по ссылке "Портал" внизу страницы, и продать все свои старые вещи в магазин. Снять старые вещи можно в меню персонажа по ссылке "Снаряжение". Так же вы должны будете купить вещи на свой новый уровень. Загляните на рынок, поищите там. Обычно, цены там ниже магазинных. Приятной игры.');
    } else {
        msg2('Поздравляем, вы получили ' . $f['lvl'] . ' уровень!');
    }
}

// --- Автопередача главы клана, если глава отсутствует > 7 дней ---
// ИСПРАВЛЕНО: раньше каждый зашедший получал статус главы.
if (!empty($f['klan']) && $f['klan_status'] < 3) {
    $klan_esc = $db->real_escape_string($f['klan']);
    $q = $db->query("SELECT `login`, `lastdate` FROM `users` WHERE `klan` = '{$klan_esc}' AND `klan_status` = 3;");
    if ($q && $q->num_rows > 0) {
        $a = $q->fetch_assoc();
        if ($a['lastdate'] + 60 * 60 * 24 * 7 < $t) {
            // Глава отсутствует > 7 дней — передаём главу первому, кто зашёл
            $db->query("UPDATE `users` SET `klan_status` = 2 WHERE `login` = '" . $db->real_escape_string($a['login']) . "' LIMIT 1;");
            $db->query("UPDATE `users` SET `klan_status` = 3 WHERE `id` = {$f['id']} LIMIT 1;");
            msg2('Поздравляем, вам доверено управление вашим кланом в связи с отсутствием главы более недели.');
        }
    }
}

// --- Обновление lastdate ---
if ($f['lastdate'] < $t - 60) {
    $db->query("UPDATE `users` SET `lastdate` = '{$t}' WHERE `id` = {$f['id']} LIMIT 1;");
}

// --- Ежедневный бонус ---
require_once __DIR__ . '/bonus.php';

// --- Количество непрочитанных писем ---
$q = $db->query("SELECT COUNT(*) AS `c` FROM `letters` WHERE `login` = '{$f['login']}' AND `read_flag` = 0;");
$count_pm = $q ? (int)$q->fetch_assoc()['c'] : 0;
