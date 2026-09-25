<?php
/**
 * Использование лечилок/маны в бою (сумка).
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: битая кодировка, два case 191, Молния судьбы для игроков.
 */

$q = $db->query("SELECT `invent`.`id` FROM `invent`, `item` WHERE `invent`.`login` = '" . $db->real_escape_string($me['login']) . "' AND `invent`.`flag_rinok` = 0 AND `invent`.`flag_equip` = 1 AND `item`.`equip` = 'sumka' AND `invent`.`ido` = `item`.`id` LIMIT 1;");
if ($q === false || $q->num_rows === 0) {
    msg2('У вас ничего нет в сумке', 1);
}
$sum = $q->fetch_assoc();
$item = $items->shmot((int)$sum['id']);
if ($item === null) {
    msg2('Вещь не найдена', 1);
}
$log_hp = '';

// HP-зелья: [ido => восстановление HP]
$hpPotions = [
    153 => 50, 154 => 100, 155 => 150, 156 => 250,
    625 => 350, 626 => 500, 627 => 750, 628 => 1000, 629 => 1500,
];
// MP-зелья
$mpPotions = [
    191 => 50, 192 => 100, 193 => 150, 194 => 250,
    630 => 350, 631 => 500, 632 => 750, 633 => 1000, 634 => 1500,
];

$ido = (int)$item['ido'];

if (isset($hpPotions[$ido])) {
    $regen = $hpPotions[$ido];
    if ($regen + $me['hpnow'] > $me['hpmax']) $regen = $me['hpmax'] - $me['hpnow'];
    $me['hpnow'] += $regen;
    $log_hp = '<span style="color:' . $notice . '">' . $me['login'] . ' использует ' . $item['name'] . '</span><br/>';
    $db->query("DELETE FROM `invent` WHERE `login` = '" . $db->real_escape_string($me['login']) . "' AND `id` = " . (int)$sum['id'] . " LIMIT 1;");
    $db->query("UPDATE `users` SET `hpnow` = " . (int)$me['hpnow'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    $db->query("UPDATE `combat` SET `hpnow` = " . (int)$me['hpnow'] . " WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
} elseif (isset($mpPotions[$ido])) {
    $regen = $mpPotions[$ido];
    if ($regen + $me['mananow'] > $me['manamax']) $regen = $me['manamax'] - $me['mananow'];
    $me['mananow'] += $regen;
    $log_hp = '<span style="color:' . $notice . '">' . $me['login'] . ' использует ' . $item['name'] . '</span><br/>';
    $db->query("DELETE FROM `invent` WHERE `login` = '" . $db->real_escape_string($me['login']) . "' AND `id` = " . (int)$sum['id'] . " LIMIT 1;");
    $db->query("UPDATE `users` SET `mananow` = " . (int)$me['mananow'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    $db->query("UPDATE `combat` SET `mananow` = " . (int)$me['mananow'] . " WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
} elseif ($ido == 157) {
    // Молния судьбы
    $lgn = isset($_REQUEST['lgn']) ? (int)$_REQUEST['lgn'] : 0;
    $kom = ((int)$me['komanda'] == 1) ? 2 : 1;
    $kom3 = [];
    $q = $db->query("SELECT * FROM `combat` WHERE `boi_id` = {$bid} AND `komanda` = {$kom} AND `flag_bot` = 0;");
    if ($q) {
        while ($hz = $q->fetch_assoc()) {
            if ($hz['hpnow'] > 1) $kom3[(int)$hz['id']] = $hz['login'] . ' (' . $hz['hpnow'] . '/' . $hz['hpmax'] . ')';
        }
    }
    if (empty($lgn)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="battle.php?mod=sumka" method="POST">';
        echo '<select name="lgn">';
        foreach ($kom3 as $key => $val) echo '<option value="' . $key . '">' . $val . '</option>';
        echo '</select><br/>';
        echo '<input type="submit" value="Молния судьбы"></form></div>';
        knopka('battle.php', 'Вернуться', 1);
        fin();
    }
    if ($lgn <= 0) msg('Не выбран соперник для удара!', 1);
    $q = $db->query("SELECT * FROM `combat` WHERE `id` = {$lgn} AND `boi_id` = {$bid} LIMIT 1;");
    $hz = $q ? $q->fetch_assoc() : null;
    if (!$hz) msg2('Боец не найден!', 1);
    if ($hz['komanda'] == $me['komanda']) msg2('Против своей команды нельзя', 1);
    if ($hz['hpnow'] <= 0) msg2('Противник уже убит', 1);
    if ($hz['flag_bot'] == 1) msg2('Боец не найден!', 1);

    $log_hp = '<span style="color:' . $notice . '">Ветвистый удар молнии с треском бьет в ' . $hz['login'] . ', оставляя 1 ХП</span><br/>';
    // ИСПРАВЛЕНО: обновляем combat для игроков тоже
    $db->query("UPDATE `combat` SET `hpnow` = 1, `time_udar` = '{$t}' WHERE `id` = " . (int)$hz['id'] . " AND `hpnow` > 1 LIMIT 1;");
    $db->query("UPDATE `combat` SET `time_udar` = '{$t}' WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
    if (empty($hz['flag_bot'])) {
        $db->query("UPDATE `users` SET `hpnow` = 1 WHERE `login` = '" . $db->real_escape_string($hz['login']) . "' AND `hpnow` > 1 LIMIT 1;");
    }
    $db->query("DELETE FROM `invent` WHERE `login` = '" . $db->real_escape_string($me['login']) . "' AND `id` = " . (int)$sum['id'] . " LIMIT 1;");
} else {
    msg('Неизвестная ошибка', 1);
}

if (!empty($log_hp)) {
    $db->query("INSERT INTO `battlelog` VALUES (0, {$bid}, '{$t}', '{$log_hp}');");
}
