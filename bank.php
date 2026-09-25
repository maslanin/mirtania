<?php
/**
 * Банк: вклады, проценты, склад.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';

if ($f['hpnow'] <= 0) {
    knopka('loc.php', 'Восстановите здоровье', 1);
    fin();
}

$mod   = isset($_REQUEST['mod'])   ? $_REQUEST['mod'] : '';
$ok    = isset($_REQUEST['ok'])    ? $_REQUEST['ok'] : '';
$summa = isset($_REQUEST['summa']) ? (int)$_REQUEST['summa'] : 0;
$iid   = isset($_REQUEST['iid'])   ? (int)$_REQUEST['iid']   : 0;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;

if ($f['loc'] != 1 && $f['loc'] != 37 && $f['loc'] != 91) {
    knopka('loc.php', 'Ошибка локации');
    fin();
}

// ИСПРАВЛЕНО: было !empty($_SERVER['auth'])
if (!empty($_SESSION['auth'])) {
    require_once __DIR__ . '/inc/hpstring.php';
}

$s = 'Ваши монеты: ' . $f['money'];
if (!empty($f['bank'])) $s .= ' | Ваш вклад: ' . $f['bank'] . ' от ' . date('d.m.Y H:i', (int)$f['bankdate']);
msg2($s);

if (empty($mod)) {
    msg2('В банке вы можете хранить ваши сбережения и вещи. Процент начисляется, если у вас на счету не меньше 100 монет.');
    knopka('bank.php?mod=set', 'Положить монеты', 1);
    knopka('bank.php?mod=get', 'Забрать монеты', 1);
    knopka('bank.php?mod=setitem', 'Положить вещи', 1);
    knopka('bank.php?mod=getitem', 'Забрать вещи', 1);
    if ($f['bank'] > 100) knopka('bank.php?mod=check', 'Проверить процент', 1);
    knopka('loc.php', 'В игру', 1);
    fin();
}

if ($mod == 'set') {
    if ($f['money'] <= 0) msg2('У вас нет монет', 1);
    if (empty($ok) || empty($summa)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="bank.php?mod=set&ok=1" method="POST">';
        echo 'Введите количество монет, которое вы хотите положить в банк:<br/>';
        echo '<input type="number" name="summa" value="0"/><br/>';
        echo '<input type="submit" value="Далее"/></form></div>';
        knopka('bank.php', 'Вернуться', 1);
        fin();
    }
    if ($summa > $f['money']) $summa = (int)$f['money'];
    if ($summa < 0) msg2('Количество не может быть отрицательным', 1);
    $f['money'] -= $summa;
    $f['bank']  += $summa;
    $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . ", `bank` = " . (int)$f['bank'] . ", `bankdate` = '{$t}' WHERE `login` = '" . $db->real_escape_string($f['login']) . "' LIMIT 1;");
    msg2('Вы положили в банк ' . $summa . ' монет.');
    knopka('bank.php', 'Далее', 1);
    fin();
}

if ($mod == 'get') {
    if ($f['bank'] <= 0) msg2('У вас нет монет в банке', 1);
    if (empty($ok) || empty($summa)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="bank.php?mod=get&ok=1" method="POST">';
        echo 'Введите количество монет, которое вы хотите забрать из банка:<br/>';
        echo '<input type="number" name="summa" value="' . $f['bank'] . '"/><br/>';
        echo '<input type="submit" value="Далее"/></form></div>';
        knopka('bank.php', 'Вернуться', 1);
        fin();
    }
    if ($summa > $f['bank']) $summa = (int)$f['bank'];
    if ($summa < 0) msg2('Количество не может быть отрицательным', 1);
    $f['bank']  -= $summa;
    $f['money'] += $summa;
    $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . ", `bank` = " . (int)$f['bank'] . ", `bankdate` = '{$t}' WHERE `login` = '" . $db->real_escape_string($f['login']) . "' LIMIT 1;");
    msg2('Вы забрали из банка ' . $summa . ' монет.');
    knopka('bank.php', 'Далее', 1);
    fin();
}

if ($mod == 'setitem') {
    if (empty($iid)) {
        $numb = 15;
        $count = 0;
        $login_esc = $db->real_escape_string($f['login']);
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `login` = '{$login_esc}' AND `flag_arenda` = 0 AND `flag_rinok` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0;");
        $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        if ($all_itm <= 0) msg2('У вас нет вещей в рюкзаке.', 1);
        else msg2('Вы можете положить на склад:');
        $q = $db->query("SELECT MIN(`id`) AS `id`, COUNT(*) AS `c` FROM `invent` WHERE `login` = '{$login_esc}' AND `flag_arenda` = 0 AND `flag_rinok` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 GROUP BY `ido` ORDER BY MAX(`time`), MIN(`id`) DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($invent = $q->fetch_assoc()) {
                $count++;
                $item = $items->shmot((int)$invent['id']);
                if ($item === null) continue;
                echo '<div class="board2" style="text-align:left">';
                echo $count . ') <a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a> (' . $item['lvl'] . ' уров.)';
                echo ' <a href="bank.php?mod=setitem&iid=' . $item['id'] . '">[на склад]</a>';
                if ($invent['c'] > 1) echo '<br/>Количество: <b>' . $invent['c'] . '</b>';
                echo '</div>';
            }
        }
        if ($all_itm > $numb) {
            echo '<div class="board">';
            if ($start > 0) echo '<a href="bank.php?mod=setitem&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
            echo ' | ';
            if ($limit + $numb < $all_itm) echo '<a href="bank.php?mod=setitem&start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
            echo '</div>';
        }
        fin();
    }
    if ($iid < 1) msg2('Вещь не найдена в вашем рюкзаке!', 1);
    $login_esc = $db->real_escape_string($f['login']);
    $res = $db->query("SELECT `id` FROM `invent` WHERE `id` = {$iid} AND `login` = '{$login_esc}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 ORDER BY `time`, `id` DESC LIMIT 1;");
    $itm = $res ? $res->fetch_assoc() : null;
    if (!$itm) msg2('Вещь не найдена в вашем рюкзаке!', 1);
    $item = $items->shmot((int)$itm['id']);
    if ($item === null) msg2('Вещь не найдена', 1);
    $summ = $items->count_item($f['login'], (int)$itm['id']);
    if (empty($summa)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="bank.php?mod=setitem&iid=' . $iid . '" method="POST">';
        echo 'Вы собираетесь положить на склад ' . $item['name'] . '<br/>';
        echo '<small>У вас - ' . $summ . ' шт.</small><br/>';
        echo 'Количество:<br/><input type="number" name="summa" value="' . $summ . '"/><br/>';
        echo '<input type="submit" value="Далее" /></div>';
        fin();
    }
    if ($summa <= 0) $summa = 1;
    if ($summa > $summ) $summa = $summ;
    if ($summa == 1) {
        $db->query("UPDATE `invent` SET `flag_sklad` = 1, `time` = '{$t}' WHERE `id` = {$iid} AND `login` = '{$login_esc}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 LIMIT 1;");
    } else {
        $ido = (int)$item['ido'];
        $db->query("UPDATE `invent` SET `flag_sklad` = 1, `time` = '{$t}' WHERE `ido` = {$ido} AND `login` = '{$login_esc}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 ORDER BY `time` DESC LIMIT {$summa};");
    }
    msg2('Вы положили на склад ' . $item['name'] . ' (' . $summa . ' шт.)');
    knopka('bank.php?mod=setitem', 'Далее', 1);
    fin();
}

if ($mod == 'getitem') {
    if (empty($iid)) {
        $numb = 15;
        $count = 0;
        $login_esc = $db->real_escape_string($f['login']);
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `login` = '{$login_esc}' AND `flag_sklad` = 1;");
        $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        if ($all_itm <= 0) msg2('У вас нет вещей на складе.', 1);
        else msg2('Вы можете забрать со склада:');
        $q = $db->query("SELECT MIN(`id`) AS `id`, COUNT(*) AS `c` FROM `invent` WHERE `login` = '{$login_esc}' AND `flag_sklad` = 1 GROUP BY `ido` ORDER BY MAX(`time`), MIN(`id`) DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($invent = $q->fetch_assoc()) {
                $count++;
                $item = $items->shmot((int)$invent['id']);
                if ($item === null) continue;
                echo '<div class="board2" style="text-align:left">';
                echo $count . ') <a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a> (' . $item['lvl'] . ' уров.)';
                echo ' <a href="bank.php?mod=getitem&iid=' . $item['id'] . '">[забрать]</a>';
                if ($invent['c'] > 1) echo '<br/>Количество: <b>' . $invent['c'] . '</b>';
                echo '</div>';
            }
        }
        if ($all_itm > $numb) {
            echo '<div class="board">';
            if ($start > 0) echo '<a href="bank.php?mod=getitem&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
            echo ' | ';
            if ($limit + $numb < $all_itm) echo '<a href="bank.php?mod=getitem&start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
            echo '</div>';
        }
        fin();
    }
    if ($iid < 1) msg2('Вещь не найдена на складе!', 1);
    $login_esc = $db->real_escape_string($f['login']);
    $res = $db->query("SELECT `id`, `ido` FROM `invent` WHERE `id` = {$iid} AND `login` = '{$login_esc}' AND `flag_sklad` = 1 ORDER BY `time`, `id` DESC LIMIT 1;");
    $item_row = $res ? $res->fetch_assoc() : null;
    if (!$item_row) msg2('Вещь не найдена на складе!', 1);
    $res = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `ido` = " . (int)$item_row['ido'] . " AND `login` = '{$login_esc}' AND `flag_sklad` = 1;");
    $summ = $res ? (int)$res->fetch_assoc()['c'] : 0;
    $item = $items->shmot((int)$item_row['id']);
    if ($item === null) msg2('Вещь не найдена', 1);
    if (empty($summa)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="bank.php?mod=getitem&iid=' . $iid . '" method="POST">';
        echo 'Вы собираетесь забрать со склада ' . $item['name'] . '<br/>';
        echo '<small>У вас - ' . $summ . ' шт.</small><br/>';
        echo 'Количество:<br/><input type="number" name="summa" value="' . $summ . '"/><br/>';
        echo '<input type="submit" value="Далее" /></div>';
        fin();
    }
    if ($summa <= 0) $summa = 1;
    if ($summa > $summ) $summa = $summ;
    if ($summa == 1) {
        $db->query("UPDATE `invent` SET `flag_sklad` = 0, `time` = '{$t}' WHERE `id` = {$iid} AND `login` = '{$login_esc}' AND `flag_sklad` = 1 LIMIT 1;");
    } else {
        $ido = (int)$item['ido'];
        $db->query("UPDATE `invent` SET `flag_sklad` = 0, `time` = '{$t}' WHERE `ido` = {$ido} AND `login` = '{$login_esc}' AND `flag_sklad` = 1 ORDER BY `time` DESC LIMIT {$summa};");
    }
    msg2('Вы забрали со склада ' . $item['name'] . ' (' . $summa . ' шт.)');
    knopka('bank.php?mod=getitem', 'Далее', 1);
    fin();
}

if ($mod == 'check') {
    if ($f['bank'] < 100) msg2('У вас в банке меньше 100 монет.', 1);
    if ($f['bankdate'] + 604800 > $t) msg2('Вам будет начислен процент ' . date('d.m.Y H:i', (int)$f['bankdate'] + 604800), 1);
    $srok = $t - (int)$f['bankdate'];
    $srok = (int)($srok / 604800);
    $oldmoney = (int)$f['bank'];
    $proc = 0.02;
    if ($f['vip'] > $t) $proc = 0.03;
    for ($i = $srok; $i > 0; $i--) {
        $bn = (int)($f['bank'] * $proc);
        if ($bn > 5000 && $f['vip'] > $t) $bn = 5000;
        if ($bn > 3000 && $f['vip'] < $t) $bn = 3000;
        $f['bank'] += $bn;
        $db->query("UPDATE `users` SET `bank` = " . (int)$f['bank'] . ", `bankdate` = `bankdate` + 604800 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    }
    $sum = $f['bank'] - $oldmoney;
    msg2('Вам начислено ' . $sum . ' монет по проценту в банке за ' . $srok . ' недель.');
    $log = $f['login'] . ' [' . $f['lvl'] . '] получает в банке ' . $sum . ' монет за ' . $srok . ' недель с вклада ' . $oldmoney . ' монет.';
    $db->query("INSERT INTO `log_peredach` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '', '{$t}');");
    knopka('bank.php', 'Далее', 1);
    fin();
}

fin();
