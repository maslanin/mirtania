<?php
/**
 * Использование свитков и эликсиров.
 * PHP 8.2-совместимая версия.
 */

$login_esc = $db->real_escape_string($f['login']);
$iid_esc   = (int)$item['id'];
$ido       = (int)$item['ido'];

// HP-зелья (вне боя)
$hpPotions = [
    153 => 50, 154 => 100, 155 => 150, 156 => 250,
    625 => 350, 626 => 500, 627 => 750, 628 => 1000, 629 => 1500,
];
// MP-зелья (вне боя)
$mpPotions = [
    191 => 50, 192 => 100, 193 => 150, 194 => 250,
    630 => 350, 631 => 500, 632 => 750, 633 => 1000, 634 => 1500,
];
// Допинг: [ido => тип, длительность]
$dopingItems = [
    620 => 6, 621 => 7, 622 => 8, 623 => 9, 624 => 10,
    635 => 1, 636 => 2, 637 => 3, 638 => 4, 639 => 5,
];

switch ($ido) {

    case 121: // свиток нападения
        if (empty($f['klan'])) msg2('Вы не в клане', 1);
        $komu = isset($_REQUEST['komu']) ? $_REQUEST['komu'] : '';
        if (empty($komu)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="inv.php?mod=useitem&iid=' . $iid_esc . '" method="POST">';
            echo 'Введите логин:<br/><input type="text" name="komu"/><br/>';
            echo '<input type="submit" value="Напасть"/></form></div>';
            knopka('inv.php', 'Вернуться', 1);
            fin();
        }
        $bz = get_login($komu);
        if ($f['login'] == $bz['login']) msg2('Нельзя напасть на себя', 1);
        if (empty($bz['klan'])) msg2('Персонаж не в клане', 1);
        if (in_array((int)$bz['loc'], [1, 37, 91], true)) msg2('Нападение невозможно, персонаж под защитой лагеря', 1);
        if ($bz['pvp'] == 0) msg2('Нападение невозможно, у персонажа выключен PvP статус', 1);
        if ($f['pvp'] == 0) msg2('Нападение невозможно, у Вас выключен PvP статус', 1);
        if (in_array((int)$f['loc'], [1, 37, 91], true)) msg2('Нападение из лагеря невозможно', 1);
        if ($bz['lastdate'] < $t - 300 && $bz['rabota'] < $t - 3600) msg2('Персонаж оффлайн', 1);
        if ($bz['hpnow'] <= 0) msg2('У персонажа отрицательное здоровье', 1);
        if ($bz['lvl'] < 6) msg2('Персонаж меньше 6 уровня и находится под защитой новичков', 1);

        if ($bz['status'] == 1) {
            $q = $db->query("SELECT `krov` FROM `battle` WHERE `id` = " . (int)$bz['boi_id'] . " LIMIT 1;");
            $a = $q ? $q->fetch_assoc() : null;
            if ($a && in_array((int)$a['krov'], [0, 1, 2], true)) msg('Персонаж находится в бою с ботами, нападение невозможно.', 1);
            if ($a && (int)$a['krov'] == 4) msg('Персонаж дерется на арене, нападение невозможно.', 1);
            $mykom = ((int)$bz['komanda'] == 1) ? 2 : 1;
            $q = $db->query("SELECT COUNT(*) AS `c` FROM `combat` WHERE `komanda` = {$mykom} AND `boi_id` = " . (int)$bz['boi_id'] . ";");
            $count = $q ? (int)$q->fetch_assoc()['c'] : 0;
            if ($count >= 10) msg2('В вашей команде уже максимальное количество бойцов - 10.', 1);
            $logboi = '<span style="color:' . $notice . '">' . date('H:i:s') . ' ' . $f['login'] . ' использует свиток нападения и нападает на ' . $bz['login'] . '</span><br/>';
            $db->query("INSERT INTO `battlelog` VALUES (0, " . (int)$bz['boi_id'] . ", '{$t}', '{$logboi}');");
            $items->del_item($f['login'], $item['id'], 1);
            $boi_id = $bz['boi_id'];
            toBoi($f, $mykom);
            knopka('battle.php', 'Вы напали на ' . $bz['login'], 1);
            fin();
        } else {
            $boi_id = addBoi(5);
            $logboi = '<span style="color:' . $notice . '">' . date('H:i:s') . ' <b>' . $f['login'] . ' использует свиток нападения и нападает на ' . $bz['login'] . '</b></span><br/>';
            $db->query("INSERT INTO `battlelog` VALUES (0, {$boi_id}, '{$t}', '{$logboi}');");
            $items->del_item($f['login'], $item['id'], 1);
            $db->query("UPDATE `users` SET `hpnow` = `hpmax`, `mananow` = `manamax` WHERE `id` = " . (int)$bz['id'] . " LIMIT 1;");
            $bz['hpnow'] = $bz['hpmax'];
            $bz['mananow'] = $bz['manamax'];
            toBoi($bz, 1);
            toBoi($f, 2);
            knopka('battle.php', 'Вы напали на ' . $bz['login'], 1);
            fin();
        }
        break;

    case 122: // свиток развоплощения
        if (empty($f['klan'])) msg('Вы не в клане', 1);
        $komu = isset($_REQUEST['komu']) ? $_REQUEST['komu'] : '';
        if (empty($komu)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="inv.php?mod=useitem&iid=' . $iid_esc . '" method="POST">';
            echo 'Введите логин:<br/><input type="text" name="komu"/><br/>';
            echo '<input type="submit" value="Напасть"/></form></div>';
            knopka('inv.php', 'Вернуться', 1);
            fin();
        }
        $bz = get_login($komu);
        if ($f['login'] == $bz['login']) msg2('Нельзя напасть на себя', 1);
        if (empty($bz['klan'])) msg2('Персонаж не в клане', 1);
        if ($bz['klan'] == $f['klan']) msg2('Нельзя нападать на соклан!', 1);
        if (in_array((int)$bz['loc'], [1, 37, 91], true)) msg2('Нападение невозможно, персонаж под защитой лагеря', 1);
        if ($bz['pvp'] == 0) msg2('Нападение невозможно, у персонажа выключен PvP статус', 1);
        if ($f['pvp'] == 0) msg2('Нападение невозможно, у Вас выключен PvP статус', 1);
        if (in_array((int)$f['loc'], [1, 37, 91], true)) msg2('Нападение из лагеря невозможно', 1);
        if ($bz['lastdate'] < $t - 300 && $bz['rabota'] < $t - 3600) msg2('Персонаж оффлайн', 1);
        if ($bz['hpnow'] <= 0) msg2('У персонажа отрицательное здоровье', 1);
        if ($bz['lvl'] < 6) msg2('Персонаж меньше 6 уровня и находится под защитой новичков', 1);
        if ($bz['status'] == 1) msg2('Персонаж в бою, нападение невозможно.', 1);

        $boi_id = addBoi(3);
        $logboi = '<span style="color:' . $male . '">' . date('H:i:s') . ' <b>' . $f['login'] . ' использует свиток развоплощения и нападает на ' . $bz['login'] . '</b></span><br/>';
        $db->query("INSERT INTO `battlelog` VALUES (0, {$boi_id}, '{$t}', '{$logboi}');");
        $items->del_item($f['login'], $item['id'], 1);
        $db->query("UPDATE `users` SET `hpnow` = `hpmax`, `mananow` = `manamax` WHERE `id` = " . (int)$bz['id'] . " LIMIT 1;");
        $bz['hpnow'] = $bz['hpmax'];
        $bz['mananow'] = $bz['manamax'];
        toBoi($bz, 1);
        toBoi($f, 2);
        knopka('battle.php', 'Вы напали на ' . $bz['login'], 1);
        fin();
        break;
}

// HP-зелья
if (isset($hpPotions[$ido])) {
    $regen = $hpPotions[$ido];
    $f['hpnow'] += $regen;
    if ($f['hpnow'] > $f['hpmax']) $f['hpnow'] = $f['hpmax'];
    $items->del_item($f['login'], $item['id'], 1);
    $db->query("UPDATE `users` SET `hpnow` = " . (int)$f['hpnow'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    msg2('Здоровье +' . $regen, 1);
}
// MP-зелья
if (isset($mpPotions[$ido])) {
    $regen = $mpPotions[$ido];
    $f['mananow'] += $regen;
    if ($f['mananow'] > $f['manamax']) $f['mananow'] = $f['manamax'];
    $items->del_item($f['login'], $item['id'], 1);
    $db->query("UPDATE `users` SET `mananow` = " . (int)$f['mananow'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    msg2('Мана +' . $regen, 1);
}
// Допинг
if (isset($dopingItems[$ido])) {
    $items->del_item($f['login'], $item['id'], 1);
    $timer = $t + 3600;
    $doping = (int)$dopingItems[$ido];
    $db->query("UPDATE `users` SET `doping` = {$doping}, `doping_time` = '{$timer}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    msg2('Вы выпили ' . $item['name'], 1);
}

fin();
