<?php
##############
# 24.12.2014 #
##############
require_once('inc/top.php');	// вывод на экран
require_once('inc/check.php');	// вход в игру
require_once('inc/head.php');
require_once('class/items.php');	// работа с вещами
require_once('inc/boi.php');		//создание боя + завод игрока в бой
require_once('inc/bot.php');		//параметры бота + создание бота

$go = isset($_REQUEST['go']) ? intval($_REQUEST['go']) : 0;
$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';
$iid = isset($_REQUEST['iid']) ? $_REQUEST['iid'] : 0;
$lvl = isset($_REQUEST['lvl']) ? $_REQUEST['lvl'] : 0;
$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : 0;
$keystring = isset($_REQUEST['keystring']) ? $_REQUEST['keystring'] : '';
if(empty($f['kvest']))
	{
	$f['kvest'] = serialize(array());
	$q = $db->query("update `users` set kvest='{$f['kvest']}' where id='{$f['id']}' limit 1;");
	}

// меню
if($f['status'] == 1)
	{
	knopka('battle.php', 'Вы в бою!', 1);
	fin();
	}
// каждый квест в отдельном файле
switch($f['loc']):
case 15: require_once('kvest/trava.php'); break;
case 18: require_once('kvest/trava.php'); break;
case 23: require_once('kvest/loc23.php'); break;
case 31: require_once('kvest/trava.php'); break;
case 45: require_once('kvest/loc45.php'); break;
case 51: require_once('kvest/trava.php'); break;
case 56: require_once('kvest/loc56.php'); break;
case 68: require_once('kvest/trava.php'); break;
case 69: require_once('kvest/loc69.php'); break;
case 77: require_once('kvest/loc77.php'); break;
case 78: require_once('kvest/trava.php'); break;
case 83: require_once('kvest/trava.php'); break;
case 99: require_once('kvest/loc99.php'); break;
case 105: require_once('kvest/loc105.php'); break;
case 106: require_once('kvest/loc106.php'); break;
case 107: require_once('kvest/loc107.php'); break;
case 109: require_once('kvest/loc109.php'); break;
default: msg('<a href="loc.php">Ошибка локации</a>'); break;
endswitch;
?>
