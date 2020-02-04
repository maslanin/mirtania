<?php
##############
# 24.12.2014 #
##############

switch($item['ido']):
case 123:
	if(empty($ok))
		{
		msg2('Вы хотите разыграть Лотерейный билет.');
		knopka('inv.php?mod=useitem&iid='.$iid.'&ok=1', 'Продолжить', 1);
		knopka('inv.php', 'Вернуться', 1);
		fin();
		}
	$min = 1;
	$max = 100;
	$rnd = mt_rand($min, $max);
	if(1 <= $rnd && $rnd <= 50)
		{
		$summ = 5000;
		$f['money'] += $summ;
		$q = $db->query("update `users` set money='{$f['money']}' where id='{$f['id']}' limit 1;");
		$items->del_item($f['login'], $item['id'], 1);
		msg2('Вы выиграли в лотерею '.$summ.' монет.',1);
		}
	elseif(51 <= $rnd && $rnd <= 60)
		{
		$summ = 25000;
		$f['money'] += $summ;
		$q = $db->query("update `users` set money={$f['money']} where id={$f['id']} limit 1;");
		$items->del_item($f['login'], $item['id'], 1);
		msg2('Вы выиграли в лотерею '.$summ.' монет.',1);
		}
	elseif(61 <= $rnd && $rnd <= 70)
		{
		$items->add_item($f['login'], $item['ido'], 1);
		msg2('Вы выиграли в лотерею еще один лотерейный билет. Старый билет остался при вас.',1);
		}
	elseif(71 <= $rnd && $rnd <= 75)
		{
		$items->del_item($f['login'], $item['id'], 1);
		$items->add_item($f['login'], 127, 1);
		msg2('Вы выиграли в лотерею Точильный камень.',1);
		}
	elseif(76 <= $rnd && $rnd <= 85)
		{
		$items->del_item($f['login'], $item['id'], 1);
		$items->add_item($f['login'], 124, 1);
		msg2('Вы выиграли в лотерею Свиток опыта 1 ступени.',1);
		}
	elseif(85 <= $rnd && $rnd <= 91)
		{
		$items->del_item($f['login'], $item['id'], 1);
		$items->add_item($f['login'], 125, 1);
		msg2('Вы выиграли в лотерею Свиток опыта 2 ступени.',1);
		}
	elseif(92 <= $rnd && $rnd <= 95)
		{
		$items->del_item($f['login'], $item['id'], 1);
		$items->add_item($f['login'], 126, 1);
		msg2('Вы выиграли в лотерею Свиток опыта 3 ступени.',1);
		}
	elseif(96 <= $rnd && $rnd <= 100)
		{
		$items->del_item($f['login'], $item['id'], 1);
		$int = $f['lvl'] + 127;
		$ido = $items->add_item($f['login'], $int, 1);
		if(date('d.m') == '30.12' or date('d.m') == '31.12') $q = $db->query("update invent set up=25 where id='{$ido}' limit 1;");
		$q = $db->query("update invent set name='Именной браслет {$f['login']}', info='Выдано персонажу {$f['login']} за выигрыш в лотерею' where id='{$ido}' limit 1;");
		msg2('Вы выиграли лотерейный браслет на '.$f['lvl'].' уровень.',1);
		}
	else
		{
		msg2('Произошла какая-то ошибка.',1);
		}
break;

case 124:
	$exxp = 7500 * $f['lvl'];
	if(empty($ok))
		{
		msg2('Вы хотите получить '.$exxp.' опыта.');
		knopka('inv.php?mod=useitem&iid='.$iid.'&ok=1', 'Продолжить', 1);
		knopka('inv.php', 'Вернуться', 1);
		fin();
		}
	addexp($f['id'],$exxp);
	$items->del_item($f['login'], $item['id'], 1);
	msg2('Вы получили '.$exxp.' опыта.',1);
break;

case 125:
	$exxp = 12500 * $f['lvl'];
	if(empty($ok))
		{
		msg2('Вы хотите получить '.$exxp.' опыта.');
		knopka('inv.php?mod=useitem&iid='.$iid.'&ok=1', 'Продолжить', 1);
		knopka('inv.php', 'Вернуться', 1);
		fin();
		}
	addexp($f['id'],$exxp);
	$items->del_item($f['login'], $item['id'], 1);
	msg2('Вы получили '.$exxp.' опыта.',1);
break;

case 126:
	$exxp = 20000 * $f['lvl'];
	if(empty($ok))
		{
		msg2('Вы хотите получить '.$exxp.' опыта.');
		knopka('inv.php?mod=useitem&iid='.$iid.'&ok=1', 'Продолжить', 1);
		knopka('inv.php', 'Вернуться', 1);
		fin();
		}
	addexp($f['id'],$exxp);
	$items->del_item($f['login'], $item['id'], 1);
	msg2('Вы получили '.$exxp.' опыта.',1);
break;

case 127:
	if(empty($look)) // если еще не выбрали вещь для снятия апгрейда
		{
		$q = $db->query("select id from `invent` where up<>0 and flag_rinok=0 and flag_arenda=0 and flag_equip=0 and flag_sklad=0 and login='{$f['login']}';"); // все вещи с апгрейдом
		if($q->num_rows == 0) msg('У вас нет вещей, пригодных для снятия модификации', 1);
		msg('Выберите нужную вещь:');
		while($a = $q->fetch_assoc())
			{
			$item = $items->shmot($a['id']);
			knopka('inv.php?mod=useitem&iid='.$iid.'&look='.$item['id'], $item['name']);
			}
		fin();
		}
	if($look <= 0) msg('Вещь не найдена в вашем рюкзаке!', 1);
	$q = $db->query("select id from `invent` where id='{$look}' and flag_rinok=0 and flag_arenda=0 and flag_equip=0 and flag_sklad=0 limit 1;");
	if($q->num_rows == 0) msg('Вещь не найдена в вашем рюкзаке!', 1);
	$a = $q->fetch_assoc();
	$item = $items->shmot($a['id']);
	if(empty($ok))
		{
		msg('Вы уверены, что хотите снять модификацию с '.$item['name'].'?');
		knopka('inv.php?mod=useitem&iid='.$iid.'&look='.$look.'&ok=1', 'Снять модификацию');
		knopka('inv.php', 'Инвентарь');
		fin();
		}
	$q = $db->query("update `invent` set up=0 where id='{$look}' and flag_rinok=0 and flag_arenda=0 and flag_equip=0 and flag_sklad=0 limit 1;");
	$items->del_item($f['login'], $iid, 1);
	msg2('Модификация с '.$item['name'].' успешно снята.', 1);
break;
endswitch;
?>
