<?php
/**
 * Личные сообщения (VK-стиль: единый листинг диалогов).
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: mb_substr(ekr($_REQUEST['mess'], 'UTF-8'), ...) → mb_substr(ekr($_REQUEST['mess']), ..., 'UTF-8').
 * Логика (GROUP BY log, max(id), read_flag, order by id desc) — оставлена как есть.
 */

$title = 'Личные сообщения';
require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/inc/hpstring.php';

// Чистка старых писем
$timer = $t - 7 * 86400;
$db->query("DELETE FROM `letters` WHERE `timemess` < '{$timer}';");

$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';
$delete = isset($_REQUEST['delete']) ? 1 : 0;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : 0;
$lid = isset($_REQUEST['lid']) ? (int)$_REQUEST['lid'] : 0;
$letter_id = isset($_REQUEST['letter_id']) ? (int)$_REQUEST['letter_id'] : 0;
$lgn = isset($_REQUEST['lgn']) ? ekr($_REQUEST['lgn']) : '';
// ИСПРАВЛЕНО: ekr() принимает 1 аргумент
$mess = isset($_REQUEST['mess']) ? mb_substr(ekr($_REQUEST['mess']), 0, 2048, 'UTF-8') : '';
$count = 0;
$numb = 15;

echo '<div class="board3">
<a href="pm.php">Письма</a> - 
<a href="pm.php?mod=write">Написать</a> - 
<a href="pm.php?mod=ignor">Игнор</a> - 
<a href="pm.php?mod=kont">Контакт</a> - 
<a href="pm.php?mod=clear">Чистка</a></div>';

if ($mod == 'clear') {
    if (empty($ok)) {
        msg2('Все ваши прочитанные сообщения будут удалены! Продолжить?');
        knopka('pm.php?mod=clear&ok=1', 'Удалить');
        knopka('pm.php', 'Вернуться');
        fin();
    }
    $db->query("DELETE FROM `letters` WHERE (`login` = '" . $db->real_escape_string($f['login']) . "' OR `login_from` = '" . $db->real_escape_string($f['login']) . "') AND `read_flag` = 1;");
    msg2('Ваши прочитанные сообщения удалены.');
    knopka('pm.php', 'Вернуться');
    fin();
}

if ($mod == 'delspisok') {
    $l = get_login($lgn);
    $lgn = $l['login'];
    if (empty($ok)) {
        msg2('Вы уверены, что хотите удалить ваш диалог с ' . $lgn . '?');
        knopka('pm.php?mod=delspisok&lgn=' . $lgn . '&ok=1', 'Удалить');
        knopka('pm.php?mod=dialog&lgn=' . $lgn, 'Вернуться к диалогу');
        fin();
    }
    $login_esc = $db->real_escape_string($f['login']);
    $lgn_esc = $db->real_escape_string($lgn);
    $db->query("DELETE FROM `letters` WHERE (`login` = '{$login_esc}' AND `login_from` = '{$lgn_esc}') OR (`login` = '{$lgn_esc}' AND `login_from` = '{$login_esc}');");
    msg2('Диалог с персонажем ' . $lgn . ' успешно удален.');
    knopka('pm.php', 'Вернуться');
    fin();
}

if ($mod == 'write') {
    echo '<div class="board" style="text-align:left">';
    echo '<form action="pm.php?mod=dialog" method="POST">';
    echo 'Кому будем писать:<br/>';
    echo '<input type="text" name="lgn" maxlength="25" size="25" /><br/>';
    echo '<input type="submit" value="Написать"></form></div>';
    knopka('pm.php', 'Вернуться');
    fin();
}

if ($mod == 'dialog') {
    $l = get_login($lgn);
    $lgn = $l['login'];
    if ($lgn == $f['login']) msg2('Нельзя писать самому себе', 1);
    if (!empty($delete)) {
        if ($letter_id <= 0) msg2('Письмо с таким ID не найдено', 1);
        $db->query("DELETE FROM `letters` WHERE `id` = '{$letter_id}' AND (`login` = '" . $db->real_escape_string($f['login']) . "' OR `login_from` = '" . $db->real_escape_string($f['login']) . "') LIMIT 1;");
        msg2('Сообщение удалено!');
    }
    $ignor = explode('|', $f['ignor']);
    $kont = explode('|', $f['kont']);
    $str = '';
    if (in_array($lgn, $ignor) && $l['admin'] == 0) $str = '<br/><br/><span style="color:' . $female . '"><b>ВНИМАНИЕ!!!<b></span> Персонаж ' . $lgn . ' у вас в игноре, он не сможет ответить на ваше сообщение!';

    echo '<div class="board">';
    echo '<form action="pm.php?mod=dialog&lgn=' . $lgn . '" method="POST">';
    echo '<input type="text" name="mess" maxlength="1024" style="width:80%"/>';
    echo '<input type="submit" value="Ok"/><br/>';
    echo '</form>' . $str . '</div>';
    knopka('pm.php?mod=dialog&lgn=' . $lgn, 'Обновить диалог');

    if (!empty($mess)) {
        if ($f['autoreg'] == 1) msg('Доступно только зарегистрированным игрокам.', 1);
        if ($f['lvl'] < 2) msg('Доступно со 2го уровня.', 1);
        $ignor2 = explode('|', $l['ignor']);
        if (in_array($f['login'], $ignor2) && $f['admin'] == 0 && $l['admin'] == 0) msg2('Вы находитесь в игнор-листе у ' . $lgn . ', сообщение не было отправлено.', 1);
        $a = $db->query("SELECT `mess` FROM `letters` WHERE `login_from` = '" . $db->real_escape_string($f['login']) . "' ORDER BY `id` DESC LIMIT 1;");
        $b = $a ? $a->fetch_assoc() : null;
        if (($b['mess'] ?? '') != $mess) {
            $db->query("INSERT INTO `letters` VALUES (0, '{$lid}', '{$t}', '" . $db->real_escape_string($lgn) . "', '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($mess) . "', 0, 0);");
        }
    }

    $login_esc = $db->real_escape_string($f['login']);
    $lgn_esc = $db->real_escape_string($lgn);
    $q = $db->query("SELECT COUNT(*) AS `c` FROM `letters` WHERE (`login` = '{$login_esc}' AND `login_from` = '{$lgn_esc}') OR (`login` = '{$lgn_esc}' AND `login_from` = '{$login_esc}');");
    $all_log = $q ? (int)$q->fetch_assoc()['c'] : 0;
    if ($start > (int)($all_log / $numb)) $start = (int)($all_log / $numb);
    if ($start < 0) $start = 0;
    $limit = $start * $numb;
    $count = $limit;
    $q = $db->query("SELECT * FROM `letters` WHERE (`login` = '{$login_esc}' AND `login_from` = '{$lgn_esc}') OR (`login` = '{$lgn_esc}' AND `login_from` = '{$login_esc}') ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
    if ($q) {
        while ($lett = $q->fetch_assoc()) {
            $count++;
            if ($lett['login'] == $f['login'] && $lett['read_flag'] == 0) {
                $db->query("UPDATE `letters` SET `read_flag` = 1 WHERE `id` = " . (int)$lett['id'] . " LIMIT 1;");
            }
            echo '<div class="board2" style="text-align:left">';
            if ($f['login'] == $lett['login_from'] || $lett['login_from'] == $settings['bot']) echo $lett['login_from'];
            else echo '<a href="infa.php?mod=uzinfa&lgn=' . $lett['login_from'] . '">' . $lett['login_from'] . '</a>';
            echo '<small> [' . date('d-m-Y H:i', (int)$lett['timemess']) . ']';
            echo ' <a href="pm.php?delete=1&letter_id=' . $lett['id'] . '&mod=dialog&lgn=' . $lgn . '">[x]</a>';
            if ($lett['login_from'] == $f['login'] && $lett['read_flag'] == 0) echo ' <b>[Непрочитано]</b>';
            if ($lett['login'] == $f['login'] && $lett['read_flag'] == 0) echo ' <b>[Новое]</b>';
            echo '</small><br/>';
            $lett_mess = link_it($lett['mess']);
            if ($f['grafika'] == 1 || $f['grafika'] == 2) $lett_mess = smile($lett_mess);
            echo $lett_mess;
            echo '</div>';
        }
    }
    if ($count > 0) knopka('pm.php?mod=delspisok&lgn=' . $lgn, 'Удалить диалог с ' . $lgn);
    if (!in_array($lgn, $kont)) knopka('pm.php?mod=kont&ok=1&lgn=' . $lgn, 'Добавить ' . $lgn . ' в контакты');
    if (!in_array($lgn, $ignor)) knopka('pm.php?mod=ignor&ok=1&lgn=' . $lgn, 'Добавить ' . $lgn . ' в игнор');
    if ($all_log > $numb) {
        echo '<div class="board">';
        if ($start > 0) echo '<a href="pm.php?mod=dialog&lgn=' . $lgn . '&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"> <-Назад</a>';
        echo ' | ';
        if ($limit + $numb < $all_log) echo '<a href="pm.php?mod=dialog&lgn=' . $lgn . '&start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo ' <a href="#" class="navig"> Вперед-></a>';
        echo '</div>';
    }
    fin();
}

if ($mod == 'ignor') {
    if (empty($ok)) {
        if (empty($f['ignor'])) msg2('Ваш игнор-лист пуст.');
        else {
            msg2('Нажмите, чтобы удалить:');
            $ignor = explode('|', $f['ignor']);
            natcasesort($ignor);
            $razm = sizeof($ignor);
            for ($i = 0; $i < $razm; $i++) {
                if (!empty($ignor[$i])) knopka('pm.php?mod=ignor&ok=2&lgn=' . $ignor[$i], $ignor[$i]);
            }
        }
        echo '<div class="board" style="text-align:left">
        <form action="pm.php?mod=ignor&ok=1" method="POST">
        Введите ник кого хотите добавить в игнор:<br/>
        <input type="text" name="lgn"/><br/>
        <input type="submit" value="Далее"/></form></div>';
        knopka('pm.php', 'Вернуться');
        fin();
    } elseif ($ok == 1) {
        $l = get_login($lgn);
        $lgn = $l['login'];
        if ($lgn == $f['login']) msg2('Вы не можете добавить себя в игнор', 1);
        $ign = explode('|', $f['ignor']);
        if (100 <= sizeof($ign)) msg2('Разрешено не более 100 человек', 1);
        if (in_array($lgn, $ign)) msg2($lgn . ' уже у вас в игноре.', 1);
        $f['ignor'] .= $lgn . '|';
        $db->query("UPDATE `users` SET `ignor` = '" . $db->real_escape_string($f['ignor']) . "' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2($lgn . ' добавлен в ваш игнор-лист.');
        knopka('pm.php?mod=ignor', 'Вернуться', 1);
        fin();
    } elseif ($ok == 2) {
        $l = get_login($lgn);
        $lgn = $l['login'];
        $ignor = explode('|', $f['ignor']);
        if (!in_array($lgn, $ignor)) msg2($lgn . ' не найден в вашем игнор-листе', 1);
        unset($ignor[array_search($lgn, $ignor)]);
        $f['ignor'] = implode('|', $ignor);
        $db->query("UPDATE `users` SET `ignor` = '" . $db->real_escape_string($f['ignor']) . "' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2($lgn . ' удален из вашего игнор-листа.');
        knopka('pm.php?mod=ignor', 'Вернуться', 1);
        fin();
    }
    fin();
}

if ($mod == 'kont') {
    if (empty($ok)) {
        if (empty($f['kont'])) msg2('Ваш контакт-лист пуст.');
        else {
            msg2('Ваши контакты:');
            echo '<div class="board" style="text-align:left">';
            $kont = explode('|', $f['kont']);
            natcasesort($kont);
            $razm = sizeof($kont);
            for ($i = 0; $i < $razm; $i++) {
                if (!empty($kont[$i])) {
                    echo '<a href="pm.php?mod=kont&ok=2&lgn=' . $kont[$i] . '">[ x ]</a> ';
                    echo '<a href="pm.php?mod=dialog&lgn=' . $kont[$i] . '"><b>' . $kont[$i] . '</b></a><br/><br/>';
                }
            }
            echo '</div>';
        }
        echo '<div class="board" style="text-align:left">
        <form action="pm.php?mod=kont&ok=1" method="POST">
        Введите ник кого хотите добавить в контакты:<br/>
        <input type="text" name="lgn"/><br/>
        <input type="submit" value="Далее"/></form></div>';
        knopka('pm.php', 'Вернуться');
        fin();
    } elseif ($ok == 1) {
        $l = get_login($lgn);
        $lgn = $l['login'];
        if ($lgn == $f['login']) msg2('Вы не можете добавить себя в контакты', 1);
        $ign = explode('|', $f['kont']);
        if (100 <= sizeof($ign)) msg2('Разрешено не более 100 человек', 1);
        if (in_array($lgn, $ign)) msg2($lgn . ' уже у вас в контактах.', 1);
        $f['kont'] .= $lgn . '|';
        $db->query("UPDATE `users` SET `kont` = '" . $db->real_escape_string($f['kont']) . "' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2($lgn . ' добавлен в ваши контакты.');
        knopka('pm.php?mod=kont', 'Вернуться', 1);
        fin();
    } elseif ($ok == 2) {
        $l = get_login($lgn);
        $lgn = $l['login'];
        $kont = explode('|', $f['kont']);
        if (!in_array($lgn, $kont)) msg2($lgn . ' не найден в ваших контактах', 1);
        unset($kont[array_search($lgn, $kont)]);
        $f['kont'] = implode('|', $kont);
        $db->query("UPDATE `users` SET `kont` = '" . $db->real_escape_string($f['kont']) . "' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2($lgn . ' удален из ваших контактов.');
        knopka('pm.php?mod=kont', 'Вернуться', 1);
        fin();
    }
    fin();
}

// --- Список диалогов (VK-стиль) ---
$login_esc = $db->real_escape_string($f['login']);
$q = $db->query("SELECT IF(`login` = '{$login_esc}', `login_from`, `login`) AS `log` FROM `letters` WHERE (`login` = '{$login_esc}' OR `login_from` = '{$login_esc}') GROUP BY `log`;");
$all_inb = $q ? $q->num_rows : 0;
if ($start > (int)($all_inb / $numb)) $start = (int)($all_inb / $numb);
if ($start < 0) $start = 0;
$limit = $start * $numb;
if (empty($all_inb)) msg2('Нет сообщений', 1);

$q = $db->query("SELECT IF(`login` = '{$login_esc}', `login_from`, `login`) AS `log`, MAX(`id`) AS `id` FROM `letters` WHERE (`login` = '{$login_esc}' OR `login_from` = '{$login_esc}') GROUP BY `log` ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
if ($q) {
    while ($m = $q->fetch_assoc()) {
        $count++;
        $qq = $db->query("SELECT COUNT(*) AS `c` FROM `letters` WHERE `login` = '{$login_esc}' AND `login_from` = '" . $db->real_escape_string($m['log']) . "' AND `read_flag` = 0;");
        $c = $qq ? (int)$qq->fetch_assoc()['c'] : 0;
        $st = '';
        $col = $c;
        if ($col > 0) $st .= '<b>';
        $st .= $m['log'];
        if ($col > 0) $st .= ' +' . $col . '</b>';
        knopka('pm.php?mod=dialog&lgn=' . $m['log'], $st);
    }
}

echo '<div class="board">';
if ($start > 0) echo '<a href="pm.php?start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
echo ' | ';
if ($limit + $numb < $all_inb) echo '<a href="pm.php?start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
echo '</div>';

fin();
