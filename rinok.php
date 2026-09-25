<?php
/**
 * Рынок: продажа и покупка вещей между игроками.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: пробел в URL навигации '&to= ' → '&to='.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';

if ($f['loc'] != 1) {
    knopka('loc.php', 'Ошибка локации', 1);
    fin();
}
if ($f['status'] == 1) {
    knopka('battle.php', 'Вы в бою!', 1);
    fin();
}
if ($f['hpnow'] <= 0) {
    knopka('loc.php', 'Восстановите здоровье', 1);
    fin();
}

$mod   = isset($_REQUEST['mod'])   ? $_REQUEST['mod'] : '';
$num   = isset($_REQUEST['num'])   ? (int)$_REQUEST['num']   : 0;
$iid   = isset($_REQUEST['iid'])   ? (int)$_REQUEST['iid']   : 0;
$ok    = isset($_REQUEST['ok'])    ? 1 : 0;
$to    = isset($_REQUEST['to'])    ? (int)$_REQUEST['to']    : 0;
$slot  = isset($_REQUEST['slot'])  ? (int)$_REQUEST['slot']  : 0;
$summa = isset($_REQUEST['summa']) ? (int)$_REQUEST['summa'] : 0;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$lvl   = isset($_REQUEST['lvl'])   ? (int)$_REQUEST['lvl']   : 0;

require_once __DIR__ . '/inc/hpstring.php';

if (empty($mod)) {
    msg2('Под навесом сидят несколько человек в богатых одеяниях. Судя по надписи на табличке, вы можете купить какую-либо поношеную вещь, или отдать на продажу свою.');
    knopka('rinok.php?mod=search', 'Смотреть вещи', 1);
    knopka('rinok.php?mod=lot', 'Выставить вещи', 1);
    knopka('rinok.php?mod=myitem', 'Мои вещи на рынке', 1);
    fin();
}

if ($mod == 'lot') {
    if (empty($iid)) {
        $numb = 15;
        $count = 0;
        $login_esc = $db->real_escape_string($f['login']);
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `login` = '{$login_esc}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 AND `flag_pered` = 1;");
        $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($all_itm <= 0) msg2('У вас нет вещей на продажу.', 1);
        else msg2('Вы можете выставить на рынок:');
        if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        $q = $db->query("SELECT `id` FROM `invent` WHERE `login` = '{$login_esc}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 AND `flag_pered` = 1 ORDER BY `time` DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($invent = $q->fetch_assoc()) {
                echo '<div class="board2" style="text-align:left">';
                $item = $items->shmot((int)$invent['id']);
                if ($item === null) continue;
                echo '<a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a>';
                echo ' <a href="rinok.php?mod=lot&iid=' . $item['id'] . '"><span style="color:' . $male . '">[выставить]</span></a><br/>';
                echo 'Цена ' . $item['price'];
                $count++;
                echo '</div>';
            }
        }
        if ($all_itm > $numb) {
            echo '<div class="board">';
            if ($start > 0) echo '<a href="rinok.php?mod=lot&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
            echo ' | ';
            if ($count + $numb < $all_itm) echo '<a href="rinok.php?mod=lot&start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
            echo '</div>';
        }
        fin();
    }
    if ($iid < 1) msg2('Вещь не найдена в вашем рюкзаке!', 1);
    $item = $items->shmot($iid);
    if ($item === null) msg2('Вещь не найдена в вашем рюкзаке!', 1);
    if (empty($ok)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="rinok.php?mod=lot&ok=1&iid=' . $iid . '" method="POST">';
        echo 'Вы хотите выставить на продажу ' . $item['name'] . '.<br/>';
        echo 'Цена: <input type="number" value="' . $item['price'] . '" name="summa"/><br/>';
        echo '<input type="submit" value="Далее"/></form></div>';
        knopka('rinok.php', 'Вернуться', 1);
        fin();
    }
    if ($summa < 50) msg2('Минимальная цена - 50 монет', 1);
    if ($summa <= (int)($item['price'] * 0.6)) msg2('Нельзя выставлять на продажу вещи ниже 60% от их цены.', 1);
    $db->query("UPDATE `invent` SET `flag_rinok` = 1, `rinok_price` = {$summa}, `time` = '{$t}' WHERE `id` = {$iid} LIMIT 1;");
    msg2('Вы выставили на продажу ' . $item['name'] . ' за ' . $summa . ' монет.');
    knopka('rinok.php?mod=lot', 'Выставить еще', 1);
    knopka('rinok.php', 'Вернуться', 1);
    fin();
}

if ($mod == 'myitem') {
    if (empty($iid)) {
        $numb = 15;
        $count = 0;
        $login_esc = $db->real_escape_string($f['login']);
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `login` = '{$login_esc}' AND `flag_rinok` = 1;");
        $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($all_itm <= 0) msg2('У вас нет вещей на рынке.', 1);
        else msg2('Ваши вещи на рынке:');
        if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        $q = $db->query("SELECT `id` FROM `invent` WHERE `login` = '{$login_esc}' AND `flag_rinok` = 1 ORDER BY `time` DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($invent = $q->fetch_assoc()) {
                $item = $items->shmot((int)$invent['id']);
                if ($item === null) continue;
                echo '<div class="board2" style="text-align:left"><a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a>';
                echo ' <a href="rinok.php?mod=myitem&iid=' . $item['id'] . '"><span style="color:' . $male . '">[забрать]</span></a>';
                echo '<br/>Уровень: ' . $item['lvl'] . ', Цена: ' . $item['rinok_price'] . '</div>';
                $count++;
            }
        }
        if ($all_itm > $numb) {
            echo '<div class="board">';
            if ($start > 0) echo '<a href="rinok.php?mod=myitem&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
            echo ' | ';
            if ($count + $numb < $all_itm) echo '<a href="rinok.php?mod=myitem&start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
            echo '</div>';
        }
        fin();
    }
    if ($iid < 1) msg2('Вещь не найдена на рынке.', 1);
    $q = $db->query("SELECT `id` FROM `invent` WHERE `id` = {$iid} AND `login` = '" . $db->real_escape_string($f['login']) . "' AND `flag_rinok` = 1 LIMIT 1;");
    $itm = $q ? $q->fetch_assoc() : null;
    if (!$itm) msg2('Вещь не найдена на рынке.', 1);
    $item = $items->shmot((int)$itm['id']);
    if ($item === null) msg2('Вещь не найдена на рынке.', 1);
    $db->query("UPDATE `invent` SET `flag_rinok` = 0, `rinok_price` = 0, `time` = '{$t}' WHERE `id` = {$iid} LIMIT 1;");
    msg2('Вы забрали с продажи ' . $item['name']);
    knopka('rinok.php?mod=myitem', 'Забрать еще', 1);
    fin();
}

if ($mod == 'search') {
    $q = $db->query("SELECT MAX(`lvl`) AS `m` FROM `item`;");
    $max_lvl = $q ? (int)$q->fetch_assoc()['m'] : 1;

    if (empty($ok)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="rinok.php?mod=search&ok=1" method="POST">';
        echo 'Уровень вещи:<br/><select name="lvl">';
        for ($i = 1; $i <= $max_lvl; $i++) echo '<option value=' . $i . '>' . $i . '</option>';
        echo '</select><br/>';
        echo '<input type="checkbox" name="to" value="1"/> Поиск по всем уровням<br/>';
        echo 'Слот:<br/><select name="slot">
        <option value="1">Без сортировки</option>
        <option value="2">Правая рука</option>
        <option value="3">Левая рука</option>
        <option value="4">Доспехи</option>
        <option value="5">Шлемы</option>
        <option value="6">Кольца</option>
        <option value="7">Амулеты</option>
        <option value="8">Сапоги</option>
        <option value="9">Плащи</option>
        <option value="10">Пояса</option>
        <option value="11">В сумку</option>
        <option value="12">Браслеты</option>
        <option value="13">Остальное</option>
        </select><br/>';
        echo '<input type="submit" value="Далее"/></form></div>';
        knopka('rinok.php', 'Вернуться', 1);
        fin();
    }

    if ($slot < 1 || $slot > 13) msg2('Неверный параметр слота!', 1);

    $append = '';
    $numb = 15;
    $count = 0;
    if ($slot == 2)  $append = 'prruka';
    if ($slot == 3)  $append = 'lruka';
    if ($slot == 4)  $append = 'dospeh';
    if ($slot == 5)  $append = 'golova';
    if ($slot == 6)  $append = 'kolco';
    if ($slot == 7)  $append = 'amulet';
    if ($slot == 8)  $append = 'nogi';
    if ($slot == 9)  $append = 'plaw';
    if ($slot == 10) $append = 'pojas';
    if ($slot == 11) $append = 'sumka';
    if ($slot == 12) $append = 'braslet';

    if ($lvl < 1) $lvl = 1;
    if ($max_lvl < $lvl) $lvl = $max_lvl;

    if (!empty($to)) {
        if ($slot == 1) {
            $q = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `flag_rinok` = 1;");
            $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
            if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
            if ($start < 0) $start = 0;
            $limit = $start * $numb;
            $q = $db->query("SELECT `id` FROM `invent` WHERE `flag_rinok` = 1 ORDER BY `time` DESC LIMIT {$limit}, {$numb};");
        } else {
            $append_esc = $db->real_escape_string($append);
            $q = $db->query("SELECT COUNT(`invent`.`id`) AS `c` FROM `invent`, `item` WHERE `invent`.`flag_rinok` = 1 AND `item`.`equip` = '{$append_esc}' AND `invent`.`ido` = `item`.`id`;");
            $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
            if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
            if ($start < 0) $start = 0;
            $limit = $start * $numb;
            $q = $db->query("SELECT `invent`.`id` FROM `invent`, `item` WHERE `invent`.`flag_rinok` = 1 AND `item`.`equip` = '{$append_esc}' AND `invent`.`ido` = `item`.`id` ORDER BY `invent`.`time` DESC LIMIT {$limit}, {$numb};");
        }
    } else {
        if ($slot == 1) {
            $q = $db->query("SELECT COUNT(`invent`.`id`) AS `c` FROM `invent`, `item` WHERE `item`.`lvl` = '{$lvl}' AND `invent`.`flag_rinok` = 1 AND `invent`.`ido` = `item`.`id`;");
            $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
            if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
            if ($start < 0) $start = 0;
            $limit = $start * $numb;
            $q = $db->query("SELECT `invent`.`id` FROM `invent`, `item` WHERE `item`.`lvl` = '{$lvl}' AND `invent`.`flag_rinok` = 1 AND `invent`.`ido` = `item`.`id` ORDER BY `invent`.`time` DESC LIMIT {$limit}, {$numb};");
        } else {
            $append_esc = $db->real_escape_string($append);
            $q = $db->query("SELECT COUNT(`invent`.`id`) AS `c` FROM `invent`, `item` WHERE `item`.`lvl` = '{$lvl}' AND `item`.`equip` = '{$append_esc}' AND `invent`.`flag_rinok` = 1 AND `invent`.`ido` = `item`.`id`;");
            $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
            if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
            if ($start < 0) $start = 0;
            $limit = $start * $numb;
            $q = $db->query("SELECT `invent`.`id` FROM `invent`, `item` WHERE `item`.`lvl` = '{$lvl}' AND `item`.`equip` = '{$append_esc}' AND `invent`.`flag_rinok` = 1 AND `invent`.`ido` = `item`.`id` ORDER BY `invent`.`time` DESC LIMIT {$limit}, {$numb};");
        }
    }

    if ($all_itm <= 0) msg2('Не найдено ни одной вещи на рынке.', 1);
    else msg2('Найдено на рынке:');

    if ($q) {
        while ($rinok = $q->fetch_assoc()) {
            $item = $items->shmot((int)$rinok['id']);
            if ($item === null) continue;
            $count++;
            echo '<div class="board2" style="text-align:left">' . $count . ') <a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a>';
            if ($item['login'] == $f['login']) {
                echo ' <a href="rinok.php?mod=myitem&iid=' . $item['id'] . '"><span style="color:' . $male . '">[забрать]</span></a>';
            } else {
                echo ' <a href="rinok.php?mod=kup&iid=' . $item['id'] . '"><span style="color:' . $male . '">[купить]</span></a>';
            }
            echo '<br/>';
            echo 'Уровень: ' . $item['lvl'];
            echo ' Цена: ' . $item['rinok_price'];
            echo '</div>';
        }
    }
    if ($all_itm > $numb) {
        echo '<div class="board">';
        // ИСПРАВЛЕНО: было '&to= '.$to
        if ($start > 0) echo '<a href="rinok.php?mod=search&lvl=' . $lvl . '&slot=' . $slot . '&ok=1&start=' . ($start - 1) . '&to=' . $to . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
        echo ' | ';
        if ($limit + $numb < $all_itm) echo '<a href="rinok.php?mod=search&lvl=' . $lvl . '&slot=' . $slot . '&ok=1&start=' . ($start + 1) . '&to=' . $to . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
        echo '</div>';
    }
    fin();
}

if ($mod == 'kup') {
    if ($iid < 1) msg2('Вещь не найдена на рынке!', 1);
    $q = $db->query("SELECT `id` FROM `invent` WHERE `id` = {$iid} AND `flag_rinok` = 1 LIMIT 1;");
    $rinok = $q ? $q->fetch_assoc() : null;
    if (!$rinok) msg2('Вещь не найдена на рынке!', 1);
    $item = $items->shmot((int)$rinok['id']);
    if ($item === null) msg2('Вещь не найдена на рынке!', 1);
    if ($item['login'] == $f['login']) msg2('Это ваша вещь, вы не можете её купить!', 1);
    if (empty($ok)) {
        msg('Вы уверены, что хотите купить ' . $item['name'] . '?');
        knopka('rinok.php?mod=kup&ok=1&iid=' . $iid, 'Купить', 1);
        knopka('loc.php', 'В игру', 1);
        fin();
    }
    if ($f['money'] < $item['rinok_price']) msg2('У вас недостаточно монет!', 1);
    $f['money'] -= $item['rinok_price'];
    $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    $db->query("UPDATE `users` SET `money` = `money` + " . (int)$item['rinok_price'] . " WHERE `login` = '" . $db->real_escape_string($item['login']) . "' LIMIT 1;");
    $log = $f['login'] . ' [' . $f['lvl'] . '] покупает на рынке ' . $item['name'] . ' [' . $item['lvl'] . '] (' . $item['price'] . ') за ' . $item['rinok_price'] . ' монет. Продавец ' . $item['login'];
    $db->query("INSERT INTO `log_peredach` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($item['login']) . "', '{$t}');");
    $db->query("UPDATE `invent` SET `login` = '" . $db->real_escape_string($f['login']) . "', `flag_rinok` = 0, `rinok_price` = 0, `time` = '{$t}' WHERE `id` = {$iid} LIMIT 1;");
    msg2('Вы купили ' . $item['name'] . '! Осталось ' . $f['money'] . ' монет.', 1);
}

fin();
