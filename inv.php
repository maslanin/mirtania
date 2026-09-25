<?php
/**
 * Инвентарь, экипировка, передача вещей, аренда.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: SQL-синтаксис в mod=arenda&go=6 (было "flag_rinok=0 flag_sklad=0 and and flag_equip=0").
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';
require_once __DIR__ . '/inc/boi.php';

require_once __DIR__ . '/inc/hpstring.php';

echo '<div class="menu">
<a href="inv.php">Начало</a> - 
<a href="inv.php?mod=log">Лог</a> - 
<a href="inv.php?mod=equip">Экипировка</a> - 
<a href="inv.php?mod=money">Передать монеты</a> - 
<a href="inv.php?mod=arenda">Аренда</a> - 
Монеты: ' . $f['money'] . '</div>';

$ok    = isset($_REQUEST['ok'])    ? $_REQUEST['ok'] : '';
$go    = isset($_REQUEST['go'])    ? (int)$_REQUEST['go'] : 0;
$slot  = isset($_REQUEST['slot'])  ? ekr($_REQUEST['slot']) : '';
$mod   = isset($_REQUEST['mod'])   ? $_REQUEST['mod'] : '';
$lgn   = isset($_REQUEST['lgn'])   ? ekr($_REQUEST['lgn']) : '';
$komm  = isset($_REQUEST['komm'])  ? ekr($_REQUEST['komm']) : 'no coment';
$summa = isset($_REQUEST['summa']) ? (int)$_REQUEST['summa'] : 0;
$iid   = isset($_REQUEST['iid'])   ? (int)$_REQUEST['iid']   : 0;
$komu  = isset($_REQUEST['komu'])  ? $_REQUEST['komu'] : '';
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$look  = isset($_REQUEST['look'])  ? (int)$_REQUEST['look']  : 0;

if ($f['hpnow'] <= 0) {
    knopka('loc.php', 'Восстановите здоровье', 1);
    fin();
}
if ($f['status'] == 1) {
    knopka('battle.php', 'Вы в бою', 1);
    fin();
}
if ($f['status'] == 2) {
    knopka('arena.php', 'У вас заявка на арене', 1);
    fin();
}

// --- Лог передач ---
if ($mod == 'log') {
    $numb = 25;
    $q = $db->query("SELECT COUNT(*) AS `c` FROM `log_peredach` WHERE `login` = '" . $db->real_escape_string($f['login']) . "' OR `login_per` = '" . $db->real_escape_string($f['login']) . "';");
    $all_log = $q ? (int)$q->fetch_assoc()['c'] : 0;
    if ($start > (int)($all_log / $numb)) $start = (int)($all_log / $numb);
    if ($start < 0) $start = 0;
    $limit = $start * $numb;
    $count = $limit;
    $q = $db->query("SELECT * FROM `log_peredach` WHERE `login` = '" . $db->real_escape_string($f['login']) . "' OR `login_per` = '" . $db->real_escape_string($f['login']) . "' ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
    if ($q) {
        while ($log = $q->fetch_assoc()) {
            $count++;
            echo '<div class="board2" style="text-align:left">';
            echo $count . '. ' . date('d.m.Y H:i', (int)$log['dateper']) . ' - ' . $log['log'];
            echo '</div>';
        }
    }
    echo '<div class="board">';
    if ($start > 0) echo '<a href="inv.php?mod=log&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
    echo ' | ';
    if ($limit + $numb < $all_log) echo '<a href="inv.php?mod=log&start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
    echo '</div>';
    fin();
}

// --- Использование вещи ---
if ($mod == 'useitem') {
    if ($iid <= 0) msg2('Вещь не найдена в вашем рюкзаке', 1);
    $q = $db->query("SELECT `id` FROM `invent` WHERE `login` = '" . $db->real_escape_string($f['login']) . "' AND `id` = {$iid} AND `flag_rinok` = 0 AND `flag_sklad` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 LIMIT 1;");
    $itm = $q ? $q->fetch_assoc() : null;
    if (!$itm) msg2('Вещь не найдена в вашем рюкзаке', 1);
    $item = $items->shmot((int)$itm['id']);
    if ($item === null) msg2('Вещь не найдена', 1);
    if ($f['lvl'] < $item['lvl']) msg2('Ваш уровень не подходит', 1);

    $svitki = [121, 122, 153, 154, 155, 156, 191, 192, 193, 194,
               620, 621, 622, 623, 624, 625, 626, 627, 628, 629,
               630, 631, 632, 633, 634, 635, 636, 637, 638, 639];
    $donate = [123, 124, 125, 126, 127];

    if (in_array((int)$item['ido'], $svitki, true)) {
        require __DIR__ . '/inc/svitki.php';
    } elseif (in_array((int)$item['ido'], $donate, true)) {
        require __DIR__ . '/inc/donate.php';
    } else {
        msg2('Эту вещь нельзя использовать.', 1);
    }
    fin();
}

// --- Передача монет ---
if ($mod == 'money') {
    if (empty($lgn) || empty($komm) || empty($summa)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="inv.php?mod=money" method="POST">';
        echo 'Передача монет. Все поля обязательны для заполнения.<br/><br/>
        Логин:<br/><input type="text" name="lgn" value="' . $lgn . '" maxlength="25"/><br/>';
        if ($f['vip'] < time()) echo '<span style="color:' . $female . '">Комиссия 1%</span><br/>';
        echo 'Сумма:<br/><input type="text" name="summa" maxlength="12"/><br/>';
        echo 'Комментарий:<br/><input type="text" name="komm" maxlength="100" style="width:80%"/><br/>';
        echo '<input type="submit" value="Далее" /></form></div>';
        fin();
    }
    if ($summa <= 0) msg2('Сумма не может быть меньше 1', 1);
    if ($f['money'] < $summa) msg2('Вы пытаетесь передать больше монет, чем у вас есть!', 1);
    $komm = mb_substr($komm, 0, 100, 'UTF-8');
    $bz = get_login($lgn);
    if ($f['login'] == $bz['login']) msg2('Нельзя передавать самому себе!', 1);
    $log = $f['login'] . ' [' . $f['lvl'] . '] (' . $f['klan'] . ') передает ' . $bz['login'] . ' [' . $bz['lvl'] . '] (' . $bz['klan'] . ') ' . $summa . ' монет - ' . $komm;
    $db->query("UPDATE `users` SET `money` = `money` - {$summa} WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    $db->query("UPDATE `users` SET `money` = `money` + {$summa} WHERE `id` = " . (int)$bz['id'] . " LIMIT 1;");
    $db->query("INSERT INTO `log_peredach` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($bz['login']) . "', '{$t}');");
    if ($f['vip'] < time()) {
        $proc = (int)ceil($summa * 0.01);
        if ($proc < 1) $proc = 1;
        $db->query("UPDATE `users` SET `money` = `money` - {$proc} WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    }
    msg2($summa . ' монет персонажу ' . $bz['login'] . ' успешно передано!');
}

// --- Передача вещи ---
if ($mod == 'item' && $iid > 0) {
    $item = $items->shmot($iid);
    if ($item === null) msg('Вещь не найдена в вашем рюкзаке!', 1);
    $it = $items->count_item($f['login'], $iid, 1);
    if (empty($item['flag_pered'])) msg('Данную вещь нельзя передать', 1);
    if (empty($summa) || empty($lgn)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="inv.php?mod=item&iid=' . $iid . '" method="POST">';
        echo 'Вы хотите передать ' . $item['name'] . '<br/>';
        echo '<small>У вас - ' . $it . ' шт.</small><br/>';
        echo '<br/>Логин:<br/><input type="text" name="lgn" value="' . $lgn . '" maxlength="30"/><br/>';
        echo 'Количество:<br/><input type="number" name="summa" value="' . $it . '" maxlength="12"/><br/>';
        echo 'Комментарий:<br/><input type="text" name="komm" maxlength="100" style="width:80%"/><br/>';
        echo '<input type="submit" value="Далее" /></form></div>';
        fin();
    }
    $komm = mb_substr($komm, 0, 100, 'UTF-8');
    if ($summa < 1) $summa = 1;
    if ($summa > $it) $summa = $it;
    $bz = get_login($lgn);
    if ($f['login'] == $bz['login']) msg2('Нельзя передавать самому себе!', 1);
    $login_esc = $db->real_escape_string($f['login']);
    $bz_esc = $db->real_escape_string($bz['login']);
    if ($summa == 1) {
        $db->query("UPDATE `invent` SET `login` = '{$bz_esc}', `time` = '{$t}' WHERE `login` = '{$login_esc}' AND `flag_rinok` = 0 AND `flag_pered` = 1 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 AND `id` = {$iid} LIMIT 1;");
        $log = $f['login'] . ' [' . $f['lvl'] . '] передал ' . $bz['login'] . ' [' . $bz['lvl'] . '] ' . $item['name'] . ' [' . $item['lvl'] . '] (' . $item['price'] . ') - ' . $komm;
    } else {
        $ido = (int)$item['ido'];
        $db->query("UPDATE `invent` SET `login` = '{$bz_esc}', `time` = '{$t}' WHERE `login` = '{$login_esc}' AND `flag_rinok` = 0 AND `flag_pered` = 1 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 AND `ido` = {$ido} ORDER BY `time` ASC LIMIT {$summa};");
        $log = $f['login'] . ' [' . $f['lvl'] . '] передал ' . $bz['login'] . ' [' . $bz['lvl'] . '] ' . $item['name'] . ' [' . $item['lvl'] . '] (' . $item['price'] . ') (' . $summa . ' шт.) - ' . $komm;
    }
    $db->query("INSERT INTO `log_peredach` VALUES (0, '{$login_esc}', '" . $db->real_escape_string($log) . "', '{$bz_esc}', '{$t}');");
    msg2($item['name'] . ' (' . $summa . ' шт.) персонажу ' . $bz['login'] . ' успешно передано!');
    knopka('inv.php?lgn=' . $lgn, 'Передать еще', 1);
}

// --- Аренда ---
if ($mod == 'arenda') {
    if (empty($go)) {
        knopka('inv.php?mod=arenda&go=2', 'Ваши вещи в аренде', 1);
        knopka('inv.php?mod=arenda&go=3', 'Вещи в аренде у вас', 1);
        fin();
    }

    if ($go == 1) {
        if ($iid < 1) msg2('Вещь не найдена!', 1);
        $item = $items->shmot($iid);
        if ($item === null) msg2('Вещь не найдена!', 1);
        if (empty($item['equip']) || $item['equip'] == 'sumka') msg2('Аренда данной вещи запрещена!', 1);
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `arenda` WHERE `idv` = {$iid};");
        if ($q && (int)$q->fetch_assoc()['c'] > 0) msg2('Вы уже предложили эту вещь в аренду!', 1);
        if (!empty($lgn)) {
            $tmp = get_login($lgn);
            $lgn = $tmp['login'];
        }
        if (empty($ok)) {
            echo '<form action="inv.php?mod=arenda&ok=1&go=1&iid=' . $iid . '" method="POST">';
            echo 'Кому даем ' . $item['name'] . '?<br/>';
            echo '<input type="text" name="lgn" value="' . $lgn . '"/><br/>';
            echo 'Цена аренды (за 1 сутки, 0-5000 монет):<br/>';
            echo '<input type="number" name="summa" value="0"/><br/>';
            echo 'На сколько дней? (1-90)<br/>';
            echo '<input type="number" name="look" value="1"/><br/>';
            echo '<input type="submit" value="Далее"/></form>';
            fin();
        }
        if ($summa < 0 || $summa > 5000) msg2('Цена аренды должна быть от 0 до 5000 монет за одни сутки аренды.', 1);
        if ($look < 0 || $look > 90) msg2('Количество дней аренды должно быть от 1 до 90.', 1);
        if ($f['login'] == $lgn) msg2('Нельзя давать себе вещи в аренду!', 1);
        $db->query("INSERT INTO `arenda` VALUES (0, '{$iid}', " . (int)$item['ido'] . ", {$summa}, {$look}, '{$t}', '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($lgn) . "');");
        msg2('Вы предложили ' . $item['name'] . ' персонажу ' . $lgn . ' на ' . $look . ' дней за ' . ($summa * $look) . ' монет.');
    } elseif ($go == 2) {
        $q = $db->query("SELECT * FROM `arenda` WHERE `login` = '" . $db->real_escape_string($f['login']) . "' ORDER BY `time` DESC;");
        if ($q && $q->num_rows > 0) {
            echo '<div class="board">';
            msg('Вы предложили в аренду:');
            while ($a = $q->fetch_assoc()) {
                $item = $items->shmot((int)$a['idv']);
                if ($item === null) continue;
                echo '<div class="board2" style="text-align:left"><b><a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a></b> (' . $a['arenda_login'] . ' за ' . $a['cena'] . ' монет) <a href="inv.php?mod=arenda&go=4&iid=' . $a['id'] . '">Отозвать</a></div>';
            }
            echo '</div>';
        }
        $q = $db->query("SELECT `id` FROM `invent` WHERE `flag_arenda` = 1 AND `login` = '" . $db->real_escape_string($f['login']) . "' ORDER BY `arenda_time` ASC;");
        if ($q && $q->num_rows > 0) {
            echo '<div class="board">';
            msg('Ваши вещи в аренде:');
            while ($a = $q->fetch_assoc()) {
                $item = $items->shmot((int)$a['id']);
                if ($item === null) continue;
                echo '<div class="board2" style="text-align:left"><b><a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a></b> (' . $item['arenda_login'] . ', до ' . date('d.m.Y H:i', (int)$item['arenda_time']) . ' за ' . $item['arenda_price'] . ' монет)</div>';
            }
            echo '</div>';
        }
        fin();
    } elseif ($go == 3) {
        $q = $db->query("SELECT * FROM `arenda` WHERE `arenda_login` = '" . $db->real_escape_string($f['login']) . "' ORDER BY `time` DESC;");
        if ($q && $q->num_rows > 0) {
            echo '<div class="board">';
            msg('Вам предлагают в аренду:');
            while ($a = $q->fetch_assoc()) {
                $item = $items->shmot((int)$a['idv']);
                if ($item === null) continue;
                echo '<div class="board2" style="text-align:left"><b><a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a></b> (' . $item['login'] . ' на ' . $item['srok'] . ' суток за ' . $a['cena'] . ' монет)';
                echo ' - <a href="inv.php?mod=arenda&go=5&iid=' . $a['id'] . '">Согласиться</a>';
                echo ' - <a href="inv.php?mod=arenda&go=6&iid=' . $a['id'] . '">Отказаться</a>';
                echo '</div>';
            }
            echo '</div>';
        }
        $q = $db->query("SELECT `id` FROM `invent` WHERE `flag_arenda` = 1 AND `arenda_login` = '" . $db->real_escape_string($f['login']) . "' ORDER BY `arenda_time` ASC;");
        if ($q && $q->num_rows > 0) {
            echo '<div class="board">';
            msg('Вещи у вас в аренде:');
            while ($a = $q->fetch_assoc()) {
                $item = $items->shmot((int)$a['id']);
                if ($item === null) continue;
                echo '<div class="board2" style="text-align:left"><b><a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a></b> (' . $item['login'] . ', до ' . date('d.m.Y H:i', (int)$item['arenda_time']) . ' за ' . $item['arenda_price'] . ' монет)</div>';
            }
            echo '</div>';
        }
        fin();
    } elseif ($go == 4) {
        if ($iid <= 0) msg2('Ошибка ID', 1);
        $db->query("DELETE FROM `arenda` WHERE `id` = {$iid} LIMIT 1;");
        msg2('Предложение аренды отозвано успешно.');
    } elseif ($go == 5) {
        if ($iid <= 0) msg2('Ошибка ID', 1);
        $q = $db->query("SELECT * FROM `arenda` WHERE `arenda_login` = '" . $db->real_escape_string($f['login']) . "' AND `id` = {$iid} LIMIT 1;");
        $a = $q ? $q->fetch_assoc() : null;
        if (!$a) msg2('Ошибка ID', 1);
        $q = $db->query("SELECT `id` FROM `invent` WHERE `id` = " . (int)$a['idv'] . " AND `login` = '" . $db->real_escape_string($a['login']) . "' AND `flag_rinok` = 0 AND `flag_sklad` = 0 AND `flag_equip` = 0 AND `flag_arenda` = 0 LIMIT 1;");
        $itm = $q ? $q->fetch_assoc() : null;
        if (!$itm) msg2('Вещь не найдена!', 1);
        $item = $items->shmot((int)$itm['id']);
        if ($item === null) msg2('Вещь не найдена!', 1);
        $srok = $t + (int)$a['srok'] * 86400;
        $db->query("UPDATE `invent` SET `arenda_login` = '" . $db->real_escape_string($f['login']) . "', `time` = '{$t}', `flag_equip` = 0, `flag_arenda` = 1, `arenda_price` = " . (int)$a['cena'] . ", `arenda_time` = '{$srok}' WHERE `id` = " . (int)$item['id'] . " LIMIT 1;");
        $db->query("UPDATE `users` SET `money` = `money` - " . (int)$a['cena'] . " WHERE `login` = '" . $db->real_escape_string($f['login']) . "' LIMIT 1;");
        $db->query("UPDATE `users` SET `money` = `money` + " . (int)$a['cena'] . " WHERE `login` = '" . $db->real_escape_string($a['login']) . "' LIMIT 1;");
        $db->query("DELETE FROM `arenda` WHERE `idv` = " . (int)$item['id'] . ";");
        $log = $a['login'] . ' дает в аренду ' . $f['login'] . ' ' . $item['name'] . ' (' . $item['lvl'] . 'lvl / ' . $item['price'] . ' мон) на ' . $a['srok'] . ' суток за ' . $a['cena'] . ' монет.';
        $db->query("INSERT INTO `log_peredach` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($a['login']) . "', '{$t}');");
        msg2('Предложение аренды успешно принято.');
    } elseif ($go == 6) {
        if ($iid <= 0) msg2('Ошибка ID', 1);
        $q = $db->query("SELECT * FROM `arenda` WHERE `arenda_login` = '" . $db->real_escape_string($f['login']) . "' AND `id` = {$iid} LIMIT 1;");
        $a = $q ? $q->fetch_assoc() : null;
        if (!$a) msg2('Ошибка ID', 1);
        // ИСПРАВЛЕНО: было "flag_rinok=0 flag_sklad=0 and and flag_equip=0"
        $q = $db->query("SELECT `id` FROM `invent` WHERE `id` = " . (int)$a['idv'] . " AND `login` = '" . $db->real_escape_string($a['login']) . "' AND `flag_rinok` = 0 AND `flag_sklad` = 0 AND `flag_equip` = 0 AND `flag_arenda` = 0 LIMIT 1;");
        $itm = $q ? $q->fetch_assoc() : null;
        if (!$itm) msg2('Вещь не найдена!', 1);
        $db->query("DELETE FROM `arenda` WHERE `idv` = " . (int)$itm['id'] . " AND `id` = {$iid} LIMIT 1;");
        msg2('Вы успешно отказались от аренды.');
    }
}

// --- Экипировка ---
if ($mod == 'equip') {
    $q = $db->query("SELECT `invent`.`id` FROM `invent`, `item` WHERE ((`invent`.`login` = '" . $db->real_escape_string($f['login']) . "' AND `invent`.`flag_arenda` = 0) OR (`invent`.`arenda_login` = '" . $db->real_escape_string($f['login']) . "' AND `invent`.`flag_arenda` = 1)) AND `invent`.`flag_rinok` = 0 AND `invent`.`flag_equip` = 1 AND `invent`.`flag_sklad` = 0 AND (`item`.`equip` <> '') AND `invent`.`ido` = `item`.`id`;");
    if (!$q || $q->num_rows == 0) msg2('На вас ничего не надето!', 1);
    while ($eq = $q->fetch_assoc()) {
        $item = $items->shmot((int)$eq['id']);
        if ($item === null) continue;
        echo '<div class="board2" style="text-align:left;">';
        if ($item['equip'] == 'prruka')  echo '<b>Правая рука</b>: ';
        if ($item['equip'] == 'lruka')   echo '<b>Левая рука</b>: ';
        if ($item['equip'] == 'dospeh')  echo '<b>Доспех</b>: ';
        if ($item['equip'] == 'golova')  echo '<b>Голова</b>: ';
        if ($item['equip'] == 'kolco')   echo '<b>Кольцо</b>: ';
        if ($item['equip'] == 'amulet')  echo '<b>Амулет</b>: ';
        if ($item['equip'] == 'nogi')    echo '<b>Ноги</b>: ';
        if ($item['equip'] == 'plaw')    echo '<b>Плащ</b>: ';
        if ($item['equip'] == 'braslet') echo '<b>Браслет</b>: ';
        if ($item['equip'] == 'pojas')   echo '<b>Пояс</b>: ';
        if ($item['equip'] == 'sumka')   echo '<b>В сумке</b>: ';
        echo '<b><a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a></b>';
        echo ' <a href="inv.php?mod=drop_equip&slot=' . $item['equip'] . '"><span style="color:' . $male . '">[снять]</span></a>';
        echo '</div>';
    }
    knopka('inv.php?mod=drop_equip_all', 'Снять все вещи', 1);
    knopka('inv.php', 'Вернуться', 1);
    fin();
}

if ($mod == 'equip_item') {
    if ($iid < 1) msg2('Такой вещи нет!', 1);
    $q = $db->query("SELECT `id` FROM `invent` WHERE ((`login` = '" . $db->real_escape_string($f['login']) . "' AND `flag_arenda` = 0) OR (`arenda_login` = '" . $db->real_escape_string($f['login']) . "' AND `flag_arenda` = 1)) AND `flag_rinok` = 0 AND `flag_sklad` = 0 AND `flag_equip` = 0 AND `id` = {$iid} LIMIT 1;");
    $itm = $q ? $q->fetch_assoc() : null;
    if (!$itm) msg2('Такой вещи нет!', 1);
    $item = $items->shmot((int)$itm['id']);
    if ($item === null) msg2('Такой вещи нет!', 1);
    if (empty($item['equip'])) msg2('Эту вещь нельзя экипировать!', 1);
    if ($f['zdor'] < $item['zdor'] || $f['sila'] < $item['sila'] || $f['inta'] < $item['inta'] || $f['lovka'] < $item['lovka'] || $f['intel'] < $item['intel'] || $f['lvl'] < $item['lvl']) {
        msg('Ваши параметры не подходят под требования вещи!');
        echo '<div class="board2" style="text-align:left">';
        echo 'Требования вещи/Ваши параметры: Уровень ' . $item['lvl'] . '/' . $f['lvl'] . ', Сила ' . $item['sila'] . '/' . $f['sila'] . ', Интуиция ' . $item['inta'] . '/' . $f['inta'] . ', Ловкость ' . $item['lovka'] . '/' . $f['lovka'] . ', Интеллект ' . $item['intel'] . '/' . $f['intel'] . ', Здоровье ' . $item['zdor'] . '/' . $f['zdor'] . '</div>';
        fin();
    }
    $items->equip_item($f['login'], (int)$item['id']);
    $f = calcparam($f);
    msg2('Вы экипировали ' . $item['name']);
}

if ($mod == 'drop_equip' && !empty($slot)) {
    $q = $db->query("SELECT `invent`.`id` FROM `invent`, `item` WHERE `invent`.`login` = '" . $db->real_escape_string($f['login']) . "' AND `invent`.`flag_equip` = 1 AND `item`.`equip` = '{$slot}' AND `invent`.`ido` = `item`.`id` LIMIT 1;");
    if (!$q || $q->num_rows == 0) msg2('Здесь у вас ничего не надето', 1);
    $itm = $q->fetch_assoc();
    $item = $items->shmot((int)$itm['id']);
    if ($item === null) msg2('Вещь не найдена', 1);
    $items->drop_equip($f['login'], $slot);
    $f = calcparam($f);
    msg2('Вы сняли ' . $item['name']);
}

if ($mod == 'drop_equip_all') {
    $items->drop_equip_all($f['login']);
    $f = calcparam($f);
    msg2('Вы сняли всю экипировку.');
}

if ($mod == 'menu') {
    if ($iid <= 0) msg2('Такой вещи нет.', 1);
    $item = $items->shmot($iid);
    if ($item === null) msg2('Такой вещи нет.', 1);
    msg2('<b>' . $item['name'] . ' [' . $item['lvl'] . ']</b>');
    knopka('infa.php?mod=iteminfa&iid=' . $iid, 'Описание', 1);
    if (!empty($item['equip'])) knopka('inv.php?mod=equip_item&iid=' . $item['id'], '<span style="color:' . $male . '">Экипировать</span>', 1);
    knopka('inv.php?mod=useitem&iid=' . $item['id'], '<span style="color:' . $notice . '">Использовать</span>', 1);
    if (!empty($item['flag_pered']) && $item['login'] == $f['login']) knopka('inv.php?mod=item&iid=' . $item['id'] . '&start=' . $start . '&lgn=' . $lgn, '<span style="color:' . $male . '">Передать</span>', 1);
    if ($item['login'] == $f['login'] && !empty($item['equip']) && $item['equip'] != 'sumka') knopka('inv.php?mod=arenda&iid=' . $item['id'] . '&lgn=' . $lgn . '&go=1', '<span style="color:' . $manacolor . '">Дать в аренду</span>', 1);
    knopka('inv.php', 'В рюкзак', 1);
    fin();
}

// --- Показ рюкзака ---
$numb = 15;
$count = 0;

if (!empty($lgn)) {
    $tmp = get_login($lgn);
    $lgn = $tmp['login'];
    msg2('Вы готовы передать вещи ' . $lgn . '.');
}

$login_esc = $db->real_escape_string($f['login']);

if (empty($look)) {
    $q = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE ((`login` = '{$login_esc}' AND `flag_arenda` = 0) OR (`arenda_login` = '{$login_esc}' AND `flag_arenda` = 1)) AND `flag_rinok` = 0 AND `flag_sklad` = 0 AND `flag_equip` = 0;");
    $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
    if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
    if ($start < 0) $start = 0;
    $limit = $start * $numb;
    $count = $limit;
    $q = $db->query("SELECT MIN(`id`) AS `id`, COUNT(*) AS `c` FROM `invent` WHERE ((`login` = '{$login_esc}' AND `flag_arenda` = 0) OR (`arenda_login` = '{$login_esc}' AND `flag_arenda` = 1)) AND `flag_rinok` = 0 AND `flag_sklad` = 0 AND `flag_equip` = 0 GROUP BY `ido` ORDER BY MAX(`time`) DESC LIMIT {$limit}, {$numb};");
    if ($q) {
        while ($invent = $q->fetch_assoc()) {
            echo '<div class="board2" style="text-align:left">';
            $count++;
            $item = $items->shmot((int)$invent['id']);
            if ($item === null) continue;
            echo $count . ') <a href="inv.php?mod=menu&iid=' . $item['id'] . '&lgn=' . $lgn . '">' . $item['name'] . '</a>';
            if (!empty($item['equip'])) echo ' <a href="inv.php?mod=equip_item&iid=' . $item['id'] . '"><span style="color:' . $male . '">Экипировать</span></a>';
            echo '<br/>уров: ' . $item['lvl'] . ', цена: ' . $item['price'];
            if ($invent['c'] > 1) echo '<br/>Количество: <a href="inv.php?look=' . $item['ido'] . '&lgn=' . $lgn . '"><b>' . $invent['c'] . '</b></a>';
            echo '</div>';
        }
    }
} else {
    if ($look <= 0) msg2('Ошибка!', 1);
    $q = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE ((`login` = '{$login_esc}' AND `flag_arenda` = 0) OR (`arenda_login` = '{$login_esc}' AND `flag_arenda` = 1)) AND `flag_rinok` = 0 AND `flag_sklad` = 0 AND `flag_equip` = 0 AND `ido` = {$look};");
    $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
    if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
    if ($start < 0) $start = 0;
    $limit = $start * $numb;
    $q = $db->query("SELECT `id` FROM `invent` WHERE ((`login` = '{$login_esc}' AND `flag_arenda` = 0) OR (`arenda_login` = '{$login_esc}' AND `flag_arenda` = 1)) AND `flag_rinok` = 0 AND `flag_sklad` = 0 AND `flag_equip` = 0 AND `ido` = {$look} ORDER BY `time` DESC LIMIT {$start}, {$numb};");
    if ($q) {
        while ($invent = $q->fetch_assoc()) {
            echo '<div class="board2" style="text-align:left">';
            $count++;
            $item = $items->shmot((int)$invent['id']);
            if ($item === null) continue;
            echo $count . ') <a href="inv.php?mod=menu&iid=' . $item['id'] . '&lgn=' . $lgn . '">' . $item['name'] . '</a>';
            if (!empty($item['equip'])) echo ' <a href="inv.php?mod=equip_item&iid=' . $item['id'] . '"><span style="color:' . $male . '">Экипировать</span></a>';
            echo '<br/>уров: ' . $item['lvl'] . ', цена: ' . $item['price'];
            echo '</div>';
        }
    }
}

if ($all_itm > $numb) {
    echo '<div class="board">';
    if ($start > 0) echo '<a href="inv.php?start=' . ($start - 1) . '&lgn=' . $lgn . '&look=' . $look . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
    echo ' | ';
    if ($limit + $numb < $all_itm) echo '<a href="inv.php?start=' . ($start + 1) . '&lgn=' . $lgn . '&look=' . $look . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
    echo '</div>';
}

fin();
