<?php
/**
 * Магазин: продажа и покупка вещей.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';

$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';

if ($f['status'] == 1) {
    knopka('battle.php', 'Вы в бою!', 1);
    fin();
}
if ($f['hpnow'] <= 0) {
    knopka('loc.php', 'Восстановите здоровье', 1);
    fin();
}
if (($f['loc'] != 1 && $f['loc'] != 37 && $f['loc'] != 91) && $mod != 'iteminfa') {
    knopka('loc.php', 'Ошибка локации', 1);
    fin();
}

$iid   = isset($_REQUEST['iid'])   ? (int)$_REQUEST['iid']   : 0;
$ok    = isset($_REQUEST['ok'])    ? 1 : 0;
$summa = isset($_REQUEST['summa']) ? (int)$_REQUEST['summa'] : 0;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$lvl   = isset($_REQUEST['lvl'])   ? (int)$_REQUEST['lvl']   : 0;

if (!empty($_SESSION['auth'])) require_once __DIR__ . '/inc/hpstring.php';

if (empty($mod)) {
    msg2('Монеты: ' . $f['money']);
    msg('Здравствуй, ' . $f['login'] . '! У меня ты можешь купить самые лучшие доспехи и оружие, а так же продать всякий ненужный хлам!');
    knopka('shop.php?mod=sell', 'Продать вещи', 1);
    knopka('shop.php?mod=bay', 'Купить вещи', 1);
    fin();
}

if ($mod == 'sell') {
    if (empty($iid)) {
        $numb = 15;
        $count = 0;

        $q = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `login` = '" . $db->real_escape_string($f['login']) . "' AND `flag_arenda` = 0 AND `flag_rinok` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0;");
        $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;

        if ($all_itm <= 0) msg2('У вас нет товара на продажу.', 1);
        else msg2('Вы можете продать:');

        $q = $db->query("SELECT MIN(`id`) AS `id`, COUNT(*) AS `c` FROM `invent` WHERE `login` = '" . $db->real_escape_string($f['login']) . "' AND `flag_arenda` = 0 AND `flag_rinok` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 GROUP BY `ido` ORDER BY MAX(`time`), MIN(`id`) DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($invent = $q->fetch_assoc()) {
                $item = $items->shmot((int)$invent['id']);
                if ($item === null) continue;
                echo '<div class="board2" style="text-align:left">';
                echo '<a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a> (' . $item['lvl'] . ' уров.)';
                $cena = (int)($item['price'] * 0.5);
                if ($f['vip'] > time()) $cena = (int)($item['price'] * 0.6);
                echo ' ' . $cena . ' монет';
                echo ' <a href="shop.php?mod=sell&iid=' . $item['id'] . '">[продать]</a>';
                if ($invent['c'] > 1) echo '<br/>Количество: <b>' . $invent['c'] . '</b>';
                $count++;
                echo '</div>';
            }
        }
        if ($all_itm > $numb) {
            echo '<div class="board">';
            if ($start > 0) echo '<a href="shop.php?mod=sell&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
            echo ' | ';
            if ($limit + $numb < $all_itm) echo '<a href="shop.php?mod=sell&start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
            echo '</div>';
        }
        fin();
    }

    if ($iid < 1) msg2('Вещь не найдена в вашем рюкзаке!', 1);
    $res = $db->query("SELECT `id` FROM `invent` WHERE `id` = {$iid} AND `login` = '" . $db->real_escape_string($f['login']) . "' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 ORDER BY `time`, `id` DESC LIMIT 1;");
    $itm = $res ? $res->fetch_assoc() : null;
    if (!$itm) msg2('Вещь не найдена в вашем рюкзаке!', 1);
    $summ = $items->count_item($f['login'], (int)$itm['id']);
    $item = $items->shmot((int)$itm['id']);
    if ($item === null) msg2('Вещь не найдена', 1);

    if (empty($summa)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="shop.php?mod=sell&iid=' . $iid . '" method="POST">';
        echo 'Вы хотите продать торговцу ' . $item['name'] . '<br/>';
        echo '<small>У вас - ' . $summ . ' шт.</small><br/>';
        echo 'Количество:<br/><input type="number" name="summa" value="' . $summ . '"/><br/>';
        echo '<input type="submit" value="Далее" /></div>';
        fin();
    }
    $summa = (int)$summa;
    if ($summa <= 0) $summa = 1;
    if ($summa > $summ) $summa = $summ;
    $money = (int)($item['price'] * 0.5) * $summa;
    if ($f['vip'] > time()) $money = (int)($item['price'] * 0.6) * $summa;
    $f['money'] += $money;
    if ($summa == 1) {
        $db->query("DELETE FROM `invent` WHERE `id` = {$iid} AND `login` = '" . $db->real_escape_string($f['login']) . "' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 LIMIT 1;");
    } else {
        $items->del_base_item($f['login'], (int)$item['ido'], $summa);
    }
    $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    msg2('Вы продали в магазин ' . $item['name'] . ' (' . $summa . ' шт.) за ' . $money . ' монет.');
    knopka('shop.php?mod=sell', 'Далее', 1);
    fin();
}

if ($mod == 'bay') {
    $q = $db->query("SELECT MAX(`lvl`) AS `m` FROM `item`;");
    $max_lvl = $q ? (int)$q->fetch_assoc()['m'] : 1;

    if (empty($lvl)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="shop.php?mod=bay" method="POST">
        На какой уровень желаете приобрести вещи?<br/>
        <select name="lvl">';
        for ($i = 1; $i <= $max_lvl; $i++) echo '<option value=' . $i . '>' . $i . '</option>';
        echo '</select><input type="submit" value="Далее"/></form></div>';
        knopka('shop.php', 'Вернуться', 1);
        fin();
    }
    if ($lvl < 1) $lvl = 1;
    if ($lvl > $max_lvl) $lvl = $max_lvl;

    require_once __DIR__ . '/inc/shop.php';

    if (empty($iid)) {
        msg2('Я продаю вещи:');
        if ($lvl >= 1 && $lvl <= 5) {
            $ids = [$lvl * 5 + 190, $lvl * 5 + 191, $lvl * 5 + 192, $lvl * 5 + 193, $lvl * 5 + 194];
            foreach ($ids as $id) {
                $item = $items->base_shmot($id);
                if ($item === null) continue;
                echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid=' . $id . '&lvl=' . $lvl . '">' . $item['name'] . '</a> <a href="shop.php?mod=iteminfa&iid=' . $id . '"><span style="color:' . $female . '">[infa]</span></a> (' . $item['price'] . ' монет)</div>';
            }
        } else {
            $sets = [
                'Комплект критовика' => [20, 0],
                'Комплект уворота'   => [20, 5],
                'Комплект танка'     => [20, 10],
                'Комплект универсала'=> [20, 15],
            ];
            foreach ($sets as $setName => $setData) {
                msg2($setName);
                for ($k = 0; $k < 5; $k++) {
                    $id = $lvl * $setData[0] + 100 + $setData[1] + $k;
                    $item = $items->base_shmot($id);
                    if ($item === null) continue;
                    echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid=' . $id . '&lvl=' . $lvl . '">' . $item['name'] . '</a> <a href="shop.php?mod=iteminfa&iid=' . $id . '"><span style="color:' . $female . '">[infa]</span></a> (' . $item['price'] . ' монет)</div>';
                }
            }
        }
        knopka('shop.php', 'Вернуться', 1);
        fin();
    }

    $iid = (int)$iid;
    if (!in_array($iid, $shop, true)) msg2('Такой вещи нет в продаже!', 1);
    $item = $items->base_shmot($iid);
    if ($item === null) msg2('Такой вещи нет в продаже!', 1);
    if ($item['price'] > $f['money']) msg2('У вас недостаточно монет!', 1);

    if (empty($ok)) {
        msg2('Вы уверены, что хотите купить ' . $item['name'] . '?');
        knopka('shop.php?mod=bay&iid=' . $iid . '&ok=1&lvl=' . $lvl, 'Купить', 1);
        knopka('inv.php', 'В игру', 1);
        fin();
    }

    $f['money'] -= $item['price'];
    $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    $items->add_item($f['login'], $iid, 1);
    msg2('Вы купили ' . $item['name'] . '! Осталось ' . $f['money'] . ' монет.');
    knopka('shop.php?mod=bay&lvl=' . $lvl, 'Далее', 1);
    fin();
}

if ($mod == 'iteminfa') {
    $iid = (int)$iid;
    if ($iid <= 0) msg2('Вещь не найдена!', 1);
    $item = $items->base_shmot($iid);
    if ($item === null) msg2('Вещь не найдена!', 1);
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    echo '<div class="board" style="text-align:left">';
    echo '<b>' . $item['name'] . ' [' . $item['lvl'] . ']</b>';
    echo '<br/>';
    if (!empty($item['art'])) echo 'Арт: ' . $item['art'] . '<br/>';
    echo 'Цена: ' . $item['price'] . '<br/>';
    echo '<hr/>';
    if (!empty($item['equip'])) {
        echo '<b>Характеристики</b>:';
        echo '<hr/>';
        if ($item['krit'] > 0)   echo 'Крит: '   . $item['krit']   . '<br/>';
        if ($item['uvorot'] > 0) echo 'Уворот: ' . $item['uvorot'] . '<br/>';
        if ($item['uron'] > 0 && $item['intel'] < $item['sila']) echo 'Урон: ' . $item['uron'] . '<br/>';
        if ($item['bron'] > 0)   echo 'Броня: '  . $item['bron']   . '<br/>';
        if ($item['hp'] > 0)     echo 'Бонус ХП: ' . $item['hp']   . '<br/>';
        echo '<hr/>';
        echo '<b>Требования</b>:';
        echo '<hr/>';
        if ($item['zdor'] > 0)  echo 'Здоровье: '  . $item['zdor']  . '<br/>';
        if ($item['sila'] > 0)  echo 'Сила: '      . $item['sila']  . '<br/>';
        if ($item['inta'] > 0)  echo 'Интуиция: '  . $item['inta']  . '<br/>';
        if ($item['lovka'] > 0) echo 'Ловкость: '  . $item['lovka'] . '<br/>';
        if ($item['intel'] > 0) echo 'Интеллект: ' . $item['intel'] . '<br/>';
        echo '<hr/>';
    }
    echo '<b>Описание</b>: ';
    if (!empty($item['info'])) echo $item['info'];
    else echo 'Нет';
    echo '</div>';
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    fin();
}

fin();
