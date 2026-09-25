<?php
/**
 * Форум.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО:
 *  - $b['name'] при $b = false → проверка
 *  - UPDATE forum_topic ... where razdel=... AND id=... → убрал razdel (логическая ошибка)
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';

$razdel = isset($_REQUEST['razdel']) ? (int)$_REQUEST['razdel'] : 0;
$topic  = isset($_REQUEST['topic'])  ? (int)$_REQUEST['topic']  : 0;
$start  = isset($_REQUEST['start'])  ? (int)$_REQUEST['start']  : 0;
$mod    = isset($_REQUEST['mod'])    ? $_REQUEST['mod'] : '';
$ok     = isset($_REQUEST['ok'])     ? $_REQUEST['ok'] : 0;
$name   = isset($_REQUEST['name'])   ? $_REQUEST['name'] : '';
$mess   = isset($_REQUEST['mess'])   ? $_REQUEST['mess'] : '';
$write  = isset($_REQUEST['write'])  ? $_REQUEST['write'] : '';
$lgn    = isset($_REQUEST['lgn'])    ? $_REQUEST['lgn'] : '';
$cid    = isset($_REQUEST['cid'])    ? (int)$_REQUEST['cid'] : 0;
$full   = isset($_REQUEST['full'])   ? 1 : 0;

require_once __DIR__ . '/inc/hpstring.php';

if ($mod == 'allclear' && 3 <= $f['admin']) {
    if (empty($ok)) {
        msg2('Все темы во всех разделах будут удалены! Продолжить?');
        knopka('forum.php?mod=allclear&ok=1', 'Удалить', 1);
        knopka('forum.php', 'Вернуться', 1);
        fin();
    }
    $db->query("TRUNCATE TABLE `forum_topic`;");
    $db->query("TRUNCATE TABLE `forum_comm`;");
}

$numb = 10;
$count = 0;

// --- Создание темы ---
if ($mod == 'opentopic') {
    if ($f['lvl'] < 2) msg2('Создавать темы можно со 2 уровня', 1);
    if (empty($ok)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="forum.php?razdel=' . $razdel . '&mod=opentopic&ok=1" method="POST">';
        echo 'Название темы (3..50 симв)<br/>';
        echo '<input type="text" name="name" maxlength=50 style="width:80%"/><br/>';
        echo 'Содержимое темы (3..5000 симв)<br/>';
        echo '<textarea name="mess" maxlength=5000 rows="10" style="width:80%;"></textarea><br/>';
        echo 'Раздел: ';
        echo '<select name="razdel">';
        if ($f['admin'] > 1) echo '<option value="1">Администрация</option>';
        echo '<option value="2">Предложения</option>';
        echo '<option value="3">Помощь</option>';
        echo '<option value="4">Баги/Ошибки</option>';
        echo '<option value="5">Общение</option>';
        echo '<option value="6">Творчество</option>';
        echo '</select>';
        echo '<input type="submit" value="Создать тему"/></form>';
        echo '<br/><br/><small>* Не забываем, что тема должна строго соответствовать тематике выбранного раздела</small></div>';
        knopka('forum.php', 'Главная форума');
        fin();
    }
    if (mb_strlen($name, 'UTF-8') < 3 || 50 < mb_strlen($name, 'UTF-8')) msg2('Ошибка заполения названия темы.', 1);
    if (mb_strlen($mess, 'UTF-8') < 3 || 5000 < mb_strlen($mess, 'UTF-8')) msg2('Ошибка заполения тела темы.', 1);
    if (empty($razdel) || ($razdel == 1 && $f['admin'] < 2) || ($razdel < 1 || $razdel > 6)) msg2('Ошибка выбора раздела', 1);

    $name_esc = $db->real_escape_string($name);
    $mess_esc = $db->real_escape_string($mess);
    $login_esc = $db->real_escape_string($f['login']);

    $a = $db->query("SELECT * FROM `forum_topic` WHERE `login` = '{$login_esc}' ORDER BY `id` DESC LIMIT 1;");
    $b = $a ? $a->fetch_assoc() : null;
    if (($b['name'] ?? '') != $name) {
        $db->query("INSERT INTO `forum_topic` VALUES (0, '{$login_esc}', {$razdel}, '{$name_esc}', '{$t}', '{$mess_esc}', '{$t}', 0);");
        $topic = $db->insert_id();
        msg2('Тема "' . $name . '" успешно добавлена!');
        knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic, 'Перейти в тему', 1);
        knopka('forum.php', 'Главная форума', 1);
        fin();
    }
    fin();
}

// --- Список тем ---
if (empty($topic)) {
    $s = '<small>';
    $razdel_names = [1=>'Администрация',2=>'Предложения',3=>'Помощь',4=>'Баги/ошибки',5=>'Общение',6=>'Творчество'];
    if (isset($razdel_names[$razdel])) $s .= $razdel_names[$razdel];
    $s .= '</small>';
    if (!empty($razdel)) msg($s);

    if (empty($razdel)) $q = $db->query("SELECT COUNT(*) AS `c` FROM `forum_topic`;");
    else $q = $db->query("SELECT COUNT(*) AS `c` FROM `forum_topic` WHERE `razdel` = {$razdel};");
    $all = $q ? (int)$q->fetch_assoc()['c'] : 0;

    if ($start > (int)($all / $numb)) $start = (int)($all / $numb);
    if ($start < 0) $start = 0;
    $limit = $start * $numb;
    $count = $limit;

    if (empty($razdel)) $q = $db->query("SELECT * FROM `forum_topic` ORDER BY `lastcomm` DESC LIMIT {$limit}, {$numb};");
    else $q = $db->query("SELECT * FROM `forum_topic` WHERE `razdel` = '{$razdel}' ORDER BY `lastcomm` DESC LIMIT {$limit}, {$numb};");

    if ($q) {
        while ($ft = $q->fetch_assoc()) {
            $b = $db->query("SELECT COUNT(*) AS `c` FROM `forum_comm` WHERE `id_topic` = " . (int)$ft['id'] . ";");
            $cc = $b ? (int)$b->fetch_assoc()['c'] : 0;
            echo '<div class="board2" style="text-align:left">
            <a href="forum.php?razdel=' . $razdel . '&topic=' . $ft['id'] . '"><b><span style="color:#363636">' . $ft['name'] . '</span></b></a>';
            if ($cc > 0) echo ' (' . $cc . ')';
            echo ' (by ' . $ft['login'] . ')';
            if (empty($razdel)) {
                echo ' [<b>';
                echo $razdel_names[(int)$ft['razdel']] ?? '?';
                echo '</b>]';
            }
            if ($ft['flag_close'] == 1) echo ' <img src="/pic/key.png" alt=""/>';
            echo '<br/>';
            echo '<small>созд. ' . date('d.m.Y H:i', (int)$ft['timetopic']);
            echo '</small>';
            echo '</div>';
            $count++;
        }
    }

    if ($all > $numb) {
        echo '<div class="board">';
        if ($start > 0) echo '<a href="forum.php?razdel=' . $razdel . '&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"> <-Назад</a>';
        echo ' | ';
        if ($limit + $numb < $all) echo '<a href="forum.php?razdel=' . $razdel . '&start=' . ($start + 1) . '" class="navig" >Вперед-></a>'; else echo ' <a href="#" class="navig"> Вперед-></a>';
        echo '</div>';
    }

    if (empty($razdel)) {
        echo '<div class="board"><form action="forum.php" method="GET">';
        echo '<select name="razdel" onchange="this.form.submit()">';
        echo '<option value="1">Администрация</option>';
        echo '<option value="2">Предложения</option>';
        echo '<option value="3">Помощь</option>';
        echo '<option value="4">Баги/ошибки</option>';
        echo '<option value="5">Общение</option>';
        echo '<option value="6">Творчество</option>';
        echo '</select></form></div>';
    }
    knopka('forum.php?mod=opentopic', 'Создать тему', 1);
    knopka('forum.php', 'Главная форума', 1);
    fin();
}

// --- Тема ---
if ($topic <= 0) msg2('Тема не найдена', 1);

if ($mod == 'edit') {
    $q = $db->query("SELECT * FROM `forum_topic` WHERE `id` = '{$topic}' LIMIT 1;");
    $ed = $q ? $q->fetch_assoc() : null;
    if (!$ed) msg2('Тема не найдена', 1);
    if ($f['admin'] < 2 && $f['login'] != $ed['login']) msg2('Вы не можете редактировать эту тему', 1);
    if ($ed['flag_close'] == 1 && $f['admin'] < 3) msg2('Нельзя редактировать закрытые темы', 1);
    if (empty($ok)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="forum.php?razdel=' . $razdel . '&topic=' . $topic . '&mod=edit&ok=1" method="POST">';
        echo 'Название темы (3..50 симв)<br/>';
        echo '<input type="text" name="name" maxlength=50 value="' . $ed['name'] . '" style="width:80%"><br/>';
        echo 'Содержимое темы (3..5000 симв)<br/>';
        echo '<textarea name="mess" maxlength=5000 rows="10" style="width:80%;">' . $ed['message'] . '</textarea><br/>';
        echo '<input type="submit" value="Редактировать"/></form></div>';
        knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic, 'Вернуться', 1);
        knopka('forum.php', 'Главная форума', 1);
        fin();
    }
    if (mb_strlen($name, 'UTF-8') < 3 || 50 < mb_strlen($name, 'UTF-8')) msg2('Ошибка заполения названия темы.', 1);
    if (mb_strlen($mess, 'UTF-8') < 3 || 5000 < mb_strlen($mess, 'UTF-8')) msg2('Ошибка заполения тела темы.', 1);
    $name_esc = $db->real_escape_string($name);
    $mess_esc = $db->real_escape_string($mess);
    // ИСПРАВЛЕНО: убрал razdel из WHERE — редактирование не должно зависеть от раздела
    $db->query("UPDATE `forum_topic` SET `name` = '{$name_esc}', `message` = '{$mess_esc}', `lastcomm` = '{$t}' WHERE `id` = '{$topic}' LIMIT 1;");
    msg2('Тема успешно отредактирована!');
    knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic, 'В тему', 1);
    knopka('forum.php', 'Главная форума', 1);
    fin();
}

if ($mod == 'close') {
    $q = $db->query("SELECT * FROM `forum_topic` WHERE `id` = '{$topic}' LIMIT 1;");
    $ed = $q ? $q->fetch_assoc() : null;
    if (!$ed) msg2('Тема не найдена', 1);
    if ($f['admin'] < 1 && $f['login'] != $ed['login']) msg2('Вы не можете закрыть эту тему', 1);
    if ($ed['flag_close'] == 1) msg2('Эта тема уже закрыта', 1);
    if (empty($ok)) {
        msg2('Вы действительно хотите закрыть эту тему?');
        knopka('forum.php?mod=close&ok=1&razdel=' . $razdel . '&topic=' . $topic, 'Закрыть', 1);
        knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic, 'Вернуться', 1);
        knopka('forum.php', 'Главная форума', 1);
        fin();
    }
    $db->query("UPDATE `forum_topic` SET `flag_close` = 1 WHERE `id` = '{$topic}' LIMIT 1;");
    msg2('Тема закрыта.');
    knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic, 'В тему', 1);
    knopka('forum.php', 'Главная форума', 1);
    fin();
}

if ($mod == 'open') {
    $q = $db->query("SELECT * FROM `forum_topic` WHERE `id` = '{$topic}' LIMIT 1;");
    $ed = $q ? $q->fetch_assoc() : null;
    if (!$ed) msg2('Тема не найдена', 1);
    if ($f['admin'] < 1) msg2('Вы не можете открыть эту тему', 1);
    if ($ed['flag_close'] == 0) msg2('Тема не закрыта', 1);
    if (empty($ok)) {
        msg2('Вы действительно хотите открыть эту тему?');
        knopka('forum.php?mod=open&ok=1&razdel=' . $razdel . '&topic=' . $topic, 'Открыть', 1);
        knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic, 'В тему', 1);
        knopka('forum.php', 'Главная форума', 1);
        fin();
    }
    $db->query("UPDATE `forum_topic` SET `flag_close` = 0 WHERE `id` = {$topic} LIMIT 1;");
    msg2('Вы открыли тему');
    knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic, 'В тему', 1);
    knopka('forum.php', 'Главная форума', 1);
    fin();
}

if ($mod == 'del' && 1 <= $f['admin']) {
    if (empty($ok)) {
        msg2('Вы действительно хотите удалить эту тему?');
        knopka('forum.php?mod=del&ok=1&razdel=' . $razdel . '&topic=' . $topic, 'Удалить', 1);
        knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic, 'В тему', 1);
        knopka('forum.php', 'Главная форума', 1);
        fin();
    }
    $db->query("DELETE FROM `forum_topic` WHERE `id` = '{$topic}' LIMIT 1;");
    $db->query("DELETE FROM `forum_comm` WHERE `id_topic` = '{$topic}';");
    msg2('Вы удалили тему');
    if (!empty($razdel)) knopka('forum.php?razdel=' . $razdel, 'В раздел', 1);
    knopka('forum.php', 'Главная форума', 1);
    fin();
}

if ($mod == 'delete') {
    $q = $db->query("SELECT * FROM `forum_topic` WHERE `id` = '{$topic}' LIMIT 1;");
    if (!$q || $q->num_rows == 0) msg2('Тема не найдена', 1);
    $FRM = $q->fetch_assoc();
    $q = $db->query("SELECT * FROM `forum_comm` WHERE `id` = '{$cid}' AND `id_topic` = '{$topic}' LIMIT 1;");
    if (!$q || $q->num_rows == 0) msg2('Сообщение не найдено!', 1);
    $MSG = $q->fetch_assoc();
    if ((($f['login'] == $MSG['login'] || $f['login'] == $FRM['login']) && $FRM['flag_close'] == 0) || $f['admin'] > 0) {
        $db->query("DELETE FROM `forum_comm` WHERE `id` = '{$cid}' AND `id_topic` = '{$topic}' LIMIT 1;");
        header("Location: forum.php?razdel=" . $razdel . "&topic=" . $topic . "&start=" . $start);
        fin();
    } else {
        msg2('Вы не можете удалить это сообщение!');
        knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic, 'Вернуться', 1);
        knopka('forum.php', 'Главная форума', 1);
        fin();
    }
}

// --- Просмотр темы ---
$q = $db->query("SELECT * FROM `forum_topic` WHERE `id` = {$topic} LIMIT 1;");
$ft = $q ? $q->fetch_assoc() : null;
if (!$ft) msg2('Тема не существует!', 1);

// Запись комментария
if ($write == 1 && 1 < mb_strlen($mess, 'UTF-8') && $ft['flag_close'] == 0) {
    $mess_esc = $db->real_escape_string($mess);
    $mess_short = mb_substr($mess_esc, 0, 512, 'UTF-8');
    $login_esc = $db->real_escape_string($f['login']);
    $a = $db->query("SELECT * FROM `forum_comm` WHERE `login` = '{$login_esc}' ORDER BY `id` DESC LIMIT 1;");
    $b = $a ? $a->fetch_assoc() : null;
    if (($b['message'] ?? '') != $mess_short) {
        $db->query("INSERT INTO `forum_comm` VALUES (0, '{$login_esc}', {$topic}, {$razdel}, '{$mess_short}', '{$t}');");
        $db->query("UPDATE `forum_topic` SET `lastcomm` = '{$t}' WHERE `id` = {$topic} LIMIT 1;");
    }
}

// Ответ на конкретное сообщение
if (!empty($lgn) && $ft['flag_close'] == 0 && !empty($cid)) {
    $TestLGN = get_login($lgn);
    $lgn = $TestLGN['login'];
    if ($cid <= 0) msg2('Сообщение не найдено', 1);
    $lgn_esc = $db->real_escape_string($lgn);
    $q = $db->query("SELECT * FROM `forum_comm` WHERE `login` = '{$lgn_esc}' AND `id` = '{$cid}' AND `id_topic` = '{$topic}' LIMIT 1;");
    $otv = $q ? $q->fetch_assoc() : null;
    if (!$otv) msg2('Сообщение не найдено', 1);
    if (empty($mess)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="forum.php?razdel=' . $razdel . '&topic=' . $topic . '&lgn=' . $lgn . '&start=' . $start . '&cid=' . $cid . '" method="POST">';
        echo 'Пишем <a href="infa.php?mod=uzinfa&lgn=' . $lgn . '"><b>' . $lgn . '</b></a><br/>';
        echo '<input type="text" name="mess" maxlength="512" style="width:80%"/><input type="submit" value="Ответ"/>';
        echo '</form></div>';
        knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic, 'В тему', 1);
        knopka('forum.php', 'Главная форума', 1);
        fin();
    }
    $mess_esc = $db->real_escape_string($mess);
    $mess_short = mb_substr($mess_esc, 0, 512, 'UTF-8');
    $mess_short = '<b>' . $lgn . '</b>, ' . $mess_short;
    $login_esc = $db->real_escape_string($f['login']);
    $a = $db->query("SELECT * FROM `forum_comm` WHERE `login` = '{$login_esc}' ORDER BY `id` DESC LIMIT 1;");
    $b = $a ? $a->fetch_assoc() : null;
    if (($b['message'] ?? '') != $mess_short) {
        $db->query("INSERT INTO `forum_comm` VALUES (0, '{$login_esc}', {$topic}, {$razdel}, '{$mess_short}', '{$t}');");
        $db->query("UPDATE `forum_topic` SET `lastcomm` = '{$t}' WHERE `id` = {$topic} LIMIT 1;");
        $b = $db->query("SELECT COUNT(*) AS `c` FROM `forum_comm` WHERE `id_topic` = {$topic};");
        $cc = $b ? (int)$b->fetch_assoc()['c'] : 0;
        $page = (int)(($cc - 1) / $numb) * $numb;
        header("Location: forum.php?razdel=" . $razdel . "&topic=" . $ft['id'] . "&start=" . $page);
        fin();
    }
}

knopka('forum.php?razdel=' . $razdel . '&topic=' . $topic . '&start=' . $start, 'Обновить', 1);
knopka('forum.php?razdel=' . $razdel, 'Вернуться в раздел', 1);

echo '<div class="board2" style="text-align:left">';
echo '<b>' . $ft['name'] . '</b> (by ' . $ft['login'] . ')';
if (empty($full)) {
    echo ' <a href="forum.php?razdel=' . $razdel . '&topic=' . $topic . '&full=1">[полн.просм]</a>';
} else {
    echo ' <a href="forum.php?razdel=' . $razdel . '&topic=' . $topic . '&full=0">[как обычно]</a>';
}
if ((2 <= $f['admin'] || $f['login'] == $ft['login']) && $ft['flag_close'] == 0) {
    echo ' <a href="forum.php?razdel=' . $razdel . '&topic=' . $topic . '&mod=edit">[ред.]</a>';
}
echo '</div>';

if ($f['grafika'] == 1 || $f['grafika'] == 2) $ft['message'] = smile(link_it(nl2br($ft['message'])));
else $ft['message'] = link_it(nl2br($ft['message']));
echo '<div class="board2" style="text-align:left">' . $ft['message'] . '</div>';

if ($ft['flag_close'] == 0) {
    echo '<div class="board">';
    echo '<form action="forum.php?razdel=' . $razdel . '&topic=' . $topic . '&write=1&start=' . $start . '&full=' . $full . '" method="POST">';
    echo '<input type="text" name="mess" maxlength="512" style="width:80%"/><input type="submit" value="Ответ"/>';
    echo '</div>';
}

if (empty($full)) {
    $q = $db->query("SELECT COUNT(*) AS `c` FROM `forum_comm` WHERE `id_topic` = " . (int)$ft['id'] . ";");
    $all = $q ? (int)$q->fetch_assoc()['c'] : 0;
    if ($start > (int)($all / $numb)) $start = (int)($all / $numb);
    if ($start < 0) $start = 0;
    $limit = $start * $numb;
    $count = $limit;
    $q = $db->query("SELECT * FROM `forum_comm` WHERE `id_topic` = " . (int)$ft['id'] . " ORDER BY `time` DESC LIMIT {$limit}, {$numb};");
} else {
    $q = $db->query("SELECT * FROM `forum_comm` WHERE `id_topic` = " . (int)$ft['id'] . " ORDER BY `time` DESC;");
}

if ($q) {
    while ($fm = $q->fetch_assoc()) {
        echo '<div class="board2" style="text-align:left">';
        echo '<a href="forum.php?razdel=' . $razdel . '&topic=' . $topic . '&start=' . $start . '&lgn=' . $fm['login'] . '&cid=' . $fm['id'] . '"><b><span style="color:' . $male . '">' . $fm['login'] . '</span></b></a>';
        echo ' <small>[' . date('d.m.Y H:i', (int)$fm['time']) . ']</small>';
        if ((($f['login'] == $ft['login'] || $fm['login'] == $f['login']) && $ft['flag_close'] == 0) || $f['admin'] > 0) {
            echo ' <a href="forum.php?mod=delete&cid=' . $fm['id'] . '&razdel=' . $razdel . '&topic=' . $topic . '&start=' . $start . '"> [x] </a>';
        }
        echo '<br/>';
        if ($f['grafika'] == 1 || $f['grafika'] == 2) echo '&nbsp;' . smile(link_it($fm['message']));
        else echo '&nbsp;' . link_it($fm['message']);
        echo '</div>';
        $count++;
    }
}

if (empty($full) && $all > $numb) {
    echo '<div class="board">';
    if ($start > 0) echo '<a href="forum.php?razdel=' . $razdel . '&topic=' . $ft['id'] . '&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"> <-Назад</a>';
    echo ' | ';
    if ($limit + $numb < $all) echo '<a href="forum.php?razdel=' . $razdel . '&topic=' . $ft['id'] . '&start=' . ($start + 1) . '" class="navig" >Вперед-></a>'; else echo ' <a href="#" class="navig"> Вперед-></a>';
    echo '</div>';
}

if (!empty($razdel)) knopka('forum.php?razdel=' . $razdel, 'Вернуться в раздел', 1);
knopka('forum.php', 'Главная форума', 1);
if ((1 <= $f['admin'] || $f['login'] == $ft['login']) && $ft['flag_close'] == 0) knopka('forum.php?mod=close&razdel=' . $razdel . '&topic=' . $topic, 'Закрыть тему', 1);
if (1 <= $f['admin'] && $ft['flag_close'] == 1) knopka('forum.php?mod=open&razdel=' . $razdel . '&topic=' . $topic, 'Открыть тему', 1);
if (1 <= $f['admin']) knopka('forum.php?mod=del&razdel=' . $razdel . '&topic=' . $topic, 'Удалить тему', 1);

fin();
