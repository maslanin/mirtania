<?php
msg('Вы можете улучшить (ну или ухудшить, уж как получится) любой предмет экипировки. Цена 500 монет за каждый уровень вещи.');
$iid = isset($_REQUEST['iid']) ? intval($_REQUEST['iid']) : 0;
$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$limit = 0;
$numb = 15;
if($mod == 'tochkam')
	{
	if($f['ruda'] < 20) msg('У вас нехватает руды. У вас '.$f['ruda'].' из 20', 1);
	if(empty($go))
		{
		msg('Вы уверены что хотите потратить 20 руды на точильный камень?');
		knopka('kvest.php?mod=tochkam&go=1', 'Да, продолжить!');
		knopka('loc.php', 'В игру');
		fin();
		}
	$q = $db->query("update `users` set ruda=ruda-20 where id='{$f['id']}' limit 1;");
	$items->add_item($f['login'], 127, 1);
	msg('Точильный камень успешно куплен, списано 20 руды', 1);
	}
if(20 <= $f['ruda'])
	{
	knopka('kvest.php?mod=tochkam', 'Купить точильный камень (20 руды)');
	}
if(empty($iid))
	{
	// вещи не в аренде, не в складе, не одетые, не на рынке, с 6 лвл, неточеные
	$q = $db->query("select count(`invent`.`id`) from `invent`,`item` where `invent`.`login`='{$f['login']}' and `invent`.`flag_arenda`=0 and `invent`.`flag_rinok`=0 and `invent`.`flag_sklad`=0 and `invent`.`flag_equip`=0 and (`item`.`equip`<>'' and `item`.`equip`<>'sumka') and `invent`.`up`=0 and `item`.`lvl`>5 and `invent`.`ido`=`item`.`id`;");
	$a = $q->fetch_assoc();
	$all_itm = $a['count(`invent`.`id`)']; // всего вещей
	if(empty($all_itm)) msg('У вас нет подходящих вещей для модификации', 1);
	if($start > intval($all_itm / $numb)) $start = intval($all_itm / $numb);
	if($start < 0) $start = 0;
	$limit = $start * $numb;
	$count = $limit;
	$q = $db->query("select `invent`.`id` from `invent`,`item` where `invent`.`login`='{$f['login']}' and `invent`.`flag_arenda`=0 and `invent`.`flag_rinok`=0 and `invent`.`flag_sklad`=0 and `invent`.`flag_equip`=0 and (`item`.`equip`<>'' and `item`.`equip`<>'sumka') and `invent`.`up`=0 and `item`.`lvl`>5 and `invent`.`ido`=`item`.`id` limit {$limit},{$numb};");
	while($invent = $q->fetch_assoc())
		{
		echo '<div class="board2" style="text-align:left">';
		$count++;
		$item = $items->shmot($invent['id']);
		echo $count.') <a href="infa.php?mod=iteminfa&iid='.$item['id'].'">'.$item['name'].'</a>';
		echo ' <a href="kvest.php?iid='.$item['id'].'"><span style="color:'.$male.'">Улучшить</span></a>';
		echo '<br/>уров: '.$item['lvl'].', цена модификации: '.($item['lvl'] * 500);
		echo '</div>';
		}
	if($all_itm > $numb)
		{
		echo '<div class="board">';
		if($start > 0) echo '<a href="kvest.php?start='.($start - 1).'" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
		echo ' | ';
		if($limit + $numb < $all_itm) echo '<a href="kvest.php?start='.($start + 1).'" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
		echo '</div>';
		}
	fin();
	}
$iid = intval($iid);
if($iid <= 0) msg('Такая вещь не найдена в рюкзаке.', 1);
$item = $items->shmot($iid);
if(!empty($item['up'])) msg('Вещь уже была модифицирована ранее.', 1);
if($item['lvl'] < 6) msg('Модифицировать можно вещи с 6 уровня.', 1);
if(empty($item['equip']) or $item['equip'] == 'sumka') msg('Эта вещь не подлежит модификации', 1);
$cena = $item['lvl'] * 500;
if($cena > $f['money']) msg('У вас недостаточно монет для модификации данной вещи.', 1);
if(empty($ok))
	{
	msg('Вы хотите модифицировать '.$item['name'].' за '.$cena.' монет?');
	knopka('kvest.php?iid='.$iid.'&ok=1', 'Продолжаем');
	knopka('loc.php', 'Да вы что, мне очень страшно! * Убежать');
	fin();
	}
$upgr = mt_rand(1, 25);
if(mt_rand(1, 100) <= 50) $upgr *= -1;
if($upgr > 0) $up = '+'.$upgr.'%'; else $up = $upgr.'%';
$q = $db->query("update `invent` set up='{$upgr}',time='{$t}' where id='{$iid}' limit 1;");
$q = $db->query("update `users` set money=money-{$cena} where id='{$f['id']}' limit 1;");
$log = $f['login'].' ['.$f['lvl'].'] ('.$f['klan'].') модифицирует '.$item['name'].' (id: '.$item['id'].') на '.$up;
$q = $db->query("insert into log_peredach values(0,'{$f['login']}','{$log}','','{$t}');");
msg('Неуклюже постукав молотком, вы модифицируете '.$item['name'].' на '.$upgr.'%');
knopka('kvest.php', 'Модифицировать еще одну вещь');
knopka('loc.php', 'Уйти');
fin();
?>
