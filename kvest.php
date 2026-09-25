<?php
/**
 * Точка входа в квесты по локациям.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';
require_once __DIR__ . '/inc/boi.php';
require_once __DIR__ . '/inc/bot.php';

$go = isset($_REQUEST['go']) ? (int)$_REQUEST['go'] : 0;
$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';
$iid = isset($_REQUEST['iid']) ? (int)$_REQUEST['iid'] : 0;
$lvl = isset($_REQUEST['lvl']) ? (int)$_REQUEST['lvl'] : 0;
$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : 0;
$keystring = isset($_REQUEST['keystring']) ? $_REQUEST['keystring'] : '';

if (empty($f['kvest'])) {
    $f['kvest'] = serialize([]);
    $db->query("UPDATE `users` SET `kvest` = '" . $db->real_escape_string($f['kvest']) . "' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
}

if ($f['status'] == 1) {
    knopka('battle.php', 'Вы в бою!', 1);
    fin();
}

switch ((int)$f['loc']) {
    case 15: require_once __DIR__ . '/kvest/trava.php'; break;
    case 18: require_once __DIR__ . '/kvest/trava.php'; break;
    case 23: require_once __DIR__ . '/kvest/loc23.php'; break;
    case 31: require_once __DIR__ . '/kvest/trava.php'; break;
    case 45: require_once __DIR__ . '/kvest/loc45.php'; break;
    case 51: require_once __DIR__ . '/kvest/trava.php'; break;
    case 56: require_once __DIR__ . '/kvest/loc56.php'; break;
    case 68: require_once __DIR__ . '/kvest/trava.php'; break;
    case 69: require_once __DIR__ . '/kvest/loc69.php'; break;
    case 77: require_once __DIR__ . '/kvest/loc77.php'; break;
    case 78: require_once __DIR__ . '/kvest/trava.php'; break;
    case 83: require_once __DIR__ . '/kvest/trava.php'; break;
    case 99: require_once __DIR__ . '/kvest/loc99.php'; break;
    case 105: require_once __DIR__ . '/kvest/loc105.php'; break;
    case 106: require_once __DIR__ . '/kvest/loc106.php'; break;
    case 107: require_once __DIR__ . '/kvest/loc107.php'; break;
    case 109: require_once __DIR__ . '/kvest/loc109.php'; break;
    default: msg('<a href="loc.php">Ошибка локации</a>'); break;
}
