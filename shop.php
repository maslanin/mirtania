<?php
##############
# 24.12.2014 #
##############
require_once('inc/top.php');		// вывод на экран
require_once('inc/check.php');	// вход в игру
require_once('inc/head.php');
require_once('class/items.php');	// работа с вещами

$mod = isset($_REQUEST['mod'])? $_REQUEST['mod'] : '';
// блок проверок
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
if(($f['loc'] != 1 and $f['loc'] != 37 and $f['loc'] != 91) and $mod != 'iteminfa')
	{
	knopka('loc.php', 'Ошибка локации',1);
	fin();
	}

// определение переменных вместо обхода register_globals
$iid = isset($_REQUEST['iid'])? $_REQUEST['iid'] : '';
$ok = isset($_REQUEST['ok'])? 1 : 0;
$summa = isset($_REQUEST['summa'])? $_REQUEST['summa'] : 0;
$start = isset($_REQUEST['start'])? $_REQUEST['start'] : 0;
$page = isset($_REQUEST['page'])? $_REQUEST['page'] : 0;
$lvl = isset($_REQUEST['lvl'])? $_REQUEST['lvl'] : 0;

// шапка
if(!empty($_SESSION['auth'])) require_once('inc/hpstring.php');
if(empty($mod))
	{
	msg2('Монеты: '.$f['money']);
	msg('Здравствуй, '.$f['login'].'! У меня ты можешь купить самые лучшие доспехи и оружие, а так же продать всякий ненужный хлам!');
	knopka('shop.php?mod=sell', 'Продать вещи', 1);
	knopka('shop.php?mod=bay', 'Купить вещи', 1);
	fin();
	}
elseif($mod=='sell')
	{
	if(empty($iid))
		{
		$numb = 15;	// сколько вещей на страницу
		$count = 0;	// обнуляем счетчик

		// нужно показать те вещи, которые не одеты, не в аренде, не на рынке, не на клан складе.
		$q = $db->query("select count(*) from `invent` where login='{$f['login']}' and flag_arenda=0 and flag_rinok=0 and flag_equip=0 and flag_sklad=0;");
		$all_itm = $q->fetch_assoc(); // всего вещей
		$all_itm = $all_itm['count(*)'];
		if($start > intval($all_itm / $numb)) $start = intval($all_itm / $numb);
		if($start < 0) $start = 0;
		$limit = $start * $numb;
		if($all_itm <= 0) msg2('У вас нет товара на продажу.',1);
		else msg2('Вы можете продать:');
		$q = $db->query("select id,count(*) from `invent` where login='{$f['login']}' and flag_arenda=0 and flag_rinok=0 and flag_equip=0 and flag_sklad=0 group by ido order by time,id desc limit {$limit},{$numb};");
		while($invent = $q->fetch_assoc())
			{
			$item = $items->shmot($invent['id']);
			echo '<div class="board2" style="text-align:left">';
			echo '<a href="infa.php?mod=iteminfa&iid='.$item['id'].'">'.$item['name'].'</a> ('.$item['lvl'].' уров.)';
			$cena = intval($item['price'] * 0.5);
			if($f['vip'] > $_SERVER['REQUEST_TIME']) $cena = intval($item['price'] * 0.6);
			echo ' '.$cena.' монет';
			echo ' <a href="shop.php?mod=sell&iid='.$item['id'].'">[продать]</a>';
			if($invent['count(*)'] > 1) echo '<br/>Количество: <b>'.$invent['count(*)'].'</b>';
			$count++;
			echo '</div>';
			}
		if($all_itm > $numb)
			{
			echo '<div class="board">';
			if($start > 0) echo '<a href="shop.php?mod=sell&start='.($start - 1).'" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
			echo ' | ';
			if($limit + $numb < $all_itm) echo '<a href="shop.php?mod=sell&start='.($start + 1).'" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
			echo '</div>';
			}
		fin();
		}
	// этот блок кода сработает если уже выбрана вещь на продажу
	$iid = intval($iid);
	if($iid < 1) msg2('Вещь не найдена в вашем рюкзаке!', 1);
	// мы возьмем только ИД вещи
	$res = $db->query("select id from `invent` where id={$iid} and login='{$f['login']}' and flag_rinok=0 and flag_arenda=0 and flag_equip=0 and flag_sklad=0 order by time,id desc limit 1;");
	$itm = $res->fetch_assoc() or msg2('Вещь не найдена в вашем рюкзаке!',1);
	// а вот тут мы посчитаем сколько всего у нас одинаковых вещей в рюкзаке
	$summ = $items->count_item($f['login'],$itm['id']);
	$item = $items->shmot($itm['id']);
	if(empty($summa))
		{
		echo '<div class="board" style="text-align:left">';
		echo '<form action="shop.php?mod=sell&iid='.$iid.'" method="POST">';
		echo 'Вы хотите продать торговцу '.$item['name'].'<br/>';
		echo '<small>У вас - '.$summ.' шт.</small><br/>';
		echo 'Количество:<br/><input type="number" name="summa" maxlenght="12" value="'.$summ.'"/><br/>';
		echo '<input type="submit" value="Далее" /></div>';
		fin();
		}
	$summa = intval($summa);
	if($summa <= 0) $summa = 1;
	if($summa > $summ) $summa = $summ;
	$money = intval($item['price'] * 0.5) * $summa;
	if($f['vip'] > $_SERVER['REQUEST_TIME']) $money = intval($item['price'] * 0.6) * $summa;
	$f['money'] += $money;
	if($summa == 1)	$q = $db->query("delete from `invent` where id={$iid} and login='{$f['login']}' and flag_rinok=0 and flag_arenda=0 and flag_equip=0 and flag_sklad=0 limit 1;");
	else $items->del_base_item($f['login'], $item['ido'], $summa);
	$q = $db->query("update `users` set money='{$f['money']}' where id='{$f['id']}' limit 1;");
	msg2('Вы продали в магазин '.$item['name'].' ('.$summa.' шт.) за '.$money.' монет.');
	knopka('shop.php?mod=sell', 'Далее',1);
	fin();
	}
elseif($mod == "bay")
	{
	$q = $db->query("select max(lvl) from `item`;");
	$a = $q->fetch_assoc();
	$max_lvl = $a['max(lvl)'];
	if(empty($lvl))
		{
		echo '<div class="board" style="text-align:left">';
		echo '<form action="shop.php?mod=bay" method="POST">
		На какой уровень желаете приобрести вещи?<br/>
		<select name="lvl">';
		for($i = 1; $i <= $max_lvl; $i++) echo '<option value='.$i.'>'.$i.'</option>';
		echo '</select><input type="submit" value="Далее"/></form></div>';
		knopka('shop.php', 'Вернуться', 1);
		fin();
		}
	$lvl = intval($lvl);
	if($lvl < 1) $lvl = 1;
	if($lvl > $max_lvl) $lvl = $max_lvl;
	require_once('inc/shop.php');
	if(empty($iid))
		{
		msg2('Я продаю вещи:');
		if($lvl >= 1 and $lvl <= 5)
			{
			$iid = $lvl * 5 + 190;		//правка
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 5 + 191;		//левка
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 5 + 192;		//доспех
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 5 + 193;		//кольцо
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 5 + 194;		//пояс
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			}
		else
			{
			$iid = $lvl * 20 + 100;		//правка
			$item = $items->base_shmot($iid);
			msg2('Комплект критовика');
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 101;		//левка
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 102;		//доспех
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 103;		//кольцо
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 104;		//пояс
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';

			$iid = $lvl * 20 + 105;		//правка
			$item = $items->base_shmot($iid);
			msg2('Комплект уворота');
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 106;		//левка
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 107;		//доспех
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 108;		//кольцо
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 109;		//пояс
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';

			$iid = $lvl * 20 + 110;		//правка
			$item = $items->base_shmot($iid);
			msg2('Комплект танка');
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 111;		//левка
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 112;		//доспех
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 113;		//кольцо
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 114;		//пояс
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';

			$iid = $lvl * 20 + 115;		//правка
			$item = $items->base_shmot($iid);
			msg2('Комплект универсала');
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 116;		//левка
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 117;		//доспех
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 118;		//кольцо
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			$iid = $lvl * 20 + 119;		//пояс
			$item = $items->base_shmot($iid);
			echo '<div class="board2" style="text-align:left"><a href="shop.php?mod=bay&iid='.$iid.'&lvl='.$lvl.'">'.$item['name'].'</a> <a href="shop.php?mod=iteminfa&iid='.$iid.'"><span style="color:'.$female.'">[infa]</span></a> ('.$item['price'].' монет)</div>';
			}
		knopka('shop.php', 'Вернуться', 1);
		fin();
		}
	$iid = intval($iid);
	if(!in_array($iid, $shop)) msg2('Такой вещи нет в продаже!',1);
	$item = $items->base_shmot($iid);
	if($item['price'] > $f['money']) msg2('У вас недостаточно монет!',1);
	if(empty($ok))
		{
		msg2('Вы уверены, что хотите купить '.$item['name'].'?');
		knopka('shop.php?mod=bay&iid='.$iid.'&ok=1&lvl='.$lvl, 'Купить', 1);
		knopka('inv.php', 'В игру', 1);
		fin();
		}
	$f['money'] -= $item['price'];
	$q = $db->query("update `users` set money='{$f['money']}' where id='{$f['id']}' limit 1;");
	$items->add_item($f['login'], $iid, 1);
	msg2('Вы купили '.$item['name'].'! Осталось '.$f['money'].' монет.');
	knopka('shop.php?mod=bay&lvl='.$lvl, 'Далее', 1);
	fin();
	}
elseif($mod == 'iteminfa')
	{
	$iid = intval($iid);
	if($iid <= 0) msg2('Вещь не найдена!',1);
	$item = $items->base_shmot($iid);
	knopka('javascript:history.go(-1)', 'Вернуться', 1);
	echo '<div class="board" style="text-align:left">';
	echo '<b>'.$item['name'].' ['.$item['lvl'].']</b>';
	echo '<br/>';
	if(!empty($item['art'])) echo 'Арт: '.$item['art'].'<br/>';
	echo 'Цена: '.$item['price'].'<br/>';
	echo '<hr/>';
	if(!empty($item['equip']))
		{
		echo '<b>Характеристики</b>:';
		echo '<hr/>';
		if($item['krit'] > 0) echo 'Крит: '.$item['krit'].'<br/>';
		if($item['uvorot'] > 0) echo 'Уворот: '.$item['uvorot'].'<br/>';
		if($item['uron'] > 0 and $item['intel'] < $item['sila']) echo 'Урон: '.$item['uron'].'<br/>';
		if($item['bron'] > 0) echo 'Броня: '.$item['bron'].'<br/>';
		if($item['hp'] > 0) echo 'Бонус ХП: '.$item['hp'].'<br/>';
		echo '<hr/>';
		echo '<b>Требования</b>:';
		echo '<hr/>';
		if($item['zdor'] > 0) echo 'Здоровье: '.$item['zdor'].'<br/>';
		if($item['sila'] > 0) echo 'Сила: '.$item['sila'].'<br/>';
		if($item['inta'] > 0) echo 'Интуиция: '.$item['inta'].'<br/>';
		if($item['lovka'] > 0) echo 'Ловкость: '.$item['lovka'].'<br/>';
		if($item['intel'] > 0) echo 'Интеллект: '.$item['intel'].'<br/>';
		echo '<hr/>';
		}
	echo '<b>Описание</b>: ';
	if(!empty($item['info'])) echo $item['info'];
	else echo 'Нет';
	echo '</div>';
	knopka('javascript:history.go(-1)', 'Вернуться',1);
	fin();
	}
?>
