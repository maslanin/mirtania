<?php
/**
 * Чат (3 комнаты).
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: ekr($_REQUEST['mess'], 'UTF-8') → ekr($_REQUEST['mess']), mb_substr(..., 'UTF-8').
 */

$title = 'Чат';
require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/inc/hpstring.php';

$room = isset($_REQUEST['room']) ? (int)$_REQUEST['room'] : 0;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$mess = isset($_REQUEST['mess']) ? mb_substr(ekr($_REQUEST['mess']), 0, 1024, 'UTF-8') : '';
$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';
$lgn = isset($_REQUEST['lgn']) ? ekr($_REQUEST['lgn']) : '';
$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : '';
$lich = isset($_REQUEST['lich']) ? 1 : 0;
$mid = isset($_REQUEST['mid']) ? (int)$_REQUEST['mid'] : 0;
$numb = 20;

$admin = $settings['admin'] ?? '';

if (empty($room)) $room = (int)$f['chatroom'];
if (empty($room)) $room = 1;
if ($room == 3 && empty($f['klan'])) $room = 1;
if ($room > 3 || $room < 1) $room = 1;
if ($room != $f['chatroom']) {
    $db->query("UPDATE `users` SET `chatroom` = {$room} WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
}

// --- Отправка сообщения ---
if (!empty($mess) && mb_strlen($mess, 'UTF-8') > 1) {
    if ($f['autoreg'] == 1) msg('Доступно только зарегистрированным игрокам.', 1);
    if ($f['lvl'] < 2) msg('Доступно со 2го уровня.', 1);
    $p = 0;
    if (!empty($lgn)) {
        $l = get_login($lgn);
        $lgn = $l['login'];
        if (!empty($lich)) $p = 1;
    }
    if ($f['ban'] > $t) msg2('У вас молча еще ' . ceil(($f['ban'] - $t) / 60) . ' мин.', 1);

    $login_esc = $db->real_escape_string($f['login']);
    $a = $db->query("SELECT `message` FROM `chat` WHERE `login` = '{$login_esc}' AND `room` = '{$room}' ORDER BY `id` DESC LIMIT 1;");
    $b = $a ? $a->fetch_assoc() : null;
    if ($mess != ($b['message'] ?? '')) {
        $mess_esc = $db->real_escape_string($mess);
        $lgn_esc = $db->real_escape_string($lgn);
        $klan_esc = $db->real_escape_string($f['klan']);
        $party_esc = $db->real_escape_string($f['party']);
        $db->query("INSERT INTO `chat` VALUES (0, '{$login_esc}', '{$mess_esc}', {$room}, '{$lgn_esc}', {$p}, " . (int)$f['sex'] . ", '{$t}', '{$klan_esc}', '{$party_esc}', " . (int)$f['admin'] . ");");
    }
    $lgn = '';
}

// --- Удаление сообщения ---
if ($mod == 'delete' && !empty($mid)) {
    $q = $db->query("SELECT * FROM `chat` WHERE `id` = {$mid} AND `room` = {$room} AND `login` <> '" . $db->real_escape_string($settings['bot']) . "' LIMIT 1;");
    $l = $q ? $q->fetch_assoc() : null;
    if ($l) {
        if ($f['login'] == $admin || $f['login'] == $l['login'] || 1 <= $f['admin']) {
            if (empty($ok)) {
                msg('Вы уверены, что хотите удалить это сообщение "' . $l['message'] . '" от <a href="infa.php?mod=uzinfa&lgn=' . $l['login'] . '">' . $l['login'] . '</a>?');
                knopka('chat.php?mod=delete&mid=' . $mid . '&ok=1', 'Удалить');
                knopka('chat.php', 'Вернуться');
                fin();
            }
            $db->query("DELETE FROM `chat` WHERE `id` = {$mid} AND `room` = {$room} LIMIT 1;");
        } else {
            msg2('Вы не можете удалить это сообщение!', 1);
        }
    }
}

// --- Чистка своих сообщений ---
if ($mod == 'clear') {
    if (empty($_REQUEST['ok'])) {
        msg2('Все ваши сообщения в этой комнате будут удалены!');
        knopka('chat.php?mod=clear&ok=1', 'Продолжить');
        knopka('chat.php', 'В чат');
        fin();
    }
    $db->query("DELETE FROM `chat` WHERE `login` = '" . $db->real_escape_string($f['login']) . "' AND `room` = " . (int)$f['chatroom'] . ";");
}

// --- Список в чате ---
if ($mod == 'listchat') {
    $timer = $t - 300;
    $q = $db->query("SELECT `login`, `chatdate`, `chatroom`, `sex` FROM `users` WHERE `chatdate` > '{$timer}' ORDER BY `login` ASC;");
    if (!$q || $q->num_rows == 0) msg('В чате никого нет!', 1);
    msg2('В чате сейчас:');
    while ($a = $q->fetch_assoc()) {
        $color = ($a['sex'] == 1) ? $male : $female;
        echo '<div class="board2" style="text-align:left">';
        if ($f['login'] == $a['login']) echo '<b><span style="color:' . $color . '">' . $a['login'] . '</span></b> [' . date('H:i', (int)$a['chatdate']) . '] ';
        else echo '<a href="chat.php?lgn=' . $a['login'] . '"><b><span style="color:' . $color . '">' . $a['login'] . '</span></b></a> <a href="chat.php?mod=view&lgn=' . $a['login'] . '">[i]</a> [' . date('H:i', (int)$a['chatdate']) . '] ';
        if ($a['chatroom'] == 1) echo '(общий)';
        elseif ($a['chatroom'] == 2) echo '(торговый)';
        else echo '(клан)';
        echo '</div>';
    }
    knopka('chat.php', 'В чат', 1);
    fin();
}

// --- Список в комнате ---
if ($mod == 'listroom') {
    $timer = $t - 300;
    if ($f['chatroom'] == 1) {
        $q = $db->query("SELECT `login`, `chatdate`, `chatroom`, `sex` FROM `users` WHERE `chatdate` > '{$timer}' AND `chatroom` = '" . (int)$f['chatroom'] . "' ORDER BY `login` ASC;");
    } elseif ($f['chatroom'] == 2) {
        $q = $db->query("SELECT `login`, `chatdate`, `chatroom`, `sex` FROM `users` WHERE `chatdate` > '{$timer}' AND `chatroom` = '" . (int)$f['chatroom'] . "' AND `klan` = '" . $db->real_escape_string($f['klan']) . "' ORDER BY `login` ASC;");
    } else {
        $q = $db->query("SELECT `login`, `chatdate`, `chatroom`, `sex` FROM `users` WHERE `chatdate` > '{$timer}' AND `chatroom` = '" . (int)$f['chatroom'] . "' AND `party` = '" . $db->real_escape_string($f['party']) . "' ORDER BY `login` ASC;");
    }
    if (!$q || $q->num_rows == 0) msg('В этой комнате никого нет', 1);
    msg2('Эту комнату сейчас читают:');
    while ($a = $q->fetch_assoc()) {
        $color = ($a['sex'] == 1) ? $male : $female;
        echo '<div class="board2" style="text-align:left">';
        if ($f['login'] == $a['login']) echo '<b><span style="color:' . $color . '">' . $a['login'] . '</span></b> [' . date('H:i', (int)$a['chatdate']) . '] ';
        else echo '<a href="chat.php?lgn=' . $a['login'] . '"><b><span style="color:' . $color . '">' . $a['login'] . '</span></b></a> <a href="chat.php?mod=view&lgn=' . $a['login'] . '">[i]</a> [' . date('H:i', (int)$a['chatdate']) . ']<br/>';
        echo '</div>';
    }
    knopka('chat.php', 'В чат', 1);
    fin();
}

// --- Просмотр анкеты из чата ---
if ($mod == 'view') {
    if (empty($lgn)) msg('Вы не ввели логин!', 1);
    echo '<div class="board" style="text-align:left">';
    $l = get_login($lgn);
    $lgn = $l['login'];
    $color = ($l['sex'] == 1) ? $manacolor : $logincolor;
    echo '<b>Анкета</b><br/><br/>';
    echo 'Персонаж: <b><span style="color:' . $color . '">' . $l['login'] . '</span></b>';
    if ($l['lastdate'] < $t - 300) echo ' [<span style="color:red">Off</span>]<br/>';
    else echo ' [<span style="color:green">On</span>]<br/>';
    echo 'Имя: ' . $l['name'] . '<br/>';
    echo 'Пол: ' . ($l['sex'] == 1 ? 'Мужской' : 'Женский') . '<br/>';
    echo 'Уровень: ' . $l['lvl'] . '<br/>';
    if (!empty($l['klan'])) echo 'Клан: ' . $l['klan'];
    echo '</div>';
    knopka('chat.php?lgn=' . $l['login'], 'Написать сообщение');
    knopka('pm.php?mod=dialog&lgn=' . $l['login'], 'Отправить письмо');
    knopka('infa.php?mod=uzinfa&lgn=' . $l['login'], 'Перейти к полной анкете');
    if (1 <= $f['admin'] && $l['admin'] <= $f['admin']) knopka('adm.php?lgn=' . $l['login'], 'Управление');
    if (1 <= $f['admin'] && $l['admin'] <= $f['admin'] && $l['flag_blok'] == 0) {
        if ($l['ban'] > $t) knopka('adm.php?mod=chatunban&lgn=' . $l['login'], 'Снять молчу');
        else knopka('adm.php?mod=chatban&lgn=' . $l['login'], 'Поставить молчу');
    }
    knopka('chat.php', 'Вернуться');
    fin();
}

// Удаление старых сообщений (было 1728000 = 20 дней, комментарий говорил "2 суток" — оставляем 20 дней)
$timer = $t - 1728000;
$db->query("DELETE FROM `chat` WHERE `timemess` < '{$timer}';");

// Обновим время посещения чата
$db->query("UPDATE `users` SET `chatdate` = '{$t}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");

if ($f['ban'] > $t) {
    echo '<div class="board3" align="center"><small>';
    echo '<br/>Молча еще ' . ceil(($f['ban'] - $t) / 60) . ' мин.';
    echo '</small></div>';
}

// Форма сообщения
echo '<div class="board">';
echo '<form method="POST" action="chat.php">';
if (!empty($lgn)) {
    $l = get_login($lgn);
    $lgn = $l['login'];
    echo 'Пишем <b>' . $lgn . '</b> <a href="chat.php">[x]</a> [<input type="checkbox" name="lich" value="1"/>Приватно]:<br/>';
    echo '<input type="hidden" name="lgn" value="' . $lgn . '">';
}
if ($f['ban'] < $t) {
    echo '<input type="text" name="mess" maxlength="1024" style="width:80%"/>';
    echo '<input type="submit" value="Ok"/></form></div>';
}

// Запросы для комнат
if ($room == 1 || $room == 2) {
    $a = $db->query("SELECT COUNT(*) AS `c` FROM `chat` WHERE `room` = '{$room}' AND (`flag_privat` = 0 OR (`flag_privat` = 1 AND (`login` = '" . $db->real_escape_string($f['login']) . "' OR `privat` = '" . $db->real_escape_string($f['login']) . "')));");
    $all_chat = $a ? (int)$a->fetch_assoc()['c'] : 0;
    if ($start > (int)($all_chat / $numb)) $start = (int)($all_chat / $numb);
    if ($start < 0) $start = 0;
    $limit = $start * $numb;
    $q = $db->query("SELECT * FROM `chat` WHERE `room` = '{$room}' AND (`flag_privat` = 0 OR (`flag_privat` = 1 AND (`login` = '" . $db->real_escape_string($f['login']) . "' OR `privat` = '" . $db->real_escape_string($f['login']) . "'))) ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
} elseif ($room == 3) {
    $a = $db->query("SELECT COUNT(*) AS `c` FROM `chat` WHERE `room` = '{$room}' AND (`flag_privat` = 0 OR (`flag_privat` = 1 AND (`login` = '" . $db->real_escape_string($f['login']) . "' OR `privat` = '" . $db->real_escape_string($f['login']) . "'))) AND `klan` = '" . $db->real_escape_string($f['klan']) . "';");
    $all_chat = $a ? (int)$a->fetch_assoc()['c'] : 0;
    if ($start > (int)($all_chat / $numb)) $start = (int)($all_chat / $numb);
    if ($start < 0) $start = 0;
    $limit = $start * $numb;
    $q = $db->query("SELECT * FROM `chat` WHERE `room` = '{$room}' AND (`flag_privat` = 0 OR (`flag_privat` = 1 AND (`login` = '" . $db->real_escape_string($f['login']) . "' OR `privat` = '" . $db->real_escape_string($f['login']) . "'))) AND `klan` = '" . $db->real_escape_string($f['klan']) . "' ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
}

// Вывод сообщений
$count = 0;
if (!empty($q)) {
    while ($a = $q->fetch_assoc()) {
        $sex = (int)$a['sex'];
        $adm = (int)$a['admin'];
        $mess_id = (int)$a['id'];
        $timemess = (int)$a['timemess'];
        $login = $a['login'];
        $message = link_it($a['message']);
        if ($f['grafika'] == 1 || $f['grafika'] == 2) $message = smile($message);
        $privat = $a['privat'];
        $lichn = (int)$a['flag_privat'];
        $color_login = ($sex == 1) ? $male : $female;
        if ($adm >= 1) $color_login = 'white';

        echo '<div class="board2" style="text-align:left;">';
        if ($login != $settings['bot']) {
            echo '[' . date('H:i', $timemess) . '] ';
            if ($f['login'] == $login) echo '<b><span style="color:' . $color_login . '">' . $login . '</span></b> ';
            else echo '<a href="chat.php?mod=view&lgn=' . $login . '"><b><span style="color:' . $color_login . '">' . $login . '</span></b></a>
            <a href="chat.php?lgn=' . $login . '">[отв]</a>';
            if (mb_strtolower($f['login'], 'UTF-8') == mb_strtolower($login, 'UTF-8') || $f['login'] == $admin || 1 <= $f['admin']) {
                echo ' <a href="chat.php?mod=delete&mid=' . $mess_id . '">[x]</a>';
            }
            echo '<br/>';
        }
        if ($lichn == 1) {
            echo '<b>[!]</b> <b>' . $privat . '</b>, <font color="#666">';
            echo $message;
            echo '</font>';
        } else {
            if (!empty($privat)) echo '<b>' . $privat . '</b>, ';
            echo $message;
        }
        $count++;
        echo '</div>';
    }
}

$timer = $t - 300;
$q = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `chatdate` > '{$timer}';");
$res = $q ? (int)$q->fetch_assoc()['c'] : 0;
knopka('chat.php?mod=listchat', 'В чате: ' . $res);

if ($room == 1 || $room == 2) {
    $q = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `chatdate` > '{$timer}' AND `chatroom` = {$room};");
} elseif ($room == 3) {
    $q = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `chatdate` > '{$timer}' AND `chatroom` = {$room} AND `klan` = '" . $db->real_escape_string($f['klan']) . "';");
}
$res = $q ? (int)$q->fetch_assoc()['c'] : 0;
knopka('chat.php?mod=listroom', 'В комнате: ' . $res);

if ($all_chat > $numb) {
    echo '<div class="board">';
    if ($start > 0) echo '<a href="chat.php?start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"> <-Назад</a>';
    echo ' | ';
    if ($limit + $numb < $all_chat) echo '<a href="chat.php?start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo ' <a href="#" class="navig"> Вперед-></a>';
    echo '</div>';
}

echo '<div class="board">';
echo '<form action="chat.php" method="POST">';
echo '<select name="room" onchange="this.form.submit()">';
echo '<option '; if ($room == 1) echo 'selected '; echo 'value="1">Общ</option>';
echo '<option '; if ($room == 2) echo 'selected '; echo 'value="2">Торг</option>';
if (!empty($f['klan'])) { echo '<option '; if ($room == 3) echo 'selected '; echo 'value="3">Клан</option>'; }
echo '</select></form></div>';

echo '<div class="menu">';
if ($f['admin'] >= 3) echo '<a href="adm.php?mod=chatclear">Чистка чатов</a> - ';
if ($f['admin'] >= 1) echo '<a href="adm.php?mod=chatroomclear">Чистка комнаты</a> - ';
echo '<a href="chat.php?mod=clear">Чистка сообщений</a> - ';
echo '<a href="lib.php?mod=smile">Справка по смайлам</a>';
echo '</div>';

fin();
