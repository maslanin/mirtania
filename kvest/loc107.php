<?php
/**
 * Навозная куча: наживка.
 * PHP 8.2-совместимая версия.
 */

if ($f['lvl'] < 6) {
    knopka('loc.php', 'Доступно с 6 уровня', 1);
    fin();
}
msg2('<b>Навозная куча</b>');

$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : '';
if (empty($ok)) {
    knopka('kvest.php?ok=1', 'Поискать вокруг кучи', 1);
    fin();
}

$rnd = mt_rand(1, 100);
$itm = 0;
if ($rnd >= 1 && $rnd <= 3) $itm = 166;
elseif ($rnd >= 4 && $rnd <= 8) $itm = 167;
elseif ($rnd >= 9 && $rnd <= 15) $itm = 170;
elseif ($rnd >= 16 && $rnd <= 25) $itm = 171;
elseif ($rnd >= 26 && $rnd <= 37) $itm = 172;
elseif ($rnd >= 38 && $rnd <= 50) $itm = 173;

if (!empty($itm)) {
    $item = $items->base_shmot($itm);
    $items->add_item($f['login'], $itm);
    msg2('Порыскав вокруг кучи, вы нашли наживку!<br/>[Найдено: ' . $item['name'] . ']');
} else {
    msg2('Вы ничего не нашли!');
}
knopka('loc.php', 'Вернуться', 1);
fin();
