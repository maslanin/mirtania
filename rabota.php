<?php
##############
# 24.12.2014 #
##############
require_once('inc/top.php');	// вывод на экран
require_once('inc/check.php');	// вход в игру
require_once('inc/head.php');
if($f['status'] == 1)
	{
	knopka('battle.php', 'Вы в бою!', 1);
	fin();
	}

// для обхода register globalls
$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : '';
$keystring = isset($_REQUEST['keystring']) ? $_REQUEST['keystring'] : '';

// обновим в игре
$q = $db->query("update `users` set lastdate='{$t}' where id='{$f['id']}' limit 1;");

// шапка
require_once('inc/hpstring.php');
if($f['loc'] == 19)
	{
	echo '<div class="board">';
	$nagrada = $f['lvl'] * 6;
	if($nagrada < 6) $nagrada = 6;
	if($f['vip'] > $_SERVER['REQUEST_TIME'])
		{
		$nagrada = $f['lvl'] * 10;
		}
	if($f['rabota'] > 0)
		{
		if($f['rabota'] > $_SERVER['REQUEST_TIME'])
			{
			$ost = $f['rabota'] - $_SERVER['REQUEST_TIME'];
			msg('Вам осталось отработать '.ceil($ost / 60).' мин.', 1);
			}
		else
			{
			$f['money'] += $nagrada;
			echo 'Получено монет: '.$nagrada.'<br/>';
			$nalog = ceil($nagrada * 0.1);
			$f['nalog'] += $nalog;
			$q = $db->query("update `users` set money='{$f['money']}',nalog={$f['nalog']}, rabota=0  where id='{$f['id']}' limit 1;");
			msg('Вы отработали свои 30 минут.');
			echo '</div>';
			knopka('rabota.php', 'Работать еще', 1);
			fin();
			}
		}
	else
		{
		if(empty($ok))
			{
			echo '<form action="rabota.php?ok=1" method="POST">';
			echo 'Вы видите, как несколько измученных рудокопов молотят кирками по рудной жиле. Ваша зарплата составит '.$nagrada.' монет. Вы решаете:<br/>';
			echo '<img src="bez.php?r='.mt_rand(11111,99999).'"><br/><input type="text" name="keystring"><br/>';
			echo '<input type="submit" value="Присоединиться" /></form>';
			fin();
			}
		if(!isset($_SESSION['bez']) or $_SESSION['bez'] != $keystring)
			{
			msg('Вы ввели неверный код с картинки!',1);
			}
		$rabota = $_SERVER['REQUEST_TIME'] + 1800;
		$q = $db->query("update `users` set rabota={$rabota} where id={$f['id']} limit 1;");
		echo '</div>';
		msg('Вы присоединяетесь к рудокопам, один из них отдает вам кирку и идет отдыхать. Работать вам придется 30 минут, в это время перемещаться по миру нельзя, а браузер можно закрыть.',1);
		}
	}
elseif($f['loc'] == 44)
	{
	echo '<div class="board">';
	if($f['lvl'] < 10) msg2('Вы недостаточно сильны, приходите когда будете хотябы 10 уровня', 1);
	$nagrada = $f['lvl'] * 10;
	if($nagrada < 10) $nagrada = 10;
	if($f['vip'] > $_SERVER['REQUEST_TIME'])
		{
		$nagrada = $f['lvl'] * 15;
		}
	if($f['rabota'] > 0)
		{
		if($f['rabota'] > $_SERVER['REQUEST_TIME'])
			{
			$ost = $f['rabota'] - $_SERVER['REQUEST_TIME'];
			msg('Вам осталось отработать '.ceil($ost / 60).' мин.',1);
			}
		else
			{
			$f['money'] += $nagrada;
			$nalog = ceil($nagrada * 0.1);
			$f['nalog'] += $nalog;
			$q = $db->query("update `users` set money='{$f['money']}',nalog={$f['nalog']}, rabota=0  where id='{$f['id']}' limit 1;");
			$q = $db->query("update `users` set money={$f['money']}, rabota=0 where id='{$f['id']}' limit 1;");
			msg('Получено монет: '.$nagrada);
			echo '</div>';
			knopka('rabota.php', 'Работать еще', 1);
			fin();
			}
		}
	else
		{
		if(empty($ok))
			{
			echo '<form action="rabota.php?ok=1" method="POST">';
			echo 'Вы хотите наняться охранять шахту. Зарплата на ваш уровень составит '.$nagrada.' монет:<br/>';
			echo '<img src="bez.php?r='.mt_rand(11111,99999).'"><br/><input type="text" name="keystring"><br/>';
			echo '<input type="submit" value="Присоединиться" /></form>';
			fin();
			}
		if(!isset($_SESSION['bez']) or $_SESSION['bez'] != $keystring)
			{
			msg('Вы ввели неверный код с картинки!',1);
			}
		$rabota = $_SERVER['REQUEST_TIME'] + 1800;
		$q = $db->query("update `users` set rabota='{$rabota}' where id='{$f['id']}' limit 1;");
		echo '</div>';
		msg('Вы присоединились к надзирателям, в вашу задачу входит обеспечивать порядок на шахте в течении 30 минут. Браузер можно закрыть, перемещаться по миру нельзя.',1);
		}
	}
elseif($f['loc'] == 8 and !empty($f['klan']))
	{
	echo '<div class="board">';
	$nagrada = rand(ceil($f['lvl']/2),$f['lvl']);
	if($f['rabota'] > 0)
		{
		if($f['rabota'] > $_SERVER['REQUEST_TIME'])
			{
			$ost = $f['rabota'] - $_SERVER['REQUEST_TIME'];
			msg('Вам осталось отработать '.ceil($ost / 60).' мин.', 1);
			}
		else
			{
			$money = $nagrada * mt_rand(10,15);
			echo 'Вы добыли '.$nagrada.' камней для своего клана<br/>Казна получает '.$money.' монет<br/>';
			$log = $f['login'].' ['.$f['lvl'].'] добывает для клана '.$nagrada.' камней и '.$money.' монет.';
			$q = $db->query("insert into `klan_log` values(0,'{$f['login']}','{$log}','{$f['klan']}','{$t}');");
			$q = $db->query("update `klans` set kamni=kamni+'{$nagrada}',kazna=kazna+'{$money}' where name='{$f['klan']}' limit 1;");
			$q = $db->query("update `users` set rabota=0 where id={$f['id']} limit 1;");
			echo '</div>';
			knopka('rabota.php', 'Добывать еще', 1);
			fin();
			}
		}
	else
		{
		if(empty($ok))
			{
			echo '<form action="rabota.php?ok=1" method="POST">';
			echo 'В этой заброшенной шахте до сих пор попадаются камни для строительства<br/>';
			echo '<img src="bez.php?r='.mt_rand(11111,99999).'"><br/><input type="text" name="keystring"><br/>';
			echo '<input type="submit" value="Добывать камни" /></form>';
			fin();
			}
		if(!isset($_SESSION['bez']) or $_SESSION['bez'] != $keystring)
			{
			msg('Вы ввели неверный код с картинки!',1);
			}
		$rabota = $_SERVER['REQUEST_TIME'] + 600;
		$q = $db->query("update `users` set rabota={$rabota} where id={$f['id']} limit 1;");
		echo '</div>';
		msg('Добывать камни вам придется 10 минут, в это время перемещаться по миру нельзя, а браузер можно закрыть.',1);
		}
	}
else
	{
	knopka('loc.php', 'Ошибка локации!', 1);
	fin();
	}
?>
