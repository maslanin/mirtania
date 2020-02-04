<?php

require_once('inc/top.php');
require_once('inc/check.php');
require_once('inc/head.php');
require_once('inc/hpstring.php');

if($f['autoreg'] != 2)
	{
	header("location: loc.php");
	fin();
	}
$go = !empty($_REQUEST['go']) ? $_REQUEST['go'] : 0;
	
if(empty($f['kvest_step']))
	{
	if(empty($go))
		{
		msg('Добро пожаловать в волшебный мир удивительных приключений, Путник! Вы можете пройти обучение, или отказаться от него.');
		knopka('newbie.php?go=1', 'Пройти обучение', 1);
		knopka('newbie.php?go=2', 'Отказаться от обучения', 1);
		fin();
		}
	elseif($go == 1)
		{
		$q = $db->query("update `users` set kvest_step=1 where id='{$f['id']}' limit 1;");
		header("location: newbie.php");
		fin();
		}
	else
		{
		$q = $db->query("update `users` set autoreg=1 where id='{$f['id']}' limit 1;");
		msg('Вы отказались от обучения', 1);
		}
	}
?>
