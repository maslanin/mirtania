<?php
/**
 * Создание боя и добавление в него.
 * PHP 8.2-совместимая версия.
 */

/**
 * Создать бой.
 * @param int $s тип боя: 0=PvE, 1=?, 2=PvP, 3=свиток развоплощения, 4=арена, 5=свиток нападения
 * @return int ID боя
 */
function addBoi($s = 0)
{
    $db = DBC::instance();
    $s = (int)$s;
    $now = time();
    $db->query("INSERT INTO `battle` VALUES (0, 1, '', '', {$now}, {$now}, 0, {$s});");
    return $db->insert_id();
}

/**
 * Добавить бойца в бой.
 */
function toBoi($usr, $komanda)
{
    global $boi_id;
    $db = DBC::instance();
    $boi_id  = (int)$boi_id;
    $komanda = (int)$komanda;
    $now = time();

    $login = $db->real_escape_string($usr['login']);
    $sila  = (int)$usr['sila'];
    $inta  = (int)$usr['inta'];
    $lovka = (int)$usr['lovka'];
    $lvl   = (int)$usr['lvl'];
    $hpnow = (int)$usr['hpnow'];
    $hpmax = (int)$usr['hpmax'];
    $mananow = (int)$usr['mananow'];
    $manamax = (int)$usr['manamax'];
    $id    = (int)$usr['id'];

    $db->query("INSERT INTO `combat` VALUES (0, '{$login}', {$sila}, {$inta}, {$lovka}, 0, 0, 0, 0, {$lvl}, {$hpnow}, {$hpmax}, {$mananow}, {$manamax}, {$boi_id}, 0, {$komanda}, 0, '{$now}', '{$now}', 0);");
    $db->query("UPDATE `users` SET `status` = 1, `boi_id` = {$boi_id}, `arena_id` = 0, `komanda` = 0, `lastdate` = '{$now}' WHERE `id` = {$id} LIMIT 1;");
    return 0;
}
