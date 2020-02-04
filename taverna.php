<?php
##############
# 24.12.2014 #
##############
require_once('inc/top.php');		// вывод на экран
require_once('inc/check.php');	// вход в игру
require_once('inc/head.php');
require_once('class/items.php');	// вещи

// блок условий (чтобы ХП в плюсе, лока 38 и не в бою)
if($f['status'] == 1)
	{
	knopka('battle.php', 'Вы в бою!',1);
	fin();
	}
if($f['hpnow'] <= 0)
	{
	knopka('loc.php', 'Восстановите здоровье', 1);
	fin();
	}
if($f['loc'] != 38)
	{
	knopka('loc.php', 'Ошибка локации',1);
	fin();
	}

// определяем переменные без всяких обходов register_globalls
$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$iid = isset($_REQUEST['iid']) ? $_REQUEST['iid'] : '';

// шапка
if(!empty($_SESSION['auth'])) require_once('inc/hpstring.php');
if(empty($mod))
	{
	echo '<div class="board">';
	echo 'Монеты: '.$f['money'].'<hr/>';
	echo 'Вы зашли в здание таверны. Здесь можно приобрести различные элексиры, влияющие на ваше состояние.<hr/></div>';
	knopka('taverna.php?mod=kup&iid=1', 'Эликсир ловкости (100 монет, уворот + 100)', 1);
	knopka('taverna.php?mod=kup&iid=2', 'Эликсир реакции (100 монет, крит + 100)', 1);
	knopka('taverna.php?mod=kup&iid=3', 'Эликсир жизненной силы (100 монет, Макс. ХП + 100)', 1);
	knopka('taverna.php?mod=kup&iid=4', 'Эликсир магической силы (100 монет, Макс. МП + 100)', 1);
	knopka('taverna.php?mod=kup&iid=5', 'Эликсир вышибалы (100 монет, урон + 50)', 1);
	knopka('taverna.php?mod=kup&iid=6', 'Купить Эссенцию исцеления (10 монет, HP +50, 1 лвл)', 1);
	knopka('taverna.php?mod=kup&iid=7', 'Купить Вытяжку исцеления (30 монет, HP +100, 5 лвл)', 1);
	knopka('taverna.php?mod=kup&iid=8', 'Купить Целебный элексир (100 монет, HP +150, 10 лвл)', 1);
	knopka('taverna.php?mod=kup&iid=9', 'Купить Напиток лечения (200 монет, HP +250, 15 лвл)', 1);
	knopka('taverna.php?mod=kup&iid=10', 'Купить Эссенцию мудрости (10 монет, MP +50, 1 лвл)', 1);
	knopka('taverna.php?mod=kup&iid=11', 'Купить Вытяжку мудрости (30 монет, MP +100, 5 лвл)', 1);
	knopka('taverna.php?mod=kup&iid=12', 'Купить Элексир мудрости (100 монет, MP +150, 10 лвл)', 1);
	knopka('taverna.php?mod=kup&iid=13', 'Купить Напиток мудрости (200 монет, MP +250, 15 лвл)', 1);
	knopka('taverna.php?mod=kup&iid=14', 'Купить Свиток нападения (10 монет, 6 лвл)', 1);
	knopka('taverna.php?mod=kup&iid=15', 'Купить Свиток развоплощения (200 монет, 6 лвл)', 1);
	fin();
	}
elseif($mod == 'kup')
	{
	$iid = intval($iid);
	if($iid < 1) $iid = 1;
	switch($iid):
	case 1:
		$cena = 100;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 620, 1);
		msg('Вы купили эликсир ловкости.',1);
	break;

	case 2:
		$cena = 100;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 621, 1);
		msg('Вы купили эликсир реакции.',1);
	break;

	case 3:
		$cena = 100;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 622, 1);
		msg('Вы купили эликсир жизненной силы.',1);
	break;

	case 4:
		$cena = 100;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 623, 1);
		msg('Вы купили эликсир магической силы.',1);
	break;

	case 5:
		$cena = 100;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 624, 1);
		msg('Вы купили эликсир вышибалы.',1);
	break;

	case 6:
		$cena = 10;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 153, 1);
		msg('Вы купили Эссенцию исцеления.',1);
	break;

	case 7:
		$cena = 30;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 154, 1);
		msg('Вы купили Вытяжку исцеления.',1);
	break;

	case 8:
		$cena = 100;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 155, 1);
		msg('Вы купили Целебный элексир.',1);
	break;

	case 9:
		$cena = 200;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 156, 1);
		msg('Вы купили Напиток лечения.',1);
	break;

	case 10:
		$cena = 10;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 191, 1);
		msg('Вы купили Эссенцию мудрости.',1);
	break;

	case 11:
		$cena = 30;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 192, 1);
		msg('Вы купили Вытяжку мудрости.',1);
	break;

	case 12:
		$cena = 100;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 193, 1);
		msg('Вы купили Элексир мудрости.',1);
	break;

	case 13:
		$cena = 200;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 194, 1);
		msg('Вы купили Напиток мудрости.',1);
	break;

	case 14:
		$cena = 10;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 121, 1);
		msg('Вы купили Свиток нападения.',1);
	break;

	case 15:
		$cena = 200;
		if($f['money'] < $cena) msg('Вам нечем заплатить.',1);
		$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
		$items->add_item($f['login'], 122, 1);
		msg('Вы купили Свиток развоплощения.',1);
	break;

	default:
		header("location: taverna.php");
		exit;
	break;
	endswitch;
	}
?>
