<?php
##############
# 24.12.2014 #
##############

if ($f['hpnow'] <= 0)
	{
	knopka('loc.php', 'Восстановите здоровье', 1);
	fin();
	}

$kvest = unserialize($f['kvest']);
if($f['loc'] == 15) $res = 644;
if($f['loc'] == 18) $res = 705;
if($f['loc'] == 31) $res = 706;
if($f['loc'] == 51) $res = 642;
if($f['loc'] == 68) $res = 641;
if($f['loc'] == 78) $res = 643;
if($f['loc'] == 83) $res = 640;
if (empty($kvest['loc'.$f['loc']]))
	{
	$kvest['loc'.$f['loc']]['date'] = 0;
	$f['kvest'] = serialize($kvest);
	}
$time = $kvest['loc'.$f['loc']]['date'] - $_SERVER['REQUEST_TIME'];
$item = $items->base_shmot($res);
if ($time > 0) msg2($item['name'].' поспеет через '.ceil($time / 60).' минут.', 1);
$kvest['loc'.$f['loc']]['date'] = $_SERVER['REQUEST_TIME'] + (60 * 60 * 6);
$items->add_item($f['login'], $res);
msg2('Вы собрали '.$item['name']);
$f['kvest'] = serialize($kvest);
$q = $db->query("update `users` set kvest='{$f['kvest']}' where id='{$f['id']}' limit 1;");
knopka('loc.php', 'В игру', 1);
fin();
