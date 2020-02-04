<?php

##############
# 24.12.2014 #
##############
require_once('inc/top.php'); // вывод на экран
require_once('inc/check.php'); // вход в игру
require_once('inc/head.php');
require_once('class/items.php'); // работа с вещами

$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';   //действие
$go = isset($_REQUEST['go']) ? $_REQUEST['go'] : '';   //действие
$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : '';   //действие
$newname = isset($_REQUEST['newname']) ? $_REQUEST['newname'] : '';   //новое имя
$newpass = isset($_REQUEST['newpass']) ? $_REQUEST['newpass'] : '';   //новый пароль
$newpass2 = isset($_REQUEST['newpass2']) ? $_REQUEST['newpass2'] : '';  //повтор пароля
$newinfa = isset($_REQUEST['newinfa']) ? $_REQUEST['newinfa'] : '';   //новая инфа
$srok = isset($_REQUEST['srok']) ? intval($_REQUEST['srok']) : 0;   //для ПСЖ на несколько суток
$strelki = isset($_REQUEST['strelki']) ? intval($_REQUEST['strelki']) : 0;   //для ПСЖ на несколько суток
$stat_zdor = isset($_REQUEST['stat_zdor']) ? $_REQUEST['stat_zdor'] : 0; //здоровье
$stat_sila = isset($_REQUEST['stat_sila']) ? $_REQUEST['stat_sila'] : 0; //сила
$stat_inta = isset($_REQUEST['stat_inta']) ? $_REQUEST['stat_inta'] : 0; //интуиция
$stat_lovka = isset($_REQUEST['stat_lovka']) ? $_REQUEST['stat_lovka'] : 0; //ловкость
$stat_intel = isset($_REQUEST['stat_intel']) ? $_REQUEST['stat_intel'] : 0; //интеллект

switch ($mod):
	case 'edit':
		if ($f['status'] == 1)
			{
			knopka('battle.php', 'Вы в бою!', 1);
			fin();
			}
		if (empty($ok))
			{
			echo '<div class="board">';
			echo '<form method="POST" action="anketa.php?mod=edit&ok=1">';
			echo 'Имя в реале: <span style="color:'.$male.'">a-Zа-Я</span><br/><input type="text" name="newname" value="'.$f['name'].'"/><br/>';
			echo 'Пароль (оставьте пустым, если не хотите менять): <span style="color:'.$male.'">a-Z0-9</span><br/><input type="text" name="newpass" value=""/><br/>';
			echo 'Повторите пароль:<br/><input type="text" name="newpass2" value=""/><br/>';
			echo 'Информационная строчка (300 симв.)<br/><input type="text" name="newinfa" value="'.$f['infa'].'"/><br/>';
			$graf = array(0 => '', 1 => '', 2=>'', 3=>'');
			$graf[$f['grafika']] = ' selected';
			echo 'Графика: <select name="srok">
			<option value="1"'.$graf[1].'>Включить</option>
			<option value="2"'.$graf[2].'>Оставить смайлы</option>
			<option value="3"'.$graf[3].'>Оставить карту</option>
			<option value="4"'.$graf[0].'>Выключить</option>
			</select><br/>';
			$graf = array(1 => '', 2 => '', 3=>'');
			$graf[$f['strelki']] = ' selected';
			echo 'Тип кнопок перемещения: <select name="strelki">
			<option value="1"'.$graf[1].'>Каждая отдельно</option>
			<option value="2"'.$graf[2].'>Сгруппированы</option>
			<option value="3"'.$graf[3].'>Стрелки</option>
			</select><br/>';
			echo '<input type="submit" value="Отправить"/></form>';
			echo '</div>';
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		if (30 < mb_strlen($newname, 'UTF-8') or mb_strlen($newname, 'UTF-8') < 3)
			{
			msg2('Неверно набрано имя или его длина не входит в промежуток 3..30 символов!');
			knopka('anketa.php?mod=edit', 'Назад', 1);
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		if ($newname != $f['name']) $f['name'] = ekr($newname);
		if ($newinfa != $f['infa']) $f['infa'] = ekr(mb_substr($newinfa, 0, 300, 'UTF-8'));
		if (!empty($newpass) && !empty($newpass2))
			{
			if (mb_strlen($newpass, 'UTF-8') < 9 or $newpass != $newpass2)
				{
				msg2('Неверно набран пароль или его длина меньше 9 символов!');
				knopka('anketa.php?mod=edit', 'Назад', 1);
				knopka('anketa.php', 'В анкету', 1);
				fin();
				}
			$f['pass'] = md5($newpass);
			unset($_SESSION);
			session_destroy();
			session_start();
			setcookie('id', '', $t - 3600, '/');
			setcookie('hash', '', $t - 3600, '/');
			setcookie('id', $f['id'], $t + 86400 * 365, '/');
			setcookie('hash', $f['pass'], $t + 86400 * 365, '/');
			}
		if ($srok == 1) $f['grafika'] = 1;		// включена
		elseif ($srok == 2) $f['grafika'] = 2;	// только смайлы
		elseif ($srok == 3) $f['grafika'] = 3;	// только карта
		else $f['grafika'] = 0;					// выключена
		if($strelki == 1) $f['strelki'] = 1;
		elseif($strelki == 2) $f['strelki'] = 2;
		else $f['strelki'] = 3;
		$q = $db->query("update `users` set name='{$f['name']}',infa='{$f['infa']}',pass='{$f['pass']}',grafika='{$f['grafika']}',strelki='{$f['strelki']}' where id={$f['id']} limit 1;");
		msg2('Профиль сохранен!');
		knopka('anketa.php', 'В анкету', 1);
		fin();
		break;

	case 'ref':
		msg2('Ваша ссылка для привлечения друзей:<br/><span style="color:'.$female.'">http://'.$_SERVER['SERVER_NAME'].'/start.php?start&r='.$f['id'].'</span><br/>Список, кто зарегистрировался по вашей реферальной ссылке:');
		$q = $db->query("select login,lvl,sex from `users` where ref='{$f['id']}' and login<>'{$f['login']}' order by login;");
		$count = 0;
		while ($s = $q->fetch_assoc())
			{
			if ($s['sex'] == 1) $color_login = $male;
			else $color_login = $female;
			$count++;
			knopka('infa.php?mod=uzinfa&lgn='.$s['login'], $count.'. <span style="color:'.$color_login.'">'.$s['login'].' ['.$s['lvl'].']</span>', 0);
			}
		knopka('anketa.php', 'В анкету', 1);
		fin();
		break;

	case 'pvp':
		if(!empty($f['status'])) msg('Вы не можете сейчас переключить данный статус.', 1);
		if(empty($f['klan'])) msg('Вы не в клане, нет смысла от переключения статуса.', 1);
		if($f['pvpdate'] > $t) msg('Нельзя переключать данный статус чаще чем раз в неделю. Будет доступно '.date('d.m.Y H:i', $f['pvpdate']), 1);
		if($f['pvp'] == 1) $f['pvp'] = 0;
		else $f['pvp'] = 1;
		$f['pvpdate'] = $t + 60 * 60 * 24 * 7;
		$q = $db->query("update `users` set pvp='{$f['pvp']}',pvpdate='{$f['pvpdate']}' where id='{$f['id']}' limit 1;");
		msg('Вы изменили свой PvP статус', 1);
	break;

	case 'exit':
		if (empty($ok))
			{
			msg2('Вы действительно хотите выйти из игры? Это не обезопасит вас от нападения другими игроками.');
			knopka('anketa.php?mod=exit&ok=1', 'Выйти из игры', 1);
			knopka('anketa.php', 'Вернуться', 1);
			fin();
			}
		setcookie('id', '', $t - 3600);
		setcookie('hash', '', $t - 3600);
		session_destroy();
		unset($_COOKIE);
		unset($_SESSION);
		msg2('Вы успешно вышли из игры!');
		knopka('index.php', 'На главную', 1);
		fin();
		break;

	case 'otkaz':
		if ($f['admin'] == 0)
			{
			msg2('Недостаточно прав');
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		if (empty($ok))
			{
			msg2('Вы действительно хотите сложить с себя полномочия?');
			knopka('anketa.php?mod=otkaz&ok=1', 'Отказаться', 1);
			knopka('anketa.php', 'Вернуться', 1);
			fin();
			}
		$mess = '<b>Каратели</b>: персонаж '.$f['login'].' сложил с себя полномочия';
		require_once('inc/i.php');
		$q = $db->query("insert into `letters` values(0,0,'{$t}','{$admin}','{$settings['bot']}','{$mess}',0,0);");
		$q = $db->query("update `users` set admin=0 where id={$f['id']} AND admin>0 limit 1;");
		msg2('Ваш статус обновлен');
		knopka('anketa.php', 'В анкету', 1);
		fin();
		break;

	case 'invite':
		if ($f['status'] == 1)
			{
			knopka('battle.php', 'Вы в бою!');
			fin();
			}
		if (empty($f['klan_invite']))
			{
			msg2('У вас нет приглашения в клан');
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		if ($go == 1)
			{
			// получим данные по пригласившему клану
			$q = $db->query("select * from `klans` where name='{$f['klan_invite']}' limit 1;");
			if ($q->num_rows == 0)
				{
				msg2('Такого клана не существует.');
				$q = $db->query("update `users` set klan_invite='' where id={$f['id']} limit 1;"); // удалим из инвайта
					knopka('anketa.php', 'В анкету', 1);
				fin();
				}
			$k = $q->fetch_assoc();
			$q = $db->query("update `users` set klan_invite='',klan_time='{$t}',klan='{$k['name']}',klan_status=0,nalog=0 where id={$f['id']} limit 1;");
			msg2('Вы вступили в клан '.$f['klan_invite']);
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		// любой другой вариант
		else
			{
			$q = $db->query("update `users` set klan_invite='' where id={$f['id']} limit 1;");
			msg2('Вы отказались от всупления в клан '.$f['klan_invite']);
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		break;

	case 'outklan':
		if (empty($f['klan'])) msg2('Вы не в клане!', 1);
		if ($f['status'] == 1)
			{
			knopka('battle.php', 'Вы в бою!');
			fin();
			}
		if ($f['status'] == 2)
			{
			knopka('arena.php', 'У вас заявка на арене!');
			fin();
			}
		if (empty($ok))
			{
			msg2('Вы действительно хотите выйти из клана?');
			knopka('anketa.php?mod=outklan&ok=1', 'Да, выйти', 1);
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		if ($f['klan_status'] == 3)
			{
			msg2('Глава не может выйти из клана.');
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		$q = $db->query("update `users` set klan='',klan_time='{$t}',klan_status=0 where id='{$f['id']}' AND klan<>'' limit 1;");
		$q = $db->query("update `klans` set kazna=kazna+{$f['nalog']} where name='{$f['klan']} limit 1;"); // деньги налога обратно в клан
		msg2('Ваш статус обновлен');
		knopka('anketa.php', 'В анкету', 1);
		fin();
		break;

	case 'razvod': // переделать на храм или еще куда-то
		if (empty($f['brak']))
			{
			msg2('Вы не состоите в браке.');
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		if (empty($ok))
			{
			msg2('Вы действительно хотите развестись с '.$f['brak'].'?');
			knopka('anketa.php?mod=razvod&ok=1', 'Да, развестись', 1);
			knopka('anketa.php', 'Вернуться в внкету', 1);
			fin();
			}
		$mess = '<b>Системное сообщение</b>: персонаж '.$f['login'].' развелся с вами.';
		$q = $db->query("insert into `letters` values(0,0,'{$t}','{$f['brak']}','{$settings['bot']}','{$mess}',0,0);");
		$q = $db->query("update `users` set brak='' where id={$f['id']} limit 1;");
		$q = $db->query("update `users` set brak='' where login='{$f['brak']}' limit 1;");
		msg2('Вы успешно разведены');
		knopka('anketa.php', 'В анкету', 1);
		fin();
		break;

	case 'ressurect':
		if ($f['status'] == 1)
			{
			knopka('battle.php', 'Вы в бою!');
			fin();
			}
		if ($f['status'] == 2)
			{
			knopka('arena.php', 'У вас заявка на арене!');
			fin();
			}
		if ($f['hpnow'] == $f['hpmax'])
			{
			msg2('Вы не мертвы.');
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		if ($f['ruda'] < 1)
			{
			msg2('Не хватает руды. Нужно 1.');
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		if (empty($ok))
			{
			$_SESSION['count'] = 1;
			msg2('Вы действительно хотите воскреснуть за 1 руды?');
			knopka('anketa.php?mod=ressurect&ok=1', 'Да, воскреснуть', 1);
			knopka('anketa.php', 'Вернуться в анкету', 1);
			fin();
			}
		if (empty($_SESSION['count']) or $_SESSION['count'] > 1) msg('Ошибка двойного нажатия',1);
		$_SESSION['count']++;
		$q = $db->query("update `users` set hpnow=hpmax,mananow=manamax,ruda=ruda-1 where id='{$f['id']}' limit 1;");
		msg2('Вы воскресли за 1 руды!');
		knopka('anketa.php', 'В анкету', 1);
		fin();
		break;

	case 'prof':
		echo '<div class="board3"><b>Профессии</b></div><div class="board2" style="text-align:left;"> ';
		echo 'Рыболов: '.$f['p_fishman'].'<br/>';
		echo '</div>';
		knopka('anketa.php', 'В анкету', 1);
		fin();
		break;

	case 'kv':
		require_once('kvest/dnevnik.php');
		break;

	case 'magic_book':
		msg2('Книга магии в разработке :)');
		knopka('anketa.php', 'В анкету', 1);
		fin();
		break;

	case 'stats':
		$q = $db->query("select count(`invent`.`id`) from `invent`,`item` where ((`invent`.`login`='{$f['login']}' and `invent`.`flag_arenda`=0) or (`invent`.`arenda_login`='{$f['login']}' and `invent`.`flag_arenda`=1)) and `invent`.`flag_rinok`=0 and `invent`.`flag_sklad`=0 and `invent`.`flag_equip`=1 and `item`.`equip`<>'' and `invent`.`ido`=`item`.`id`;");
		$a = $q->fetch_assoc();
		if ($a['count(`invent`.`id`)'] > 0)
			{
			msg2('Предварительно нужно снять всю <a href="inv.php?mod=equip">экипировку</a>!');
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		if (empty($ok))
			{
			if (!empty($_SESSION['stats']))
				{
				msg2('Ваши статы изменены');
				unset($_SESSION['stats']);
				}
			echo '<div class="board">';
			echo '<form action="anketa.php?mod=stats&ok=1" method="POST">';
			echo '</div><div class="board2">Здесь вы можете сменить ваши статы как вам угодно. У вас не использовано '.$stat_free.' из '.$all_stats.'.</div><div class="board2">';
			echo '<p align="left">Здоровье:<br/> <input type="number" name="stat_zdor" value="'.$f['zdor'].'"/><br/>';
			echo 'Сила:<br/> <input type="number" name="stat_sila" value="'.$f['sila'].'"/><br/>';
			echo 'Интуиция:<br/> <input type="number" name="stat_inta" value="'.$f['inta'].'"/><br/>';
			echo 'Ловкость:<br/> <input type="number" name="stat_lovka" value="'.$f['lovka'].'"/><br/>';
			echo 'Интеллект:<br/> <input type="number" name="stat_intel" value="'.$f['intel'].'"/><br/></p>';
			echo '<input type="submit" value="Далее"/></form>';
			echo '</div>';
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		$stat_sila = intval($stat_sila);
		$stat_inta = intval($stat_inta);
		$stat_lovka = intval($stat_lovka);
		$stat_intel = intval($stat_intel);
		if ($stat_sila < 1 or $stat_inta < 1 or $stat_lovka < 1 or $stat_intel < 1)
			{
			msg2('Значение стата не может быть меньше 1');
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		$st2 = $stat_zdor + $stat_sila + $stat_inta + $stat_lovka + $stat_intel;

		if ($st2 > $all_stats)
			{
			msg2('Сумма статов не может превышать '.$all_stats.', у вас '.$st2.'. Вернитесь и исправьте.');
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		$q = $db->query("update `users` set zdor={$stat_zdor},sila={$stat_sila},inta={$stat_inta},lovka={$stat_lovka},intel={$stat_intel} where id={$f['id']} limit 1;");
		$_SESSION['stats'] = 1;
		header("location: anketa.php?mod=stats");
		fin();
		break;

	case 'vip':
		msg2('Руда: '.$f['ruda']);
		if ($f['vip'] > $t) msg2('VIP статус до '.date('d.m.Y H:i', $f['vip']));
		if (empty($go))
			{
			knopka('anketa.php?mod=vip&go=1', 'Купить руду', 1);
			knopka('anketa.php?mod=vip&go=3', 'Купить лотерейные билеты', 1);
			knopka('anketa.php?mod=vip&go=2', 'Продлить VIP', 1);
			knopka('anketa.php?mod=vip&go=4', 'Сменить логин', 1);
			fin();
			}
		else if ($go == 1)
			{
			msg2('Покупка руды пока недоступна в автоматическом режиме.<br/>
			Кошелек WebMoney <b>R422552891903</b><br/>
			Кошелек QIWI <b>+7923-123-72-03</b><br/>1 руда = 1 рубль.<br/>
			В комментариях необходимо указать Ваш игровой ник и уточнить, что вы хотите приобрести руду.<hr/>
			Пример: перевод 50р рублей с комментарием: "ник maslanin, покупка руды"<hr/>
			Так же необходимо написать админу письмо с просьбой проверить кошелек.<br/><br/>
			Про другие способы оплаты вы так же можете узнать у админа.');
			fin();
			}
		else if ($go == 2)
			{
			$cena = 30;
			$cena2 = 10;
			msg2('VIP статус дает вашему персонажу некоторые привилегии в игре, какие именно, смотрите в библиотеке. VIP продлевается на 30 суток за '.$cena.' руды, либо на 7 суток за '.$cena2.' руды.');
			if (empty($ok))
				{
				$_SESSION['count'] = 1;
				if ($f['ruda'] >= $cena) knopka('anketa.php?mod=vip&go=2&ok=1', 'Продлить VIP на 30 суток ('.$cena.' руды)', 1);
				if ($f['ruda'] >= $cena2) knopka('anketa.php?mod=vip&go=2&ok=2', 'Продлить VIP на 7 суток ('.$cena2.' руды)', 1);
				fin();
				}
			elseif($ok == 1)
				{
				if ($f['ruda'] < $cena)
					{
					msg2('У вас нет '.$cena.' руды');
					knopka('anketa.php', 'В анкету', 1);
					fin();
					}
				if (empty($_SESSION['count']) or $_SESSION['count'] > 1) msg('Ошибка двойного нажатия',1);
				$_SESSION['count']++;
				$f['ruda'] -= $cena;
				if ($f['vip'] > $t) $f['vip'] += 60 * 60 * 24 * 30;
				else $f['vip'] = $t + 60 * 60 * 24 * 30;
				$q = $db->query("update `users` set vip={$f['vip']},ruda={$f['ruda']} where id={$f['id']} limit 1;");
				msg2('VIP продлён на 30 суток за '.$cena.' руды');
				}
			elseif($ok == 2)
				{
				if ($f['ruda'] < $cena2)
					{
					msg2('У вас нет '.$cena2.' руды');
					knopka('anketa.php', 'В анкету', 1);
					fin();
					}
				if (empty($_SESSION['count']) or $_SESSION['count'] > 1) msg('Ошибка двойного нажатия',1);
				$_SESSION['count']++;
				$f['ruda'] -= $cena2;
				if ($f['vip'] > $t) $f['vip'] += 60 * 60 * 24 * 7;
				else $f['vip'] = $t + 60 * 60 * 24 * 7;
				$q = $db->query("update `users` set vip={$f['vip']},ruda={$f['ruda']} where id={$f['id']} limit 1;");
				msg2('VIP продлён на 7 суток за '.$cena2.' руды');
				}
			else msg('Нет такого варианта', 1);
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		else if ($go == 3)
			{
			// купить лотерейные билеты
			if (empty($srok))
				{
				$_SESSION['count'] = 1;
				echo '<div class="board">';
				echo '<form action="anketa.php?mod=vip&go=3" method="POST">';
				$bil = intval($f['ruda'] / 10);
				if ($bil > 100) $bil = 100;
				echo 'Введите количество билетов. (вы можете купить '.$bil.', цена 1 билета 10 руды)<br/>';
				echo '<input type="number" value="'.$bil.'" name="srok"/><br/>';
				echo '<input type="submit" value="Купить"/>';
				echo '</div>';
				knopka('anketa.php', 'В анкету', 1);
				fin();
				}
			$srok = intval($srok);
			if ($srok < 1) $srok = 1;
			if ($srok > 100) $srok = 100; //не более 100 за раз
			$cena = $srok * 10;
			if ($f['ruda'] < $cena)
				{
				msg2('У вас не хватает руды для покупки '.$srok.' билетов. Нужно '.$cena);
				knopka('anketa.php', 'В анкету', 1);
				fin();
				}
			if (empty($_SESSION['count']) or $_SESSION['count'] > 1) msg('Ошибка двойного нажатия',1);
			$_SESSION['count']++;
			for($i = 1; $i <= $srok; $i ++) $items->add_item($f['login'], 123, 1);
			$q = $db->query("update `users` set ruda=ruda-{$cena} where id={$f['id']} limit 1");
			msg2('Вы получили '.$srok.' билетов в лото за '.$cena.' руды');
			knopka('anketa.php', 'В анкету', 1);
			fin();
			}
		else if ($go == 4)
			{
			if (empty($newname))
				{
				$_SESSION['count'] = 1;
				echo '<div class="board" style="text-align:left">';
				echo 'Смена логина стоит 500 руды<br/>';
				echo '<form action="anketa.php?mod=vip&go=4" method="POST">
			Логин: '.$f['login'].'<br/>
			Новый логин<br/><input type="text" name="newname"/><br/>
			<input type="submit" value="Ок"/></form></div>';
				knopka('adm.php', 'В админку', 1);
				fin();
				}
			if ($f['ruda'] < 500) msg2('Недостаточно руды для данного действия!', 1);
			if (preg_match("/[^a-zA-Z0-9_а-яА-ЯёЁ]/", $newname)) msg2('Недопустимый логин', 1);
			$q = $db->query("select login from `users` where login='{$newname}' limit 1;");
			if ($q->num_rows > 0)
				{
				msg2('<span style="color:red"><b>Логин '.$newname.' уже занят. Выберите другой.</b></span>');
				knopka('javascript:history.go(-1)', 'Назад', 1);
				fin();
				}
			if (empty($_SESSION['count']) or $_SESSION['count'] > 1) msg('Ошибка двойного нажатия',1);
			$_SESSION['count']++;
			$q = $db->query("update `chat` set login='{$newname}' WHERE login='{$f['login']}';");
			$q = $db->query("update `chat` set privat='{$newname}' WHERE privat='{$f['login']}';");
			$q = $db->query("update `combat` set login='{$newname}' WHERE login='{$f['login']}';");
			$q = $db->query("update `forum_comm` set login='{$newname}' WHERE login='{$f['login']}';");
			$q = $db->query("update `forum_topic` set login='{$newname}' WHERE login='{$f['login']}';");
			$q = $db->query("update `invent` set login='{$newname}' WHERE login='{$f['login']}';");
			$q = $db->query("update `invent` set arenda_login='{$newname}' WHERE arenda_login='{$f['login']}';");
			$q = $db->query("update `ipsoft` set login='{$newname}' WHERE login='{$f['login']}';");
			$q = $db->query("update `letters` set login='{$newname}' WHERE login='{$f['login']}';");
			$q = $db->query("update `letters` set login_from='{$newname}' WHERE login_from='{$f['login']}';");
			$q = $db->query("update `log_peredach` set login='{$newname}' WHERE login='{$f['login']}';");
			$q = $db->query("update `log_peredach` set login_per='{$newname}' WHERE login_per='{$f['login']}';");
			$q = $db->query("update `magic` set login='{$newname}' WHERE login='{$f['login']}';");
			$q = $db->query("update `users` set login='{$newname}',ruda=ruda-500 WHERE id='{$f['id']}' limit 1;");
			msg2('Ваш новый логин: '.$newname.', необходимо заново представиться системе.');
			unset($_SESSION);
			session_destroy();
			setcookie('id', '', $t - 3600);
			setcookie('hash', '', $t - 3600);
			setcookie('lgn', '', $t - 3600);
			knopka('index.php', 'На главную', 1);
			fin();
			}
		break;

	default:
		$f = calcparam($f); //секция просчёта модификаторов, просчитывается каждый раз при заходе в анкету...
		if(!empty($_REQUEST['dropqv']) and !empty($f['kvest_now']))
			{
			$f['kvest_now'] = 0;
			$f['kvest_step'] = 0;
			$q = $db->query("update `users` set kvest_now=0,kvest_step=0 where id='{$f['id']}' limit 1;");
			msg('Вы отказались от задания');
			}
		echo '<div class="board2" style="text-align:left;">
		Персонаж: <span style="color:'.$logincolor.'"><b>'.$f['login'].'</b></span> [<a href="infa.php?mod=uzinfa&lgn='.$f['login'].'">Анкета</a>]<br/>
		Уровень: ['.$f['lvl'].'] <br/>
		Жизни: <b><span style="color:'.$hpcolor.'">'.$f['hpnow'].'</span></b>/<b><span style="color:'.$hpcolor.'">'.$f['hpmax'].'</span></b> ['.$hp_plus.' в мин.]<br/>
		Мана: <b><span style="color:'.$manacolor.'">'.$f['mananow'].'</span></b>/<b><span style="color:'.$manacolor.'">'.$f['manamax'].'</span></b> ['.$mp_plus.' в мин.]';
		if ($f['hpnow'] < $f['hpmax'] and $f['ruda'] >= 1 and $f['status'] == 0)
			{
			echo '<br/>[<a href="anketa.php?mod=ressurect">Вылечиться (1 руды)</a>]';
			}
		echo '</div>';
		echo '<div class="board2" style="text-align:left;">';
		echo 'Опыт: '.number_format($f['exp'], 0, ".", ".").' (Осталось: '.number_format($tolev, 0, ".", ".").')';
		echo '<br/>Монеты: '.number_format($f['money'], 0, ".", ".").' ';
		if (0 < $f['bank']) echo ' - В банке: '.number_format($f['bank'], 0, ".", ".");
		echo '<br/>';
		if($f['vip'] > $t) echo '<b>VIP-статус</b> до '.date('d.m.Y H:i', $f['vip']).'<br/>';
		if (!empty($f['klan'])) echo '<a href="lib.php?mod=nalog">Налог: <b>'.$f['nalog'].'</b></a><br/>';
		if (!empty($f['altar']) and $f['altar_time'] >= $t) echo 'Алтарь '.$f['altar'].' уровня, еще '.ceil(($f['altar_time'] - $t) / 60).' минут<br/>';
		if ($f['ruda'] > 0) echo 'Руда: '.number_format($f['ruda'], 0, ".", ".").'<br/>';
		if ($f['fishrod'] > 0) echo 'Прочность удочки: '.number_format($f['fishrod'], 0, ".", ".").'<br/>';
		echo '</div>';
///////////////////////////////////////////////
		if (!empty($f['klan']))
			{
			echo '<div class="board2" style="text-align:left;">
			Клан: '.$f['klan'].' ';
			if (0 < $f['klan_status']) echo '<br/>[<a href="klan.php">Управление</a>]';
			echo '<br/>[<a href="anketa.php?mod=outklan">Покинуть</a>]<br/>';

			if ($f['klan_status'] == 3) echo 'Статус: Глава<br/>';
			elseif ($f['klan_status'] == 2) echo 'Статус: Наместник<br/>';
			elseif ($f['klan_status'] == 1) echo 'Статус: Зам. главы<br/>';
			echo 'Очки чести: '.$f['chest'].'<br/>';
			echo '</div>';
			}
		if (!empty($f['allince']))
			{
			echo '<div class="board2" style="text-align:left;">
			Альянс: '.$f['alliance'].' ';
			echo '[<a href="anketa.php?mod=outal">Покинуть</a>] <br/>';
			echo '</div>';
			}
//////////////////////////////////////////////
		echo '<div class="board2" style="text-align:left;">';
		if ($f['doping'] > 0 and $f['doping_time'] > $t)
			{
			echo 'Допинг: ';
			if ($f['doping'] == 1) echo 'Брага';
			elseif ($f['doping'] == 2) echo 'Пиво';
			elseif ($f['doping'] == 3) echo 'Вино';
			elseif ($f['doping'] == 4) echo 'Самогон';
			elseif ($f['doping'] == 5) echo 'Рисовый шнапс';
			elseif ($f['doping'] == 6) echo 'Ловкость';
			elseif ($f['doping'] == 7) echo 'Реакция';
			elseif ($f['doping'] == 8) echo 'Жизненная энергия';
			elseif ($f['doping'] == 9) echo 'Магическая энергия';
			elseif ($f['doping'] == 10) echo 'Вышибала';
			echo ' еще '.ceil(($f['doping_time'] - $t) / 60).' мин.<br/>';
			}
		if (!empty($f['brak'])) echo 'Вы в браке с <a href="infa.php?mod=uzinfa&lgn='.$f['brak'].'">'.$f['brak'].'</a> [<a href="anketa.php?mod=razvod">Развестись</a>]<br/>';
		echo 'Бои: <span style="color:'.$notice.'"><b>'.$f['win'].'</b></span>/<span style="color:'.$female.'"><b>'.$f['lost'].'</b></span>';
		echo '<br/>Задание: '; // переделать в базу
		if(empty($f['kvest_now'])) echo 'нет';
		elseif($f['kvest_now'] == 1) echo 'Вы грабите корован на южном побережье';
		else echo 'неизвестно, сообщите админу';
		if(!empty($f['kvest_now'])) echo ' <a href="anketa.php?dropqv=1">[отказаться]</a>';
		echo '</div>';
		echo '<div class="board2" style="text-align:left;">
		<a href="anketa.php?mod=stats"><b>Статы: '.$stat_free.'</b></a><br/>
		Здоровье: '.$f['zdor'].'<br/>
		Сила: '.$f['sila'].'<br/>
		Интуиция: '.$f['inta'].'<br/>
		Ловкость: '.$f['lovka'].'<br/>
		Интеллект: '.$f['intel'].'<br/>

		<b>Модификаторы</b>:<br/>
		Крит: '.$f['krit'].'<br/>
		Уворот: '.$f['uvorot'].'<br/>
		Броня: '.$f['bron'].'<br/>';
		echo 'Урон: '.intval($f['uron'] * 1.4).' - '.intval($f['uron'] * 1.6).'<br/>';
		$sopromag = intval($f['bron'] * 0.1 + $f['intel']);
		if ($sopromag > 99) $sopromag = 99;
		echo 'Сопротивление магии: '.$sopromag.'%<br/>';
		echo '</div>';
		knopka('inv.php', 'Рюкзак');
		knopka('anketa.php?mod=ref', 'Реферальная ссылка');
		knopka('anketa.php?mod=prof', 'Профессии');
		knopka('anketa.php?mod=kv', 'Дневник');
		knopka('anketa.php?mod=edit', 'Изменить профиль');
		knopka('anketa.php?mod=vip', 'VIP услуги');
		if(!empty($f['klan']))
			{
			if($f['pvp'] == 1) $str = '<span style="color:green">On</span>'; else $str = '<span style="color:red">Off</span>';
			knopka('anketa.php?mod=pvp', 'PvP ('.$str.')');
			}
		knopka('start.php?exit', 'Выйти из игры');
		if ($f['admin'] > 0) knopka('anketa.php?mod=otkaz', 'Сложить полномочия');

		if (!empty($f['klan_invite']))
			{
			msg('Вас приглашают в клан <b>'.$f['klan_invite'].'</b>, принять приглашение?');
			knopka('anketa.php?mod=invite&go=1', 'Принять', 1);
			knopka('anketa.php?mod=invite', 'Отказаться', 1);
			}
		fin();
		break;
endswitch;
?>
