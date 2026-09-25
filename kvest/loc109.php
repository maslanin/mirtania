<?php
/**
 * Кладбище животных: крючок.
 * PHP 8.2-совместимая версия.
 */

if ($f['lvl'] < 6) {
    knopka('loc.php', 'Доступно с 6 уровня', 1);
    fin();
}
if ($f['hpnow'] <= 0) {
    knopka('loc.php', 'Восстановите здоровье', 1);
    fin();
}

msg2('<b>Кладбище животных</b>');

$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : '';
if (empty($ok)) {
    knopka('kvest.php?ok=1', 'Осмотреть кости', 1);
    fin();
}

$rnd = mt_rand(1, 100);
if ($rnd >= 1 && $rnd <= 25) {
    $items->add_item($f['login'], 165);
    msg2('Наконец вы нашли подходящий кусок кости и вырезали крючок!');
} elseif ($rnd >= 26 && $rnd <= 95) {
    msg2('Вы хорошо искали, но так не нашли подходящей по форме кости!');
} elseif ($rnd >= 96 && $rnd <= 100) {
    $db->query("UPDATE `users` SET `hpnow` = -`hpnow`, `mananow` = -`mananow`, `hptime` = '{$t}', `manatime` = '{$t}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    msg2('Вы укололись острой костью!');
}
knopka('loc.php', 'Вернуться', 1);
fin();
