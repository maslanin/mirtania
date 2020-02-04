<?php
##############
# 24.12.2014 #
##############

require_once('inc/top.php');	// вывод на экран
require_once('inc/check.php');	// вход в игру
require_once('inc/head.php');
require_once('class/items.php');	// шмотки
require_once('inc/boi.php');

// шапка
require_once('inc/hpstring.php');
echo '<div class="menu">
<a href="inv.php">Начало</a> - 
<a href="inv.php?mod=log">Лог</a> - 
<a href="inv.php?mod=equip">Экипировка</a> - 
<a href="inv.php?mod=money">Передать монеты</a> - 
<a href="inv.php?mod=arenda">Аренда</a> - 
Монеты: '.$f['money'].'</div>';
// определение переменных для обхода register globals
$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : '';
$go = isset($_REQUEST['go']) ? $_REQUEST['go'] : '';
$slot = isset($_REQUEST['slot']) ? $_REQUEST['slot'] : '';
$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';
$lgn = isset($_REQUEST['lgn']) ? ekr($_REQUEST['lgn']) : '';
$komm = isset($_REQUEST['komm']) ? ekr($_REQUEST['komm']) : 'no coment';
$summa = isset($_REQUEST['summa']) ? intval($_REQUEST['summa']) : 0;
$iid = isset($_REQUEST['iid']) ? intval($_REQUEST['iid']) : '';
$komu = isset($_REQUEST['komu']) ? $_REQUEST['komu'] : '';
$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$look = isset($_REQUEST['look']) ? intval($_REQUEST['look']) : 0;

if($f['hpnow'] <= 0)
	{
	knopka('loc.php', 'Восстановите здоровье', 1);
	fin();
	}
if($f['status'] == 1) 
	{
	knopka('battle.php', 'Вы в бою', 1);
	fin();
	}
if($f['status'] == 2) 
	{
	knopka('arena.php', 'У вас заявка на арене', 1);
	fin();
	}
if($mod == 'log')
	{
	$numb = 25;			//записей на страницу
	$count = 0;
	$q = $db->query("select count(*) from `log_peredach` where login='{$f['login']}' or login_per='{$f['login']}';");
	$a = $q->fetch_assoc();
	$all_log = $a['count(*)'];
	if($start > intval($all_log / $numb)) $start = intval($all_log / $numb);
	if($start < 0) $start = 0;
	$limit = $start * $numb;
	$count = $limit;
	$q = $db->query("select * from log_peredach where login='{$f['login']}' OR login_per='{$f['login']}' order by id desc limit {$limit},{$numb};");
	while($log = $q->fetch_assoc())
		{
		$count++;
		echo '<div class="board2" style="text-align:left">';
		echo $count.'. '.date('d.m.Y H:i',$log['dateper']).' - '.$log['log'];
		echo '</div>';
		}
	echo '<div class="board">';
	if($start > 0) echo '<a href="inv.php?mod=log&start='.($start - 1).'" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"> <-Назад</a>';
	echo ' | ';
	if($limit + $numb < $all_log) echo '<a href="inv.php?mod=log&start='.($start + 1).'" class="navig" >Вперед-></a>'; else echo ' <a href="#" class="navig"> Вперед-></a>';
	echo '</div>';
	fin();
	}

if($mod == 'useitem')
	{
	if($iid <= 0) msg2('Вещь не найдена в вашем рюкзаке', 1);
	$q = $db->query("select id from `invent` where login='{$f['login']}' and id='{$iid}' and flag_rinok=0 and flag_sklad=0 and flag_arenda=0 and flag_equip=0 limit 1;");
	$itm = $q->fetch_assoc() or msg2('Вещь не найдена в вашем рюкзаке', 1);
	$item = $items->shmot($itm['id']);
	if($f['lvl'] < $item['lvl']) msg2('Ваш уровень не подходит',1);
	if($item['ido'] == 121 or $item['ido'] == 122 or $item['ido'] == 153 or $item['ido'] == 154 or $item['ido'] == 155 or $item['ido'] == 156 or $item['ido'] == 191 or $item['ido'] == 192 or $item['ido'] == 193 or $item['ido'] == 194 or $item['ido'] == 620 or $item['ido'] == 621 or $item['ido'] == 622 or $item['ido'] == 623 or $item['ido'] == 624 or $item['ido'] == 625 or $item['ido'] == 626 or $item['ido'] == 627 or $item['ido'] == 628 or $item['ido'] == 629 or $item['ido'] == 630 or $item['ido'] == 631 or $item['ido'] == 632 or $item['ido'] == 633 or $item['ido'] == 634 or $item['ido'] == 635 or $item['ido'] == 636 or $item['ido'] == 637 or $item['ido'] == 638 or $item['ido'] == 639) require_once('inc/svitki.php');
	elseif($item['ido'] == 123 or $item['ido'] == 124 or $item['ido'] == 125 or $item['ido'] == 126 or $item['ido'] == 127) require_once('inc/donate.php');
	else msg2('Эту вещь нельзя использовать.',1);
    fin();
	}

if($mod == 'money')
	{
	if(empty($lgn) or empty($komm) or empty($summa))
		{
		echo '<div class="board" style="text-align:left">';
		echo '<form action="inv.php?mod=money" method="POST">';
		echo 'Передача монет. Все поля обязательны для заполнения.<br/><br/>
		Логин:<br/><input type="text" name="lgn" value="'.$lgn.'" maxlenght=25/><br/>';
		if($f['vip'] < $_SERVER['REQUEST_TIME']) echo '<span style="color:'.$female.'">Комиссия 1%</span><br/>';
		echo 'Сумма:<br/><input type="text" name="summa" maxlenght=12/><br/>';
		echo 'Комментарий:<br/><input type="text" name="komm" maxlenght=100 style="width:80%"/><br/>';
		echo '<input type="submit" value="Далее" /></form></div>';
		fin();
		}
	if($summa <= 0) msg2('Сумма не может быть меньше 1',1);
	if($f['money'] < $summa) msg2('Вы пытаетесь передать больше монет, чем у вас есть!',1);
	$komm = mb_substr($komm, 0, 100, 'UTF-8');
    $bz = get_login($lgn);
	if($f['login'] == $bz['login']) msg2('Нельзя передавать самому себе!',1);
    $log = $f['login'].' ['.$f['lvl'].'] ('.$f['klan'].') передает '.$bz['login'].' ['.$bz['lvl'].'] ('.$bz['klan'].') '.$summa.' монет - '.$komm;
	$q = $db->query("update users set money=money-{$summa} where id='{$f['id']}' limit 1;");
	$q = $db->query("update users set money=money+{$summa} where id='{$bz['id']}' limit 1;");
	$q = $db->query("insert into log_peredach values(0,'{$f['login']}','{$log}','{$bz['login']}','{$t}');");
    if($f['vip'] < $_SERVER['REQUEST_TIME'])
		{
		$proc = ceil($summa * 0.01);
		if($proc < 1) $proc = 1;
		$q = $db->query("update users set money=money-{$proc} where id='{$f['id']}' limit 1;");
		}
	msg2($summa.' монет персонажу '.$bz['login'].' успешно передано!');
	}

if($mod == 'item' and !empty($iid))
	{
	if($iid <= 0) msg('Вещь не найдена в вашем рюкзаке!', 1);
	$item = $items->shmot($iid);					// получим все данные о вещи
	$it = $items->count_item($f['login'], $iid, 1);	// подсчет передающихся вещей
	if(empty($item['flag_pered'])) msg('Данную вещь нельзя передать', 1);
	if(empty($summa) or empty($lgn))
		{
		echo '<div class="board" style="text-align:left">';
		echo '<form action="inv.php?mod=item&iid='.$iid.'" method="POST">';
		echo 'Вы хотите передать '.$item['name'].'<br/>';
		echo '<small>У вас - '.$it.' шт.</small><br/>';
		echo '<br/>Логин:<br/><input type="text" name="lgn" value="'.$lgn.'" maxlenght=30/><br/>';
		echo 'Количество:<br/><input type="number" name="summa" value="'.$it.'" maxlenght=12/><br/>';
		echo 'Комментарий:<br/><input type="text" name="komm" maxlenght="100" style="width:80%"/><br/>';
		echo '<input type="submit" value="Далее" /></form></div>';
		fin();
		}
	$komm = mb_substr($komm, 0, 100, 'UTF-8');
	if($summa < 1) $summa = 1;
	if($summa > $it) $summa = $it;
	$bz = get_login($lgn);
	if($f['login'] == $bz['login']) msg2('Нельзя передавать самому себе!',1);
	if($summa == 1)
		{
		$query = "update `invent` set login='{$bz['login']}',time='{$t}' where login='{$f['login']}' and flag_rinok=0 and flag_pered=1 and flag_arenda=0 and flag_equip=0 and flag_sklad=0 and id={$iid} limit 1;";
		$log = $f['login'].' ['.$f['lvl'].'] передал '.$bz['login'].' ['.$bz['lvl'].'] '.$item['name'].' ['.$item['lvl'].'] ('.$item['price'].') - '.$komm;
		}
	else
		{
		$query = "update `invent` set login='{$bz['login']}',time='{$t}' where login='{$f['login']}' and flag_rinok=0 and flag_pered=1 and flag_arenda=0 and flag_equip=0 and flag_sklad=0 and ido={$item['ido']} order by time asc limit {$summa};";
		$log = $f['login'].' ['.$f['lvl'].'] передал '.$bz['login'].' ['.$bz['lvl'].'] '.$item['name'].' ['.$item['lvl'].'] ('.$item['price'].') ('.$summa.' шт.) - '.$komm;
		}
	$q = $db->query($query);
	$q = $db->query("insert into `log_peredach` values(0,'{$f['login']}','{$log}','{$bz['login']}','{$t}');");
	msg2($item['name'].' ('.$summa.' шт.) персонажу '.$bz['login'].' успешно передано!');
	knopka('inv.php?lgn='.$lgn, 'Передать еще',1);
	}

if($mod == 'arenda')
	{
	if(empty($go))
		{
		knopka('inv.php?mod=arenda&go=2', 'Ваши вещи в аренде', 1);
		knopka('inv.php?mod=arenda&go=3', 'Вещи в аренде у вас', 1);
		fin();
		}
	elseif($go == 1)
		{
		if($iid < 1) msg2('Вещь не найдена!',1);
		$item = $items->shmot($iid);
		if(empty($item['equip']) or $item['equip'] == 'sumka') msg2('Аренда данной вещи запрещена!', 1);
		$q = $db->query("select count(*) from `arenda` where idv={$iid};");
		$a = $q->fetch_assoc();
		if($a['count(*)'] > 0) msg2('Вы уже предложили эту вещь в аренду!',1);
		if(!empty($lgn))
			{
			$lgn = get_login($lgn);
			$lgn = $lgn['login'];
			}
		if(empty($ok))
			{
			echo '<form action="inv.php?mod=arenda&ok=1&go=1&iid='.$iid.'" method="POST">';
			echo 'Кому даем '.$item['name'].'?<br/>';
			echo '<input type="text" name="lgn" value="'.$lgn.'"/><br/>';
			echo 'Цена аренды (за 1 сутки, 0-5000 монет):<br/>';
			echo '<input type="number" name="summa" value="0"/><br/>';
			echo 'На сколько дней? (1-90)<br/>';
			echo '<input type="number" name="look" value="1"/><br/>';
			echo '<input type="submit" value="Далее"/></form>';
			fin();
			}
		$summa = intval($summa);
		if($summa < 0 or $summa > 5000) msg2('Цена аренды должна быть от 0 до 5000 монет за одни сутки аренды.',1);
		$look = intval($look);
		if($look < 0 or $look > 90) msg2('Количество дней аренды должно быть от 1 до 90.',1);
		if($f['login'] == $lgn) msg2('Нельзя давать себе вещи в аренду!',1);
		$q = $db->query("insert into `arenda` values(0,'{$iid}','{$item['ido']}','{$summa}','{$look}','{$t}','{$f['login']}','{$lgn}');");
		msg2('Вы предложили '.$item['name'].' персонажу '.$lgn.' на '.$look.' дней за '.($summa * $look).' монет.');
		}
	elseif($go == 2)
		{
		// сначала список вещей, которые еще не взяли в аренду
		$q = $db->query("select * from `arenda` where login='{$f['login']}' order by time desc;");
		if($q->num_rows > 0)
			{
			echo '<div class="board">';
			msg('Вы предложили в аренду:');
			while($a = $q->fetch_assoc())
				{
				$item = $items->shmot($a['idv']);
				echo '<div class="board2" style="text-align:left"><b><a href="infa.php?mod=iteminfa&iid='.$item['id'].'">'.$item['name'].'</a></b> ('.$a['arenda_login'].' за '.$a['cena'].' монет) <a href="inv.php?mod=arenda&go=4&iid='.$item['id'].'">Отозвать</a></div>';
				}
			echo '</div>';
			}
		// теперь список вещей, который уже в аренде
		$q = $db->query("select id from `invent` where flag_arenda=1 and login='{$f['login']}' order by arenda_time asc;");
		if($q->num_rows > 0)
			{
			echo '<div class="board">';
			msg('Ваши вещи в аренде:');
			while($a = $q->fetch_assoc())
				{
				$item = $items->shmot($a['id']);
				echo '<div class="board2" style="text-align:left"><b><a href="infa.php?mod=iteminfa&iid='.$item['id'].'">'.$item['name'].'</a></b> ('.$item['arenda_login'].', до '.date('d.m.Y H:i',$item['arenda_time']).' за '.$item['arenda_price'].' монет)</div>';
				}
			echo '</div>';
			}
		fin();
		}
	elseif($go == 3)
		{
		// сначала список вещей, нам ДАЮТ в аренду
		$q = $db->query("select * from `arenda` where arenda_login='{$f['login']}' order by time desc;");
		if($q->num_rows > 0)
			{
			echo '<div class="board">';
			msg('Вам предлагают в аренду:');
			while($a = $q->fetch_assoc())
				{
				$item = $items->shmot($a['idv']);
				echo '<div class="board2" style="text-align:left"><b><a href="infa.php?mod=iteminfa&iid='.$item['id'].'">'.$item['name'].'</a></b> ('.$item['login'].' на '.$item['srok'].' суток за '.$a['cena'].' монет)';
				echo ' - <a href="inv.php?mod=arenda&go=5&iid='.$a['id'].'">Согласиться</a>';
				echo ' - <a href="inv.php?mod=arenda&go=6&iid='.$a['id'].'">Отказаться</a>';
				echo '</div>';
				}
			echo '</div>';
			}
		// теперь список вещей, который уже в аренде
		$q = $db->query("select id from `invent` where flag_arenda=1 and arenda_login='{$f['login']}' order by arenda_time asc;");
		if($q->num_rows > 0)
			{
			echo '<div class="board">';
			msg('Вещи у вас в аренде:');
			while($a = $q->fetch_assoc())
				{
				$item = $items->shmot($a['id']);
				echo '<div class="board2" style="text-align:left"><b><a href="infa.php?mod=iteminfa&iid='.$a['idv'].'">'.$item['name'].'</a></b> ('.$a['login'].', до '.date('d.m.Y H:i',$a['arenda_time']).' за '.$a['arenda_price'].' монет)</div>';
				}
			echo '</div>';
			}
		fin();
		}
	elseif($go == 4)
		{
		if($iid <= 0) msg2('Ошибка ID', 1); // iid - номер в таблице arenda
		$q = $db->query("delete from `arenda` where id='{$iid}' limit 1;");
		if($q->num_rows == 0) msg2('Ошибка ID', 1);
		msg2('Предложение аренды отозвано успешно.');
		}
	elseif($go == 5)
		{
		if($iid <= 0) msg2('Ошибка ID',1); // iid - номер в таблице arenda
		$q = $db->query("select * from `arenda` where arenda_login='{$f['login']}' and id='{$iid}' limit 1;");
		if($q->num_rows == 0) msg2('Ошибка ID',1);
		$a = $q->fetch_assoc();
		$q = $db->query("select id from `invent` where id='{$a['idv']}' and login='{$a['login']}' and flag_rinok=0 and flag_sklad=0 and flag_equip=0 and flag_arenda=0 limit 1;");
		$itm = $q->fetch_assoc() or msg2('Вещь не найдена!',1);
		$item = $items->shmot($itm['id']);
		$srok = $t + $a['srok'] * 86400;
		$q = $db->query("update `invent` set arenda_login='{$f['login']}',time='{$t}',flag_equip=0,flag_arenda=1,arenda_price={$a['cena']},arenda_time='{$srok}' where id='{$item['id']}' limit 1;");
		$q = $db->query("update `users` set money=money-{$a['cena']} where login='{$f['login']}' limit 1;");
		$q = $db->query("update `users` set money=money+{$a['cena']} where login='{$a['login']}' limit 1;");
		$q = $db->query("delete from `arenda` where idv='{$item['id']}';");
		$log = $a['login'].' дает в аренду '.$f['login'].' '.$item['name'].' ('.$item['lvl'].'lvl / '.$item['price'].' мон) на '.$a['srok'].' суток за '.$a['cena'].' монет.';
		$q = $db->query("insert into `log_peredach` values(0,'{$f['login']}','{$log}','{$a['login']}','{$t}');");
		msg2('Предложение аренды успешно принято.');
		}
	elseif($go == 6)
		{
		if($iid <= 0) msg2('Ошибка ID',1); // iid - номер в таблице arenda
		$q = $db->query("select * from `arenda` where arenda_login='{$f['login']}' and id='{$iid}' limit 1;");
		if($q->num_rows == 0) msg2('Ошибка ID',1);
		$a = $q->fetch_assoc();
		$q = $db->query("select id from invent where id='{$a['idv']}' and login='{$a['login']}' and flag_rinok=0 flag_sklad=0 and and flag_equip=0 and flag_arenda=0 limit 1;");
		$itm = $q->fetch_assoc() or msg2('Вещь не найдена!',1);
		$q = $db->query("delete from `arenda` where idv='{$itm['id']}' and id='{$iid}' limit 1;");
		msg2('Вы успешно отказались от аренды.');
		}
	}


if($mod == 'equip')
	{
	$q = $db->query("select `invent`.`id` from `invent`,`item` where ((`invent`.`login`='{$f['login']}' and `invent`.`flag_arenda`=0) or (`invent`.`arenda_login`='{$f['login']}' and `invent`.`flag_arenda`=1)) and `invent`.`flag_rinok`=0 and `invent`.`flag_equip`=1 and `invent`.`flag_sklad`=0 and (`item`.`equip`<>'') and `invent`.`ido`=`item`.`id`;");
	if($q->num_rows == 0) msg2('На вас ничего не надето!',1);
	while($eq = $q->fetch_assoc())
		{
		$item = $items->shmot($eq['id']);
		echo '<div class="board2" style="text-align:left;">';
		if($item['equip'] == 'prruka') echo '<b>Правая рука</b>: ';
		if($item['equip'] == 'lruka') echo '<b>Левая рука</b>: ';
		if($item['equip'] == 'dospeh') echo '<b>Доспех</b>: ';
		if($item['equip'] == 'golova') echo '<b>Голова</b>: ';
		if($item['equip'] == 'kolco') echo '<b>Кольцо</b>: ';
		if($item['equip'] == 'amulet') echo '<b>Амулет</b>: ';
		if($item['equip'] == 'nogi') echo '<b>Ноги</b>: ';
		if($item['equip'] == 'plaw') echo '<b>Плащ</b>: ';
		if($item['equip'] == 'braslet') echo '<b>Браслет</b>: ';
		if($item['equip'] == 'pojas') echo '<b>Пояс</b>: ';
		if($item['equip'] == 'sumka') echo '<b>В сумке</b>: ';
		echo '<b><a href="infa.php?mod=iteminfa&iid='.$item['id'].'">'.$item['name'].'</a></b>';
		echo ' <a href="inv.php?mod=drop_equip&slot='.$item['equip'].'"><span style="color:'.$male.'">[снять]</span></a>';
		echo '</div>';
		}
	knopka('inv.php?mod=drop_equip_all', 'Снять все вещи',1);
	knopka('inv.php', 'Вернуться',1);
	fin();
	}

if($mod == 'equip_item')
	{
	if($iid < 1) msg2('Такой вещи нет!', 1);
	$q = $db->query("select id from `invent` where ((login='{$f['login']}' and flag_arenda=0) or (arenda_login='{$f['login']}' and flag_arenda=1)) and flag_rinok=0 and flag_sklad=0 and flag_equip=0 and id={$iid} limit 1;");
	$itm = $q->fetch_assoc() or msg2('Такой вещи нет!',1);
	$item = $items->shmot($itm['id']);
	if(empty($item['equip'])) msg2('Эту вещь нельзя экипировать!',1);
	if($f['zdor'] < $item['zdor'] or $f['sila'] < $item['sila'] or $f['inta'] < $item['inta'] or $f['lovka'] < $item['lovka'] or $f['intel'] < $item['intel'] or $f['lvl'] < $item['lvl'])
		{
		msg('Ваши параметры не подходят под требования вещи!');
		echo '<div class="board2" style="text-align:left">';
		echo 'Требования вещи/Ваши параметры: Уровень '.$item['lvl'].'/'.$f['lvl'].', Сила '.$item['sila'].'/'.$f['sila'].', Интуиция '.$item['inta'].'/'.$f['inta'].', Ловкость '.$item['lovka'].'/'.$f['lovka'].', Интеллект '.$item['intel'].'/'.$f['intel'].', Здоровье '.$item['zdor'].'/'.$f['zdor'].'</div>';
		fin();
		}
	$items->equip_item($f['login'], $item['id'], $item['equip']);
	$f = calcparam($f);
	msg2('Вы экипировали '.$item['name']);
	}

if($mod == 'drop_equip' and !empty($slot))
	{
	$slot = ekr($slot);
	$q = $db->query("select `invent`.`id` from `invent`,`item` where `invent`.`login`='{$f['login']}' and `invent`.`flag_equip`=1 and `item`.`equip`='{$slot}' and `invent`.`ido`=`item`.`id` limit 1;");
	if($q->num_rows == 0) msg2('Здесь у вас ничего не надето',1);
	$itm = $q->fetch_assoc();
	$item = $items->shmot($itm['id']);
	$items->drop_equip($f['login'], $slot);
	$f = calcparam($f);
	msg2('Вы сняли '.$item['name']);
	}

if($mod == 'drop_equip_all')
	{
	$items->drop_equip_all($f['login']);
	$f = calcparam($f);
	msg2('Вы сняли всю экипировку.');
	}

if($mod == 'menu')
	{
	if($iid<=0) msg2('Такой вещи нет.',1);
	$item = $items->shmot($iid);
	msg2('<b>'.$item['name'].' ['.$item['lvl'].']</b>');
	knopka('infa.php?mod=iteminfa&iid='.$iid, 'Описание',1);
	if(!empty($item['equip'])) knopka('inv.php?mod=equip_item&iid='.$item['id'], '<span style="color:'.$male.'">Экипировать</span>',1);
	knopka('inv.php?mod=useitem&iid='.$item['id'],'<span style="color:'.$notice.'">Использовать</span>',1);
	if(!empty($item['flag_pered']) && $item['login'] == $f['login']) knopka('inv.php?mod=item&iid='.$item['id'].'&start='.$start.'&lgn='.$lgn, '<span style="color:'.$male.'">Передать</span>',1);
	if($item['login'] == $f['login'] && !empty($item['equip']) && $item['equip'] != 'sumka') knopka('inv.php?mod=arenda&iid='.$item['id'].'&lgn='.$lgn.'&go=1', '<span style="color:'.$manacolor.'">Дать в аренду</span>',1);
	knopka('inv.php', 'В рюкзак',1);
	fin();
	}

$numb = 15;	// вещей на странице
$count = 0;
if(!empty($lgn))
	{
	// это если вдруг мы хотим вещь передать
	$lgn = get_login($lgn);
	$lgn = $lgn['login'];
	msg2('Вы готовы передать вещи '.$lgn.'.');
	}
// выберем все вещи, которые не на рынке, не одеты, не на складе, или у нас в аренде
if(empty($look))
	{
	$q = $db->query("select count(*) from `invent` where ((login='{$f['login']}' and flag_arenda=0) or (arenda_login='{$f['login']}' and flag_arenda=1)) and flag_rinok=0 and flag_sklad=0 and flag_equip=0;");
	$a = $q->fetch_assoc();
	$all_itm = $a['count(*)']; // всего вещей
	if($start > intval($all_itm / $numb)) $start = intval($all_itm / $numb);
	if($start < 0) $start = 0;
	$limit = $start * $numb;
	$count = $limit;
	$q = $db->query("select id,count(*) from `invent` where ((login='{$f['login']}' and flag_arenda=0) or (arenda_login='{$f['login']}' and flag_arenda=1)) and flag_rinok=0 and flag_sklad=0 and flag_equip=0 group by ido order by time desc limit {$limit},{$numb};");
	while($invent = $q->fetch_assoc())
		{
		echo '<div class="board2" style="text-align:left">';
		$count++;
		$item = $items->shmot($invent['id']);
		echo $count.') <a href="inv.php?mod=menu&iid='.$item['id'].'&lgn='.$lgn.'">'.$item['name'].'</a>';
		if(!empty($item['equip'])) echo ' <a href="inv.php?mod=equip_item&iid='.$item['id'].'"><span style="color:'.$male.'">Экипировать</span></a>';
		echo '<br/>уров: '.$item['lvl'].', цена: '.$item['price'];
		if($invent['count(*)'] > 1) echo '<br/>Количество: <a href="inv.php?look='.$item['ido'].'&lgn='.$lgn.'"><b>'.$invent['count(*)'].'</b></a>';
		echo '</div>';
		}
	}
else
	{
	if($look <= 0) msg2('Ошибка!',1);
	$q = $db->query("select count(*) from `invent` where ((login='{$f['login']}' and flag_arenda=0) or (arenda_login='{$f['login']}' and flag_arenda=1)) and flag_rinok=0 and flag_sklad=0 and flag_equip=0 and ido='{$look}';");
	$a = $q->fetch_assoc();
	$all_itm = $a['count(*)']; // всего вещей
	if($start > intval($all_itm / $numb)) $start = intval($all_itm / $numb);
	if($start < 0) $start = 0;
	$limit = $start * $numb;
	$q = $db->query("select id from `invent` where ((login='{$f['login']}' and flag_arenda=0) or (arenda_login='{$f['login']}' and flag_arenda=1)) and flag_rinok=0 and flag_sklad=0 and flag_equip=0 and ido='{$look}' order by time desc limit {$start},{$numb};");
	while($invent = $q->fetch_assoc())
		{
		echo '<div class="board2" style="text-align:left">';
		$count++;
		$item = $items->shmot($invent['id']);
		echo $count.') <a href="inv.php?mod=menu&iid='.$item['id'].'&lgn='.$lgn.'">'.$item['name'].'</a>';
		if(!empty($item['equip'])) echo ' <a href="inv.php?mod=equip_item&iid='.$item['id'].'"><span style="color:'.$male.'">Экипировать</span></a>';
		echo '<br/>уров: '.$item['lvl'].', цена: '.$item['price'];
		echo '</div>';
		}
	}
if($all_itm > $numb)
	{
	echo '<div class="board">';
	if($start > 0) echo '<a href="inv.php?start='.($start - 1).'&lgn='.$lgn.'&look='.$look.'" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
	echo ' | ';
	if($limit + $numb < $all_itm) echo '<a href="inv.php?start='.($start + 1).'&lgn='.$lgn.'&look='.$look.'" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
	echo '</div>';
	}
fin();
?>
