<?php
##############
# 24.12.2014 #
##############
switch($item['ido']):
case 121:	// свиток нападения
	if(empty($f['klan'])) msg2('Вы не в клане',1);
	if(empty($komu))
		{
		echo '<div class="board" style="text-align:left">';
		echo '<form action="inv.php?mod=useitem&iid='.$iid.'" method="POST">';
		echo 'Введите логин:<br/><input type="text" name="komu"/><br/>';
		echo '<input type="submit" value="Напасть"/></form></div>';
		knopka('inv.php', 'Вернуться', 1);
		fin();
		}
	$bz = get_login($komu);
	if($f['login'] == $bz['login']) msg2('Нельзя напасть на себя',1);
	if(empty($bz['klan'])) msg2('Персонаж не в клане',1);
	if($bz['loc'] == 1 or $bz['loc'] == 37 or $bz['loc'] == 91) msg2('Нападение невозможно, персонаж под защитой лагеря',1);
	if($bz['pvp'] == 0) msg2('Нападение невозможно, у персонажа выключен PvP статус',1);
	if($f['pvp'] == 0) msg2('Нападение невозможно, у Вас выключен PvP статус',1);
	if($f['loc'] == 1 or $f['loc'] == 37 or $f['loc'] == 91) msg2('Нападение из лагеря невозможно',1);
	if($bz['lastdate'] < $t - 300 && $bz['rabota'] < $t - 3600) msg2('Персонаж оффлайн',1);
	if($bz['hpnow'] <= 0) msg2('У персонажа отрицательное здоровье',1);
	if($bz['lvl'] < 6) msg2('Персонаж меньше 6 уровня и находится под защитой новичков',1);
	if($bz['status'] == 1)
		{
		$q = $db->query("select krov from `battle` where id={$bz['boi_id']} limit 1;");
		$a = $q->fetch_assoc();
		if($a['krov'] == 0 or $a['krov'] == 1 or $a['krov'] == 2) msg('Персонаж находится в бою с ботами, нападение невозможно.',1);
		if($a['krov'] == 4) msg('Персонаж дерется на арене, нападение невозможно.',1);
		if($bz['komanda'] == 1) $mykom = 2; else $mykom = 1;
		$q = $db->query("select count(*) from `combat` where komanda={$mykom} AND boi_id={$bz['boi_id']};");
		$count = $q->fetch_assoc();
		$count = $count['count(*)'];
		if($count >= 10) msg2('В вашей команде уже максимальное количество бойцов - 10.',1);
		$logboi = '<span style="color:'.$notice.'">'.date("H:i:s").' '.$f['login'].' использует свиток нападения и нападает на '.$bz['login'].'</span><br/>';
		$q = $db->query("insert into `battlelog` values (0,{$bz['boi_id']},'{$t}','{$logboi}');");
		$items->del_item($f['login'], $item['id'], 1);
		$boi_id = $bz['boi_id'];
		toBoi($f,$mykom);
		knopka('battle.php', 'Вы напали на '.$bz['login'], 1);
		fin();
		}
	else
		{
		$boi_id = addBoi(5);
		$logboi = '<span style="color:'.$notice.'">'.date("H:i:s").' <b>'.$f['login'].' использует свиток нападения и нападает на '.$bz['login'].'</b></span><br/>';
		$q = $db->query("insert into `battlelog` values (0,'{$boi_id}','{$t}','{$logboi}');");
		$items->del_item($f['login'], $item['id'], 1);
		$q = $db->query("update `users` set hpnow=hpmax,mananow=manamax where id={$bz['id']} limit 1;");
		$bz['hpnow'] = $bz['hpmax'];
		$bz['mananow'] = $bz['manamax'];
		toBoi($bz,1);
		toBoi($f,2);
		knopka('battle.php', 'Вы напали на '.$bz['login'],1);
		fin();
		}
break;

case 122:	// свиток развоплощения
	if(empty($f['klan'])) msg('Вы не в клане',1);
	//if(!empty($f['admin'])) msg('Вы в администрации игры, незачем распугивать игроков.',1);
	if(empty($komu))
		{
		echo '<div class="board" style="text-align:left">';
		echo '<form action="inv.php?mod=useitem&iid='.$iid.'" method="POST">';
		echo 'Введите логин:<br/><input type="text" name="komu"/><br/>';
		echo '<input type="submit" value="Напасть"/></form></div>';
		knopka('inv.php', 'Вернуться', 1);
		fin();
		}
	$bz = get_login($komu);
	if($f['login'] == $bz['login']) msg2('Нельзя напасть на себя',1);
	if(empty($bz['klan'])) msg2('Персонаж не в клане',1);
	if($bz['klan'] == $f['klan']) msg2('Нельзя нападать на соклан!', 1);
	//if(!empty($bz['admin'])) msg('Персонаж в администрации игры, нападение невозможно.',1);
	if($bz['loc'] == 1 or $bz['loc'] == 37 or $bz['loc'] == 91) msg2('Нападение невозможно, персонаж под защитой лагеря',1);
	if($bz['pvp'] == 0) msg2('Нападение невозможно, у персонажа выключен PvP статус',1);
	if($f['pvp'] == 0) msg2('Нападение невозможно, у Вас выключен PvP статус',1);
	if($f['loc'] == 1 or $f['loc'] == 37 or $f['loc'] == 91) msg2('Нападение из лагеря невозможно',1);
	if($bz['lastdate'] < $t - 300 && $bz['rabota'] < $t - 3600) msg2('Персонаж оффлайн',1);
	if($bz['hpnow'] <= 0) msg2('У персонажа отрицательное здоровье',1);
	if($bz['lvl'] < 6) msg2('Персонаж меньше 6 уровня и находится под защитой новичков',1);
	if($bz['status'] == 1) msg2('Персонаж в бою, нападение невозможно.',1);
	else
		{
		$boi_id = addBoi(3);
		$logboi = '<span style="color:'.$male.'">'.date("H:i:s").' <b>'.$f['login'].' использует свиток развоплощения и нападает на '.$bz['login'].'</b></span><br/>';
		$q = $db->query("insert into `battlelog` values (0,'{$boi_id}','{$t}','{$logboi}');");
		$items->del_item($f['login'], $item['id'], 1);
		$q = $db->query("update `users` set hpnow=hpmax,mananow=manamax where id={$bz['id']} limit 1;");
		$bz['hpnow'] = $bz['hpmax'];
		$bz['mananow'] = $bz['manamax'];
		toBoi($bz,1);
		toBoi($f,2);
		knopka('battle.php', 'Вы напали на '.$bz['login'],1);
		fin();
		}
break;

case 153:
	$regen = 50;
	$f['hpnow'] += $regen;
	if($f['hpnow'] > $f['hpmax']) $f['hpnow'] = $f['hpmax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set hpnow={$f['hpnow']} where id={$f['id']} limit 1;");
	msg2('Здоровье +50',1);
break;

case 154:
	$regen = 100;
	$f['hpnow'] += $regen;
	if($f['hpnow'] > $f['hpmax']) $f['hpnow'] = $f['hpmax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set hpnow={$f['hpnow']} where id={$f['id']} limit 1;");
	msg2('Здоровье +100',1);
break;

case 155:
	$regen = 150;
	$f['hpnow'] += $regen;
	if($f['hpnow'] > $f['hpmax']) $f['hpnow'] = $f['hpmax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set hpnow={$f['hpnow']} where id={$f['id']} limit 1;");
	msg('Здоровье +150',1);
break;

case 156:
	$regen = 250;
	$f['hpnow'] += $regen;
	if($f['hpnow'] > $f['hpmax']) $f['hpnow'] = $f['hpmax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set hpnow={$f['hpnow']} where id={$f['id']} limit 1;");
	msg2('Здоровье +250',1);
break;

case 625:
	$regen = 350;
	$f['hpnow'] += $regen;
	if($f['hpnow'] > $f['hpmax']) $f['hpnow'] = $f['hpmax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set hpnow={$f['hpnow']} where id={$f['id']} limit 1;");
	msg2('Здоровье +350',1);
break;

case 626:
	$regen = 500;
	$f['hpnow'] += $regen;
	if($f['hpnow'] > $f['hpmax']) $f['hpnow'] = $f['hpmax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set hpnow={$f['hpnow']} where id={$f['id']} limit 1;");
	msg2('Здоровье +500',1);
break;

case 627:
	$regen = 750;
	$f['hpnow'] += $regen;
	if($f['hpnow'] > $f['hpmax']) $f['hpnow'] = $f['hpmax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set hpnow={$f['hpnow']} where id={$f['id']} limit 1;");
	msg2('Здоровье +750',1);
break;

case 628:
	$regen = 1000;
	$f['hpnow'] += $regen;
	if($f['hpnow'] > $f['hpmax']) $f['hpnow'] = $f['hpmax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set hpnow={$f['hpnow']} where id={$f['id']} limit 1;");
	msg2('Здоровье +1000',1);
break;

case 629:
	$regen = 1500;
	$f['hpnow'] += $regen;
	if($f['hpnow'] > $f['hpmax']) $f['hpnow'] = $f['hpmax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set hpnow={$f['hpnow']} where id={$f['id']} limit 1;");
	msg2('Здоровье +1500',1);
break;

case 191:
	$regen = 50;
	$f['mananow'] += $regen;
	if($f['mananow'] > $f['manamax']) $f['mananow'] = $f['manamax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set mananow={$f['mananow']} where id={$f['id']} limit 1;");
	msg2('Мана +50',1);
break;

case 192:
	$regen = 100;
	$f['mananow'] += $regen;
	if($f['mananow'] > $f['manamax']) $f['mananow'] = $f['manamax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set mananow={$f['mananow']} where id={$f['id']} limit 1;");
	msg2('Мана +100',1);
break;

case 193:
	$regen = 150;
	$f['mananow'] += $regen;
	if($f['mananow'] > $f['manamax']) $f['mananow'] = $f['manamax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set mananow={$f['mananow']} where id={$f['id']} limit 1;");
	msg2('Мана +150',1);
break;

case 194:
	$regen = 250;
	$f['mananow'] += $regen;
	if($f['mananow'] > $f['manamax']) $f['mananow'] = $f['manamax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set mananow={$f['mananow']} where id={$f['id']} limit 1;");
	msg2('Мана +250',1);
break;

case 630:
	$regen = 350;
	$f['mananow'] += $regen;
	if($f['mananow'] > $f['manamax']) $f['mananow'] = $f['manamax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set mananow={$f['mananow']} where id={$f['id']} limit 1;");
	msg2('Мана +350',1);
break;

case 631:
	$regen = 500;
	$f['mananow'] += $regen;
	if($f['mananow'] > $f['manamax']) $f['mananow'] = $f['manamax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set mananow={$f['mananow']} where id={$f['id']} limit 1;");
	msg2('Мана +500',1);
break;

case 632:
	$regen = 750;
	$f['mananow'] += $regen;
	if($f['mananow'] > $f['manamax']) $f['mananow'] = $f['manamax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set mananow={$f['mananow']} where id={$f['id']} limit 1;");
	msg2('Мана +750',1);
break;

case 633:
	$regen = 1000;
	$f['mananow'] += $regen;
	if($f['mananow'] > $f['manamax']) $f['mananow'] = $f['manamax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set mananow={$f['mananow']} where id={$f['id']} limit 1;");
	msg2('Мана +1000',1);
break;

case 634:
	$regen = 1500;
	$f['mananow'] += $regen;
	if($f['mananow'] > $f['manamax']) $f['mananow'] = $f['manamax'];
	$items->del_item($f['login'], $item['id'], 1);
	$q = $db->query("update `users` set mananow={$f['mananow']} where id={$f['id']} limit 1;");
	msg2('Мана +1500',1);
break;

case 620:
	$items->del_item($f['login'], $item['id'], 1);
	$timer = $t + 3600;
	$q = $db->query("update `users` set doping=6,doping_time='{$timer}' where id={$f['id']} limit 1;");
	msg2('Вы выпили эликсир ловкости',1);
break;

case 621:
	$items->del_item($f['login'], $item['id'], 1);
	$timer = $t + 3600;
	$q = $db->query("update `users` set doping=7,doping_time='{$timer}' where id={$f['id']} limit 1;");
	msg2('Вы выпили эликсир реакции',1);
break;

case 622:
	$items->del_item($f['login'], $item['id'], 1);
	$timer = $t + 3600;
	$q = $db->query("update `users` set doping=8,doping_time='{$timer}' where id={$f['id']} limit 1;");
	msg2('Вы выпили эликсир жизненной энергии',1);
break;

case 623:
	$items->del_item($f['login'], $item['id'], 1);
	$timer = $t + 3600;
	$q = $db->query("update `users` set doping=9,doping_time='{$timer}' where id={$f['id']} limit 1;");
	msg2('Вы выпили эликсир магической энергии',1);
break;

case 624:
	$items->del_item($f['login'], $item['id'], 1);
	$timer = $t + 3600;
	$q = $db->query("update `users` set doping=10,doping_time='{$timer}' where id={$f['id']} limit 1;");
	msg2('Вы выпили эликсир вышибалы',1);
break;

case 635:
	$items->del_item($f['login'], $item['id'], 1);
	$timer = $t + 3600;
	$q = $db->query("update `users` set doping=1,doping_time='{$timer}' where id={$f['id']} limit 1;");
	msg2('Вы выпили брагу',1);
break;

case 636:
	$items->del_item($f['login'], $item['id'], 1);
	$timer = $t + 3600;
	$q = $db->query("update `users` set doping=2,doping_time='{$timer}' where id={$f['id']} limit 1;");
	msg2('Вы выпили пиво',1);
break;

case 637:
	$items->del_item($f['login'], $item['id'], 1);
	$timer = $t + 3600;
	$q = $db->query("update `users` set doping=3,doping_time='{$timer}' where id={$f['id']} limit 1;");
	msg2('Вы выпили вино',1);
break;

case 638:
	$items->del_item($f['login'], $item['id'], 1);
	$timer = $t + 3600;
	$q = $db->query("update `users` set doping=4,doping_time='{$timer}' where id={$f['id']} limit 1;");
	msg2('Вы выпили самогон',1);
break;

case 639:
	$items->del_item($f['login'], $item['id'], 1);
	$timer = $t + 3600;
	$q = $db->query("update `users` set doping=5,doping_time='{$timer}' where id={$f['id']} limit 1;");
	msg2('Вы выпили рисовый шнапс',1);
break;
default: fin(); break;
endswitch;
?>
