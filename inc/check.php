<?php
##############
# 29.07.2014 #
##############

$admin = $settings['admin'];  //логин админа

if (!empty($_COOKIE['id']) and !empty($_COOKIE['hash']) and !empty($_COOKIE['lgn']))
	{
	$id = $_COOKIE['id'];
	$hash = $_COOKIE['hash'];
	$login = $_COOKIE['lgn'];
	}
else
	{
	unset($_SESSION);
	session_destroy();
	setcookie('id', '', $_SERVER['REQUEST_TIME'] - 3600);
	setcookie('hash', '', $_SERVER['REQUEST_TIME'] - 3600);
	setcookie('lgn', '', $_SERVER['REQUEST_TIME'] - 3600);
	header('location: index.php');
	fin();
	}
$id = intval($id);
$login = ekr($login);
$q = $db->query("select * from `users` where id={$id} and login='{$login}' limit 1;");
$f = $q->fetch_assoc();
if ($q->num_rows == 0 or $f['pass'] != $hash)
	{
	unset($_SESSION);
	session_destroy();
	setcookie('id', '', $_SERVER['REQUEST_TIME'] - 3600);
	setcookie('hash', '', $_SERVER['REQUEST_TIME'] - 3600);
	setcookie('lgn', '', $_SERVER['REQUEST_TIME'] - 3600);
	header("location: index.php");
	fin();
	}
else $_SESSION['auth'] = 1;

//начало проверок на совпадение IP/SOFT
$ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? ekr($_SERVER['HTTP_X_FORWARDED_FOR']) : ekr($_SERVER['REMOTE_ADDR']);
$soft = isset($_SERVER['HTTP_USER_AGENT']) ? ekr($_SERVER['HTTP_USER_AGENT']) : '';
$host = isset($_SERVER['HTTP_X_OPERAMINI_PHONE']) ? ekr($_SERVER['HTTP_X_OPERAMINI_PHONE']) : '';

if ($f['soft'] != $soft or $f['host'] != $host or $f['ip'] != $ip)
	{
	unset($_SESSION);
	session_destroy();
	session_start();
	$_SESSION['auth'] = 1;
	setcookie('id', '', $_SERVER['REQUEST_TIME'] - 3600);
	setcookie('hash', '', $_SERVER['REQUEST_TIME'] - 3600);
	setcookie('lgn', '', $_SERVER['REQUEST_TIME'] - 3600);
	setcookie('id', $f['id'], $_SERVER['REQUEST_TIME'] + 3600 * 24 * 365);
	setcookie('hash', $f['pass'], $_SERVER['REQUEST_TIME'] + 3600 * 24 * 365);
	setcookie('lgn', $f['login'], $_SERVER['REQUEST_TIME'] + 3600 * 24 * 365);
	$q = $db->query("insert into `ipsoft` values(0,'{$f['login']}','{$t}','{$ip}','{$host}','{$soft}','".session_id()."');");
	$q = $db->query("update `users` set soft='{$soft}',host='{$host}',ip='{$ip}',session='".session_id()."' where id={$f['id']} limit 1;");
	}
//конец проверки совпадения IP/SOFT

/* if($f['code'] != 'no')
  {
  $_SESSION['auth'] = 0;
  require_once("inc/head.php");
  echo '<div class="board">';
  echo 'Добро пожаловать, '.$f['login'].'!<br/>';
  echo 'Вы успешно зарегистрировались.<br/>';
  echo 'Для продолжения необходимо подтвердить регистрацию путем ввода кода, полученого на ваш email.<br/>';
  echo 'Ваш email: '.$f['email'].' <a href="code.php?mod=change&lgn='.$f['login'].'">[изм]</a><br/>';
  echo '<a href="code.php?mod=newcode&lgn='.$f['email'].'">Повторная отправка кода</a><br/><br/>';
  echo '<form action="code.php?lgn='.$f['login'].'" method="POST">';
  echo 'Введите код, присланный вам на email:<br/>';
  echo '<input type="text" name="code"/><br/>';
  echo '<input type="submit" value="Далее"/></form>';
  echo '</div>';
  fin();
  } */
// удаление авторегов
$timer = $t - 86400;
$a = $db->query("select login from `users` where autoreg=1 and regdate<'{$timer}';");
while ($del = $a->fetch_assoc())
	{
	$q = $db->query("delete from `chat` WHERE login='{$del['login']}';");
	$q = $db->query("delete from `chat` WHERE privat='{$del['login']}';");
	$q = $db->query("delete from `combat` WHERE login='{$del['login']}';");
	$q = $db->query("delete from `forum_comm` WHERE login='{$del['login']}';");
	$q = $db->query("delete from `forum_topic` WHERE login='{$del['login']}';");
	$q = $db->query("delete from `invent` WHERE login='{$del['login']}';");
	//$q = $db->query("delete from `invent` WHERE arenda_login='{$del['login']}';");
	$q = $db->query("delete from `ipsoft` WHERE login='{$del['login']}';");
	$q = $db->query("delete from `letters` WHERE login='{$del['login']}';");
	$q = $db->query("delete from `letters` WHERE login_from='{$del['login']}';");
	$q = $db->query("delete from `log_peredach` WHERE login='{$del['login']}';");
	$q = $db->query("delete from `log_peredach` WHERE login_per='{$del['login']}';");
	$q = $db->query("delete from `magic` WHERE login='{$del['login']}';");
	$q = $db->query("delete from `users` WHERE login='{$del['login']}' limit 1;");
	}
if ($f['ban'] != 0 && $f['ban'] < $_SERVER['REQUEST_TIME'] && $f['flag_blok'] == 1)
	{
	$f['ban'] = 0;
	$f['flag_blok'] = 0;
	$f['zachto_blok'] = '';
	$q = $db->query("update `users` set ban=0,flag_blok=0,zachto_blok='' where id='{$f['id']}' limit 1;");
	}

if ($f['ban'] != 0 && $f['ban'] < $_SERVER['REQUEST_TIME'] && $f['flag_blok'] == 0)
	{
	$f['ban'] = 0;
	$f['zachto_blok'] = '';
	$q = $db->query("update `users` set ban=0,zachto_blok='' where id='{$f['id']}' limit 1;");
	}

if ($f['flag_blok'] == 1)
	{
	echo 'Ваш персонаж заблокирован.<br/>';
	echo 'Причина: '.$f['zachto_blok'].'<br/>';
	if ($f['ban'] > 0) echo 'Блок до '.Date('d.m.Y H:i', $f['ban']);
	fin();
	}
//восстановление хп
$hp_plus = $f['hpmax'] * 0.07; //коэффициент фосстановления ХП
if (!empty($f['klan'])) $hp_plus = $f['hpmax'] * 0.09;
$hp_plus = ceil($hp_plus);
if ($f['status'] != 1)
	{
	$hp_plus1 = ceil($hp_plus * floor(($_SERVER['REQUEST_TIME'] - $f['hptime']) / 60));
	$plus = $f['hpnow'] + $hp_plus1;
	if ($plus > $f['hpmax']) $plus = $f['hpmax'];
	if ($plus > $f['hpnow'])
		{
		$f['hpnow'] = $plus;
		$q = $db->query("update `users` set hpnow={$plus}, hptime='{$t}' where id={$f['id']} limit 1;");
		}
	}

//восстановление мп
$mp_plus = $f['manamax'] * 0.07; //коэффициент фосстановления МП
if (!empty($f['klan'])) $mp_plus = $f['manamax'] * 0.09;
$mp_plus = ceil($mp_plus);
if ($f['status'] != 1)
	{
	$mp_plus1 = ceil($mp_plus * floor(($_SERVER['REQUEST_TIME'] - $f['manatime']) / 60));
	$plus = $f['mananow'] + $mp_plus1;
	if ($plus > $f['manamax']) $plus = $f['manamax'];
	if ($plus > $f['mananow'])
		{
		$f['mananow'] = $plus;
		$q = $db->query("update `users` set mananow={$plus}, manatime='{$t}' where id={$f['id']} limit 1;");
		}
	}

//можно было обрезание ХП сделать сразу в восстановлении ХП, но с введением вампиризма и лечения в бою ХП становились больше ХПМАХ
if ($f['hpnow'] > $f['hpmax'])
	{
	$f['hpnow'] = $f['hpmax'];
	$q = $db->query("update `users` set hpnow='{$f['hpnow']}', hptime='{$t}' where id={$f['id']} limit 1;");
	}

if ($f['mananow'] > $f['manamax'])
	{
	$f['mananow'] = $f['manamax'];
	$q = $db->query("update `users` set mananow={$f['mananow']}, manatime='{$t}' where id={$f['id']} limit 1;");
	}
if ($f['hpnow'] < 0)
	{
	$f['hpnow'] = 0;
	$q = $db->query("update `users` set hpnow=0, hptime='{$t}' where id={$f['id']} limit 1;");
	}

if ($f['mananow'] < 0)
	{
	$f['mananow'] = 0;
	$q = $db->query("update `users` set mananow=0, manatime='{$t}' where id={$f['id']} limit 1;");
	}
	
//взятие уровня
require_once('inc/exp.php');
if ($tolev < 1 and $f['status'] != 1)
	{
	$f['lvl'] += 1;
	$f['exp'] = (-1) * $tolev;
	if (!empty($f['klan'])) klan_points($f['klan'], $f['lvl']);
	$q = $db->query("update `users` set lvl={$f['lvl']}, exp='{$f['exp']}' where id={$f['id']} limit 1;");
	$f = calcparam($f);
	if($f['lvl'] == 2) msg2('Поздравляем! Вы только что получили новый уровень. Теперь вам нужно переместиться в один из лагерей по ссылке "Портал" внизу страницы, и продать все свои старые вещи в магазин. Снять старые вещи можно в меню персонажа по ссылке "Снаряжение". Так же вы должны будете купить вещи на свой новый уровень. Загляните на рынок, поищите там. Обычно, цены там ниже магазинных. Приятной игры.');
	else msg2('Поздравляем, вы получили '.$f['lvl'].' уровень!');
	}
// проверка на кланы, отдаем главенство первому зашедшему, если главы нет более 7 дней
if(!empty($f['klan']) and $f['klan_status'] < 3)
	{
	$q = $db->query("select login, lastdate from `users` where klan='{$f['klan']}' and klan_status=3;");
	$a = $q->fetch_assoc();
	if($a['lastdate'] + 60 * 60 * 24 * 7 < $t)
		{
		$q = $db->query("update `users` set klan_status=2 where login='{$a['login']}' limit 1;");
		$q = $db->query("update `users` set klan_status=3 where id='{$f['id']}' limit 1;");
		msg2('Поздравляем, вам доверено управление вашим кланом в связи с отсутствием главы более недели.');
		}
	}
// обновим время онлайна
if ($f['lastdate'] < $t - 60) $q = $db->query("update `users` set lastdate='{$t}' where id='{$f['id']}' limit 1;");
require_once('inc/bonus.php');
$q = $db->query("select count(*) from `letters` where login='{$f['login']}' and read_flag=0;");
$a = $q->fetch_assoc();
$count_pm = $a['count(*)'];
?>
