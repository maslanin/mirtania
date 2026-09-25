<?php
/**
 * Арт-эффекты в бою.
 * PHP 8.2-совместимая версия.
 */

$art_uron   = 0;
$art_hp     = 0;
$s_art_uron = 0;
$s_art_hp   = 0;

/**
 * Применить арт-эффект для одного бойца.
 */
function apply_art(&$actor, $art_name, &$uron_zaudar, &$art_uron, &$art_hp, $db, $side)
{
    if (empty($actor['art'][$art_name])) {
        return;
    }
    $lvl = (int)$actor['lvl'];
    $power = (int)$actor['art'][$art_name];

    if ($art_name === 'лечение' || $art_name === 'исцеление') {
        if (mt_rand(1, 100) <= 70 && $actor['hpnow'] < $actor['hpmax']) {
            if ($art_name === 'лечение') {
                $hp = mt_rand((int)ceil($lvl * 1.5), $lvl * 4 * $power);
            } else {
                $hp = mt_rand($lvl * 3, $lvl * 5 * $power);
            }
            $actor['hpnow'] += $hp;
            if ($actor['hpnow'] > $actor['hpmax']) $actor['hpnow'] = $actor['hpmax'];
            if (empty($actor['flag_bot'])) {
                $db->query("UPDATE `users` SET `hpnow` = " . (int)$actor['hpnow'] . " WHERE `login` = '" . $db->real_escape_string($actor['login']) . "' LIMIT 1;");
            }
            $db->query("UPDATE `combat` SET `hpnow` = " . (int)$actor['hpnow'] . " WHERE `id` = " . (int)$actor['id'] . " LIMIT 1;");
            $art_hp += $hp;
        }
    } elseif ($art_name === 'вампиризм') {
        if (mt_rand(1, 100) <= 70) {
            $hp = mt_rand((int)ceil($lvl * 1.5), $lvl * 4 * $power);
            $actor['hpnow'] += $hp;
            $uron_zaudar += $hp;
            if ($actor['hpnow'] > $actor['hpmax']) $actor['hpnow'] = $actor['hpmax'];
            if (empty($actor['flag_bot'])) {
                $db->query("UPDATE `users` SET `hpnow` = " . (int)$actor['hpnow'] . " WHERE `login` = '" . $db->real_escape_string($actor['login']) . "' LIMIT 1;");
            }
            $db->query("UPDATE `combat` SET `hpnow` = " . (int)$actor['hpnow'] . " WHERE `id` = " . (int)$actor['id'] . " LIMIT 1;");
            $art_hp += $hp;
            $art_uron += $hp;
        }
    } elseif (in_array($art_name, ['огонь', 'пламя', 'ветер', 'лед'], true)) {
        if (mt_rand(1, 100) <= 70) {
            $uron = mt_rand($lvl * 3, $lvl * 5 * $power);
            $uron_zaudar += $uron;
            $art_uron += $uron;
        }
    }
}

if (!empty($me['art']) && is_array($me['art'])) {
    foreach (array_keys($me['art']) as $art_name) {
        apply_art($me, $art_name, $uron_zaudar, $art_uron, $art_hp, $db, 'me');
    }
}
if (!empty($uz['art']) && is_array($uz['art'])) {
    foreach (array_keys($uz['art']) as $art_name) {
        apply_art($uz, $art_name, $s_uron_zaudar, $s_art_uron, $s_art_hp, $db, 'uz');
    }
}
