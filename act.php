<?php
##############
# 24.12.2014 #
##############
require_once('inc/top.php');		//вывод на экран
require_once('inc/check.php');		//вход в игру
require_once('inc/head.php');
require_once('class/items.php');
require_once('inc/boi.php');		//создание боя + завод игрока в бой
require_once('inc/bot.php');		//параметры бота + создание бота

if($f['status'] == 1)
	{
	knopka('battle.php', 'Вы в бою!', 1);
	fin();
	}
if($f['status'] == 2)
	{
	knopka('arena.php', 'У вас заявка на арене!', 1);
	fin();
	}
if($f['hpnow'] <= 0)
	{
	knopka('loc.php', 'Восстановите здоровье', 1);
	fin();
	}
if($f['rabota'] > $t)
	{
	knopka('loc.php', 'Вы работаете!', 1);
	fin();
	}

switch($f['loc']):
case 1:
	if($f['lvl'] > 3) msg2('Вы уже опытный воин, тренеры вам не нужны', 1);
	$boi_id = addBoi(0);
	if($f['lvl'] == 1) addBot('Младший тренер', $f['lvl']);
	elseif($f['lvl'] == 2) addBot('Тренер', $f['lvl']);
	elseif($f['lvl'] == 3) addBot('Старший тренер', $f['lvl']);
	toBoi($f,2);
break;

case 5:
	if($f['lvl'] < 4)
		{
		knopka('loc.php', 'Доступно с 4 уровня', 1);
		fin();
		}
	$boi_id = addBoi(0);						//создаем бой и получаем его ИД
	addBot('Падальщик',$f['lvl']+1);			//Создаем бота Падальщик, лвл +1 к нашему
	addBot('Молодой падальщик',$f['lvl']);		//Создаем бота Молодой падальщик, лвл = наш лвл
	toBoi($f,2);								//сами заходим в бой (наша рабочая переменная ($f),команда)
break;

case 8:
	if($f['lvl'] < 4)
		{
		knopka('loc.php', 'Доступно с 4 уровня', 1);
		fin();
		}
	$boi_id = addBoi(0);
	addBot('Кротокрыс',$f['lvl']+1);
	addBot('Молодой кротокрыс',$f['lvl']);
	toBoi($f,2);
break;

case 9:
	$boi_id = addBoi(0);
	addBot('Мясной жук',$f['lvl']);
	toBoi($f,2);
break;

case 11:
	if($f['lvl'] < 4)
		{
		knopka('loc.php', 'Доступно с 4 уровня', 1);
		fin();
		}
	$boi_id = addBoi(0);
	addBot('Стервятник',$f['lvl']+1);
	addBot('Молодой стервятник',$f['lvl']);
	toBoi($f,2);
break;

case 13:
	if($f['lvl'] < 4)
		{
		knopka('loc.php', 'Доступно с 4 уровня', 1);
		fin();
		}
	$boi_id = addBoi(1);
	addBot('Кровосос',$f['lvl']+1);
	addBot('Шершень',$f['lvl']);
	toBoi($f,2);
break;

case 14:
	if($f['lvl'] < 4)
		{
		knopka('loc.php', 'Доступно с 4 уровня', 1);
		fin();
		}
	$boi_id = addBoi(0);
	addBot('Волк',$f['lvl']+1);
	addBot('Волчица',$f['lvl']);
	toBoi($f,2);
break;

case 20:
	if($f['lvl'] < 3)
		{
		knopka('loc.php', 'Доступно с 3 уровня', 1);
		fin();
		}
	$boi_id = addBoi(1);
	addBot('Остер',$f['lvl']+1);
	toBoi($f,2);
break;

case 21:
	if($f['lvl'] < 4)
		{
		knopka('loc.php', 'Доступно с 4 уровня', 1);
		fin();
		}
	$boi_id = addBoi(1);
	addBot('Черный гоблин',$f['lvl']+1);
	addBot('Гоблин',$f['lvl']);
	toBoi($f,2);
break;

case 22:
	if($f['lvl'] < 10)
		{
		knopka('loc.php', 'Доступно с 10 уровня', 1);
		fin();
		}
	if(date('H') != 19  or date('i') > 10) msg2('Зайти в бой могут только 25 человек, и только с 19:00 и до 19:10',1);
	$num = 0;	// количество чел в бою с ботом
	$bot_name = 'Тролль';
	$q = $db->query("select boi_id from `combat` where login='{$bot_name}' limit 1;");
	$bz = $q->fetch_assoc();
	$boi_id = $bz['boi_id'];
	if(isset($bz['boi_id']) and $bz['boi_id'] == 0) $q = $db->query("delete from `combat` where login='{$bot_name}' limit 1;");
	if(empty($boi_id))
		{
		$q = $db->query("update `users` set doping=0,doping_time=0 where id={$f['id']} limit 1;");
		$f = calcparam($f);
		$boi_id = addBoi(1);
		addBot($bot_name,1);
		toBoi($f,2);
		}
	else
		{
		$q = $db->query("select count(*) from `users` where boi_id='{$boi_id}';");
		$a = $q->fetch_assoc();
		$num = $a['count(*)'];
		if($num < 25)
			{
			$q = $db->query("update `users` set doping=0,doping_time=0 where id={$f['id']} limit 1;");
			$f = calcparam($f);
			toBoi($f,2);
			}
		else
			{
			msg2('Тролля окружили уже '.$num.' бойцов, вы не можете к нему протиснуться!',1);
			}
		}
break;

case 27:
	if($f['lvl'] < 4)
		{
		knopka('loc.php', 'Доступно с 4 уровня', 1);
		fin();
		}
	$boi_id = addBoi(0);
	addBot('Глорх',$f['lvl']+1);
	addBot('Кусач',$f['lvl']);
	toBoi($f,2);
break;

case 39:
	if($f['lvl'] < 3)
		{
		knopka('loc.php', 'Доступно с 3 уровня', 1);
		fin();
		}
	$boi_id = addBoi(1);
	addBot('Шмыг',$f['lvl']);
	toBoi($f,2);
break;

case 41:
	if($f['lvl'] < 4)
		{
		knopka('loc.php', 'Доступно с 4 уровня', 1);
		fin();
		}
	$boi_id = addBoi(1);
	addBot('Оборотень',$f['lvl']+1);
	addBot('Упырь',$f['lvl']);
	toBoi($f,2);
break;

case 43:
	$kvest = unserialize($f['kvest']);
	$kv = $kvest['loc56ks'];

	if($kv['nagrada'] == 1)
		{
		knopka('loc.php', 'Ошибка локации', 1);
		fin();
		}
	if($kv['lg'] == 1) msg2('Сердце ледяного голема уже у вас!',1);
	//ледяной голем
	$boi_id = addBoi(1);
	addBot('Ледяной голем',$f['lvl']+5);
	toBoi($f,2);
break;

case 49:
	if($f['lvl'] < 4)
		{
		knopka('loc.php', 'Доступно с 4 уровня', 1);
		fin();
		}
	$boi_id = addBoi(0);
	addBot('Орочий маг',$f['lvl']+1);
	addBot('Орочий шаман',$f['lvl']);
	toBoi($f,2);
break;

case 55:
	$kvest = unserialize($f['kvest']);
	$kv = $kvest['loc56ks'];

	if($kv['nagrada'] == 1)
		{
		knopka('loc.php', 'Ошибка локации', 1);
		fin();
		}
	if($kv['og'] == 1) msg2('Сердце огненного голема уже у вас!',1);
	//огненный голем
	$boi_id = addBoi(1);
	addBot('Огненный голем',$f['lvl']+5);
	toBoi($f,2);
break;

case 62:
	$kvest = unserialize($f['kvest']);
	$kv = $kvest['loc56ks'];

	if($kv['nagrada'] == 1)
		{
		knopka('loc.php', 'Ошибка локации', 1);
		fin();
		}
	if($kv['kg'] == 1) msg2('Сердце каменного голема уже у вас!',1);
	//каменный голем
	$boi_id = addBoi(1);
	addBot('Каменный голем',$f['lvl']+5);
	toBoi($f,2);
break;

case 67:
	$boi_id = addBoi(0);
	addBot('Гарпия',$f['lvl']);
	toBoi($f,2);
break;

case 73:
	if($f['lvl'] < 4)
		{
		knopka('loc.php', 'Доступно с 4 уровня', 1);
		fin();
		}
	$boi_id = addBoi(0);
	addBot('Орочий воин',$f['lvl']+1);
	addBot('Орк',$f['lvl']);
	toBoi($f,2);
break;

case 89:
	if($f['lvl'] < 3)
		{
		knopka('loc.php', 'Доступно с 3 уровня', 1);
		fin();
		}
	$boi_id = addBoi(1);
	addBot('Болотожор',$f['lvl']);
	toBoi($f,2);
break;

case 92:
	if($f['lvl'] < 6)
		{
		knopka('loc.php', 'Доступно с 6 уровня', 1);
		fin();
		}
	$boi_id = addBoi(2);
	addBot('Огненная ящерица',$f['lvl']+2);
	addBot('Огненный варан',$f['lvl']+1);
	toBoi($f,2);
break;

case 95:
	if($f['lvl'] < 3)
		{
		knopka('loc.php', 'Доступно с 3 уровня', 1);
		fin();
		}
	$boi_id = addBoi(1);
	if(mt_rand(1, 100) <= 50) addBot('Скелет',$f['lvl']+1);
	else addBot('Зомби',$f['lvl']+1);
	toBoi($f,2);
break;

case 96:
	if($f['lvl'] < 10)
		{
		knopka('loc.php', 'Доступно с 10 уровня', 1);
		fin();
		}
	if(date('H') != 22  or date('i') > 10) msg2('Зайти в бой могут только 20 человек, и только с 22:00 и до 22:10',1);
	$num = 0;	// количество чел в бою с ботом
	$bot_name = 'Дракон';
	$q = $db->query("select boi_id from `combat` where login='{$bot_name}' limit 1;");
	$bz = $q->fetch_assoc();
	$boi_id = $bz['boi_id'];
	if(isset($bz['boi_id']) and $bz['boi_id'] == 0) $q = $db->query("delete from `combat` where login='{$bot_name}' limit 1;");
	if(empty($boi_id))
		{
		$q = $db->query("update `users` set doping=0,doping_time=0 where id={$f['id']} limit 1;");
		$f = calcparam($f);
		$boi_id = addBoi(1);
		addBot($bot_name,1);
		toBoi($f,2);
		}
	else
		{
		$q = $db->query("select count(*) from `users` where boi_id='{$boi_id}';");
		$a = $q->fetch_assoc();
		$num = $a['count(*)'];
		if($num < 20)
			{
			$q = $db->query("update `users` set doping=0,doping_time=0 where id={$f['id']} limit 1;");
			$f = calcparam($f);
			toBoi($f,2);
			}
		else
			{
			msg2('Дракона окружили уже '.$num.' бойцов, вы не можете к нему протиснуться!',1);
			}
		}
break;

case 100:
	$boi_id = addBoi(0);
	addBot('Ползун',$f['lvl']);
	toBoi($f,2);
break;

case 103:
	if($f['lvl'] < 6)
		{
		knopka('loc.php', 'Доступно с 6 уровня', 1);
		fin();
		}
	$boi_id = addBoi(0);
	addBot('Шелкопряд',$f['lvl']);
	toBoi($f,2);
break;

default:
	knopka('loc.php', 'Ошибка локации', 1);
	fin();
endswitch;

header('location: battle.php');
//knopka('battle.php', 'В бой', 1);
fin();
?>
