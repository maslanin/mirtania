<?php
/**
 * Арена: заявки на PvP-бой.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО:
 *  - "delete ftom" → "delete from" (опечатка, из-за которой arena не удалялась).
 *  - toBoi выбирал всех с arena_id, включая komanda=0 и status!=2.
 *  - Добавлена проверка status=2 и komanda IN (1,2).
 *  - После старта боя сбрасываем arena_id/komanda/status у всех.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/inc/boi.php';

if ($f['status'] == 1) {
    knopka('battle.php', 'Вы в бою!', 1);
    fin();
}
if ($f['hpnow'] <= 0) {
    knopka('loc.php', 'Восстановите здоровье', 1);
    fin();
}
if ($f['loc'] != 1) {
    knopka('loc.php', 'Ошибка локации', 1);
    fin();
}

// Если игрок в заявке — проверим, не пора ли начинать
if ($f['status'] == 2) {
    $q = $db->query("SELECT * FROM `arena` WHERE `id` = " . (int)$f['arena_id'] . " LIMIT 1;");
    $a = $q ? $q->fetch_assoc() : null;
    if ($a) {
        $q1 = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `arena_id` = " . (int)$f['arena_id'] . " AND `komanda` = 1 AND `status` = 2;");
        $c1 = $q1 ? (int)$q1->fetch_assoc()['c'] : 0;
        $q2 = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `arena_id` = " . (int)$f['arena_id'] . " AND `komanda` = 2 AND `status` = 2;");
        $c2 = $q2 ? (int)$q2->fetch_assoc()['c'] : 0;

        $start = false;
        if ((int)$a['kom1'] <= $c1 && (int)$a['kom2'] <= $c2) $start = true;
        elseif ($c1 > 0 && $c2 > 0 && (int)$a['time'] <= $t) $start = true;

        if ($start) {
            $boi_id = addBoi(4);

            // ИСПРАВЛЕНО: выбираем только status=2 и komanda IN (1,2)
            $q = $db->query("SELECT * FROM `users` WHERE `arena_id` = " . (int)$a['id'] . " AND `status` = 2 AND `komanda` IN (1, 2);");
            if ($q) {
                while ($b = $q->fetch_assoc()) {
                    toBoi($b, (int)$b['komanda']);
                }
            }

            // ИСПРАВЛЕНО: было "delete ftom" — опечатка!
            $db->query("DELETE FROM `arena` WHERE `id` = " . (int)$a['id'] . " LIMIT 1;");
            // Сбросим заявку у всех
            $db->query("UPDATE `users` SET `arena_id` = 0, `komanda` = 0, `status` = 0 WHERE `arena_id` = " . (int)$a['id'] . ";");

            knopka('battle.php', 'Начинаем. В бой.', 1);
            $logboi = '<span style="color:' . $notice . '">' . date('H:i:s') . ' ' . $a['login'] . ' начинает бой на арене</span><br/>';
            $db->query("INSERT INTO `battlelog` VALUES (0, {$boi_id}, '{$t}', '" . $db->real_escape_string($logboi) . "');");
            $db->query("UPDATE `battle` SET `login` = '" . $db->real_escape_string($a['login']) . "' WHERE `id` = {$boi_id} LIMIT 1;");
            fin();
        }
    } else {
        // Заявка исчезла — сбросим статус
        $db->query("UPDATE `users` SET `status` = 0, `komanda` = 0, `arena_id` = 0 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        $f['status'] = 0;
    }
}

// Чистка просроченных заявок
$q = $db->query("SELECT `id` FROM `arena` WHERE `time` < '{$t}';");
if ($q) {
    while ($a = $q->fetch_assoc()) {
        $db->query("UPDATE `users` SET `status` = 0, `komanda` = 0, `arena_id` = 0 WHERE `arena_id` = " . (int)$a['id'] . ";");
        $db->query("DELETE FROM `arena` WHERE `id` = " . (int)$a['id'] . " LIMIT 1;");
    }
}

$mod    = isset($_REQUEST['mod'])    ? $_REQUEST['mod'] : '';
$aid    = isset($_REQUEST['aid'])    ? (int)$_REQUEST['aid'] : 0;
$kom    = isset($_REQUEST['kom'])    ? (int)$_REQUEST['kom'] : 0;
$comm   = isset($_REQUEST['comm'])   ? ekr($_REQUEST['comm']) : '';
$minlvl1 = isset($_REQUEST['minlvl1']) ? (int)$_REQUEST['minlvl1'] : 1;
$minlvl2 = isset($_REQUEST['minlvl2']) ? (int)$_REQUEST['minlvl2'] : 1;
$maxlvl1 = isset($_REQUEST['maxlvl1']) ? (int)$_REQUEST['maxlvl1'] : 1;
$maxlvl2 = isset($_REQUEST['maxlvl2']) ? (int)$_REQUEST['maxlvl2'] : 1;
$kol1   = isset($_REQUEST['kol1'])   ? (int)$_REQUEST['kol1'] : 1;
$kol2   = isset($_REQUEST['kol2'])   ? (int)$_REQUEST['kol2'] : 1;
$timer  = isset($_REQUEST['timer'])  ? (int)$_REQUEST['timer'] : 1;
$go     = isset($_REQUEST['go'])     ? (int)$_REQUEST['go'] : 0;
$ok     = isset($_REQUEST['ok'])     ? 1 : 0;

switch ($mod) {
    default:
        if ($f['status'] == 0) knopka('arena.php?mod=create', 'Подать заявку', 1);
        $q = $db->query("SELECT * FROM `arena` WHERE `time` > '{$t}' ORDER BY `id` DESC;");
        if (!$q || $q->num_rows == 0) {
            msg('Нет заявок на арене');
        } else {
            knopka('arena.php?r=' . mt_rand(111111, 999999), 'Обновить', 1);
            msg2('Активные заявки:');
            while ($a = $q->fetch_assoc()) {
                echo '<div class="board" style="text-align:left">';
                $qq = $db->query("SELECT `login`, `komanda`, `lvl` FROM `users` WHERE `komanda` <> 0 AND `arena_id` = " . (int)$a['id'] . " AND `status` = 2;");
                $count1 = 0;
                $count2 = 0;
                $str1 = '';
                $str2 = '';
                if ($qq) {
                    while ($b = $qq->fetch_assoc()) {
                        if ($b['komanda'] == 1) {
                            $count1++;
                            $str1 .= $b['login'] . ' [' . $b['lvl'] . '] ';
                        } else {
                            $count2++;
                            $str2 .= $b['login'] . ' [' . $b['lvl'] . '] ';
                        }
                    }
                }
                echo 'Команда 1 ';
                if ($count1 < $a['kom1'] && $f['status'] == 0) echo '<a href="arena.php?aid=' . $a['id'] . '&mod=enjoy&kom=1">>>></a> ';
                echo '(ур: ' . $a['minlvl1'] . '-' . $a['maxlvl1'] . '; ' . $count1 . ' из ' . $a['kom1'] . ': ' . $str1 . ')<br/>';
                echo 'Команда 2 ';
                if ($count2 < $a['kom2'] && $f['status'] == 0) echo '<a href="arena.php?aid=' . $a['id'] . '&mod=enjoy&kom=2">>>></a> ';
                echo '(ур: ' . $a['minlvl2'] . '-' . $a['maxlvl2'] . '; ' . $count2 . ' из ' . $a['kom2'] . ': ' . $str2 . ')<br/>';
                echo 'До боя: ' . ($a['time'] - $t) . ' сек.<br/>';
                if (!empty($a['comment'])) echo 'Комментарий: ' . $a['comment'] . '<br/>';
                if ($f['login'] == $a['login']) echo '<a href="arena.php?mod=del&aid=' . $a['id'] . '">Удалить заявку</a>';
                echo '</div>';
            }
        }
        fin();
        break;

    case 'del':
        if ($aid <= 0) msg2('Не выбрана заявка для удаления', 1);
        $q = $db->query("SELECT * FROM `arena` WHERE `id` = {$aid} AND `login` = '" . $db->real_escape_string($f['login']) . "' LIMIT 1;");
        $a = $q ? $q->fetch_assoc() : null;
        if (!$a) msg2('Заявка не найдена', 1);
        if (empty($ok)) {
            msg2('Вы уверены, что хотите удалить заявку на арене?');
            knopka('arena.php?mod=del&aid=' . $a['id'] . '&ok=1', 'Удалить', 1);
            knopka('arena.php', 'Вернуться', 1);
            fin();
        }
        $db->query("UPDATE `users` SET `arena_id` = 0, `status` = 0, `komanda` = 0 WHERE `arena_id` = " . (int)$a['id'] . ";");
        $db->query("DELETE FROM `arena` WHERE `id` = " . (int)$a['id'] . " LIMIT 1;");
        msg2('Вы удалили заявку на арене');
        knopka('arena.php', 'Вернуться', 1);
        knopka('loc.php', 'В игру', 1);
        fin();
        break;

    case 'enjoy':
        if (!empty($f['status'])) msg2('У вас уже есть заявка на арене', 1);
        if ($aid <= 0) msg2('Заявка не найдена', 1);
        $q = $db->query("SELECT * FROM `arena` WHERE `id` = {$aid} LIMIT 1;");
        $a = $q ? $q->fetch_assoc() : null;
        if (!$a) msg2('Заявка не найдена', 1);
        if ($kom != 1 && $kom != 2) msg2('Не правильно выбрана команда', 1);

        $q = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `arena_id` = {$aid} AND `komanda` = {$kom} AND `status` = 2;");
        $c = $q ? (int)$q->fetch_assoc()['c'] : 0;

        if ($kom == 1 && $a['kom1'] <= $c) msg2('В данной команде уже максимальное количество бойцов', 1);
        if ($kom == 1 && $f['lvl'] < $a['minlvl1']) msg2('Ваш уровень не подходит', 1);
        if ($kom == 1 && $a['maxlvl1'] < $f['lvl']) msg2('Ваш уровень не подходит', 1);
        if ($kom == 2 && $a['kom2'] <= $c) msg2('В данной команде уже максимальное количество бойцов', 1);
        if ($kom == 2 && $f['lvl'] < $a['minlvl2']) msg2('Ваш уровень не подходит', 1);
        if ($kom == 2 && $a['maxlvl2'] < $f['lvl']) msg2('Ваш уровень не подходит', 1);

        $db->query("UPDATE `users` SET `arena_id` = " . (int)$a['id'] . ", `komanda` = {$kom}, `status` = 2 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2('Вы приняли заявку на арене');
        knopka('arena.php', 'Вернуться', 1);
        knopka('loc.php', 'В игру', 1);
        fin();
        break;

    case 'create':
        if (!empty($f['status'])) msg2('У вас уже есть заявка на арене', 1);
        $q = $db->query("SELECT MAX(`lvl`) AS `m` FROM `item`;");
        $max_lvl = $q ? (int)$q->fetch_assoc()['m'] : 1;

        if (empty($ok)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="arena.php?mod=create&ok=1" method="POST">';
            echo '<b>1-я команда</b>:<br/>';
            echo 'Количество бойцов: <select name="kol1">';
            for ($i = 1; $i <= 20; $i++) echo '<option value="' . $i . '">' . $i . '</option>';
            echo '</select><br/>';
            echo 'Минимальный уровень: <select name="minlvl1">';
            for ($i = 1; $i <= $max_lvl; $i++) echo '<option value="' . $i . '">' . $i . '</option>';
            echo '</select><br/>';
            echo 'Максимальный уровень: <select name="maxlvl1">';
            for ($i = 1; $i <= $max_lvl; $i++) echo '<option value="' . $i . '">' . $i . '</option>';
            echo '</select><br/><br/>';
            echo '<b>2-я команда</b>:<br/>';
            echo 'Количество бойцов: <select name="kol2">';
            for ($i = 1; $i <= 20; $i++) echo '<option value="' . $i . '">' . $i . '</option>';
            echo '</select><br/>';
            echo 'Минимальный уровень: <select name="minlvl2">';
            for ($i = 1; $i <= $max_lvl; $i++) echo '<option value="' . $i . '">' . $i . '</option>';
            echo '</select><br/>';
            echo 'Максимальный уровень: <select name="maxlvl2">';
            for ($i = 1; $i <= $max_lvl; $i++) echo '<option value="' . $i . '">' . $i . '</option>';
            echo '</select><br/><br/>';
            echo '<b>Время до боя</b>: <select name="timer">';
            echo '<option value="1">1 минута</option>';
            echo '<option value="2">3 минуты</option>';
            echo '<option value="3">5 минут</option>';
            echo '<option value="4">10 минут</option>';
            echo '</select><br/>';
            echo 'Комментарий (можно оставить пустым):<br/><input type="text" name="comm"/>';
            echo '<input type="submit" value="Создать заявку"/></form></div>';
            knopka('arena.php', 'Вернуться', 1);
            fin();
        }

        if ($minlvl1 < 1 || $minlvl1 > $max_lvl) msg2('Неправильно выбран минимальный уровень 1 команды', 1);
        if ($minlvl2 < 1 || $minlvl2 > $max_lvl) msg2('Неправильно выбран минимальный уровень 2 команды', 1);
        if ($maxlvl1 < 1 || $maxlvl1 > $max_lvl) msg2('Неправильно выбран максимальный уровень 1 команды', 1);
        if ($maxlvl2 < 1 || $maxlvl2 > $max_lvl) msg2('Неправильно выбран максимальный уровень 2 команды', 1);
        if ($kol1 < 1 || $kol1 > 20) msg2('Неправильно выбрано количество бойцов 1 команды', 1);
        if ($kol2 < 1 || $kol2 > 20) msg2('Неправильно выбрано количество бойцов 2 команды', 1);
        if ($kol1 == 1 && ($f['lvl'] < $minlvl1 || $maxlvl1 < $f['lvl'])) $minlvl1 = $maxlvl1 = (int)$f['lvl'];
        if ($maxlvl1 < $minlvl1) $maxlvl1 = $minlvl1;
        if ($maxlvl2 < $minlvl2) $maxlvl2 = $minlvl2;
        if ($timer < 1 || $timer > 4) msg('Неправильно выбрано время боя', 1);
        if ($timer == 1) $do = 60;
        if ($timer == 2) $do = 180;
        if ($timer == 3) $do = 300;
        if ($timer == 4) $do = 600;
        $timer = $do + $t;

        $comm_esc = $db->real_escape_string($comm);
        $login_esc = $db->real_escape_string($f['login']);
        $db->query("INSERT INTO `arena` VALUES (0, '{$login_esc}', {$timer}, {$kol1}, {$kol2}, {$minlvl1}, {$maxlvl1}, {$minlvl2}, {$maxlvl2}, '{$comm_esc}');");
        $aid = $db->insert_id();
        $db->query("UPDATE `users` SET `arena_id` = {$aid}, `komanda` = 1, `status` = 2 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2('Вы создали заявку на арене');
        knopka('arena.php', 'Вернуться', 1);
        knopka('loc.php', 'В игру', 1);
        fin();
        break;
}

fin();
