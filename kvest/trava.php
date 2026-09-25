<?php
/**
 * Сбор трав.
 * PHP 8.2-совместимая версия.
 */

if ($f['hpnow'] <= 0) {
    knopka('loc.php', 'Восстановите здоровье', 1);
    fin();
}

$herbs = [
    15 => 644,
    18 => 705,
    31 => 706,
    51 => 642,
    68 => 641,
    78 => 643,
    83 => 640,
];

$loc = (int)$f['loc'];
if (!isset($herbs[$loc])) {
    msg('Ошибка локации', 1);
}
$res = $herbs[$loc];

$kvest = !empty($f['kvest']) ? @unserialize($f['kvest']) : [];
if (!is_array($kvest)) $kvest = [];

$key = 'loc' . $loc;
if (empty($kvest[$key])) {
    $kvest[$key]['date'] = 0;
    $f['kvest'] = serialize($kvest);
}

$time = (int)$kvest[$key]['date'] - time();
$item = $items->base_shmot($res);
if ($time > 0) msg2($item['name'] . ' поспеет через ' . ceil($time / 60) . ' минут.', 1);

$kvest[$key]['date'] = time() + (60 * 60 * 6);
$items->add_item($f['login'], $res);
msg2('Вы собрали ' . $item['name']);
$f['kvest'] = serialize($kvest);
$db->query("UPDATE `users` SET `kvest` = '" . $db->real_escape_string($f['kvest']) . "' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
knopka('loc.php', 'В игру', 1);
fin();
