<?php
/**
 * Наковальня: модификация вещей.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: IDOR — не проверялось, что вещь принадлежит игроку.
 */

msg('Вы можете улучшить (ну или ухудшить, уж как получится) любой предмет экипировки. Цена 500 монет за каждый уровень вещи.');

$iid   = isset($_REQUEST['iid'])   ? (int)$_REQUEST['iid'] : 0;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$limit = 0;
$numb = 15;

if ($mod == 'tochkam') {
    if ($f['ruda'] < 20) msg('У вас нехватает руды. У вас ' . $f['ruda'] . ' из 20', 1);
    if (empty($go)) {
        msg('Вы уверены что хотите потратить 20 руды на точильный камень?');
        knopka('kvest.php?mod=tochkam&go=1', 'Да, продолжить!');
        knopka('loc.php', 'В игру');
        fin();
    }
    $db->query("UPDATE `users` SET `ruda` = `ruda` - 20 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    $items->add_item($f['login'], 127, 1);
    msg('Точильный камень успешно куплен, списано 20 руды', 1);
}

if (20 <= $f['ruda']) {
    knopka('kvest.php?mod=tochkam', 'Купить точильный камень (20 руды)');
}

if (empty($iid)) {
    $login_esc = $db->real_escape_string($f['login']);
    $q = $db->query("SELECT COUNT(`invent`.`id`) AS `c` FROM `invent`, `item` WHERE `invent`.`login` = '{$login_esc}' AND `invent`.`flag_arenda` = 0 AND `invent`.`flag_rinok` = 0 AND `invent`.`flag_sklad` = 0 AND `invent`.`flag_equip` = 0 AND (`item`.`equip` <> '' AND `item`.`equip` <> 'sumka') AND `invent`.`up` = 0 AND `item`.`lvl` > 5 AND `invent`.`ido` = `item`.`id`;");
    $all_itm = $q ? (int)$q->fetch_assoc()['c'] : 0;
    if (empty($all_itm)) msg('У вас нет подходящих вещей для модификации', 1);
    if ($start > (int)($all_itm / $numb)) $start = (int)($all_itm / $numb);
    if ($start < 0) $start = 0;
    $limit = $start * $numb;
    $count = $limit;

    $q = $db->query("SELECT `invent`.`id` FROM `invent`, `item` WHERE `invent`.`login` = '{$login_esc}' AND `invent`.`flag_arenda` = 0 AND `invent`.`flag_rinok` = 0 AND `invent`.`flag_sklad` = 0 AND `invent`.`flag_equip` = 0 AND (`item`.`equip` <> '' AND `item`.`equip` <> 'sumka') AND `invent`.`up` = 0 AND `item`.`lvl` > 5 AND `invent`.`ido` = `item`.`id` LIMIT {$limit}, {$numb};");
    if ($q) {
        while ($invent = $q->fetch_assoc()) {
            echo '<div class="board2" style="text-align:left">';
            $count++;
            $item = $items->shmot((int)$invent['id']);
            if ($item === null) continue;
            echo $count . ') <a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '">' . $item['name'] . '</a>';
            echo ' <a href="kvest.php?iid=' . $item['id'] . '"><span style="color:' . $male . '">Улучшить</span></a>';
            echo '<br/>уров: ' . $item['lvl'] . ', цена модификации: ' . ($item['lvl'] * 500);
            echo '</div>';
        }
    }
    if ($all_itm > $numb) {
        echo '<div class="board">';
        if ($start > 0) echo '<a href="kvest.php?start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
        echo ' | ';
        if ($limit + $numb < $all_itm) echo '<a href="kvest.php?start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
        echo '</div>';
    }
    fin();
}

if ($iid <= 0) msg('Такая вещь не найдена в рюкзаке.', 1);

// ИСПРАВЛЕНО: проверяем, что вещь принадлежит игроку
$login_esc = $db->real_escape_string($f['login']);
$q = $db->query("SELECT `id` FROM `invent` WHERE `id` = {$iid} AND `login` = '{$login_esc}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 LIMIT 1;");
if (!$q || $q->num_rows == 0) msg('Такая вещь не найдена в рюкзаке.', 1);
$item = $items->shmot($iid);
if ($item === null) msg('Такая вещь не найдена в рюкзаке.', 1);

if (!empty($item['up'])) msg('Вещь уже была модифицирована ранее.', 1);
if ($item['lvl'] < 6) msg('Модифицировать можно вещи с 6 уровня.', 1);
if (empty($item['equip']) || $item['equip'] == 'sumka') msg('Эта вещь не подлежит модификации', 1);
$cena = (int)$item['lvl'] * 500;
if ($cena > $f['money']) msg('У вас недостаточно монет для модификации данной вещи.', 1);

if (empty($ok)) {
    msg('Вы хотите модифицировать ' . $item['name'] . ' за ' . $cena . ' монет?');
    knopka('kvest.php?iid=' . $iid . '&ok=1', 'Продолжаем');
    knopka('loc.php', 'Да вы что, мне очень страшно! * Убежать');
    fin();
}

$upgr = mt_rand(1, 25);
if (mt_rand(1, 100) <= 50) $upgr *= -1;
$up = ($upgr > 0) ? '+' . $upgr . '%' : $upgr . '%';

$db->query("UPDATE `invent` SET `up` = '{$upgr}', `time` = '{$t}' WHERE `id` = {$iid} AND `login` = '{$login_esc}' LIMIT 1;");
$db->query("UPDATE `users` SET `money` = `money` - {$cena} WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");

$klan_str = !empty($f['klan']) ? $f['klan'] : '';
$log = $f['login'] . ' [' . $f['lvl'] . '] (' . $klan_str . ') модифицирует ' . $item['name'] . ' (id: ' . $item['id'] . ') на ' . $up;
$db->query("INSERT INTO `log_peredach` VALUES (0, '{$login_esc}', '" . $db->real_escape_string($log) . "', '', '{$t}');");

msg('Неуклюже постукав молотком, вы модифицируете ' . $item['name'] . ' на ' . $upgr . '%');
knopka('kvest.php', 'Модифицировать еще одну вещь');
knopka('loc.php', 'Уйти');
fin();
