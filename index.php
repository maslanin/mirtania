<?php
##############
# 24.12.2014 #
##############

require_once('inc/top.php'); // оформление
$_SESSION['auth'] = 0; //если 1, то не выводим форму авторизации
//сколько всего пользователей зарегистрировано
$res = $db->query("select count(*) from `users` where autoreg=0;");
$q = $res->fetch_assoc();
$countreg = $q['count(*)'];

if (!empty($_COOKIE['id']) and !empty($_COOKIE['hash']) and !empty($_COOKIE['lgn']))
	{
	$id = $_COOKIE['id'];
	$hash = $_COOKIE['hash'];
	$login = $_COOKIE['lgn'];
	$id = intval($id);
	$login = ekr($login);
	$res = $db->query("select login,pass from `users` where id={$id} and login='{$login}' limit 1;");
	$auth = $res->fetch_assoc();
	if (empty($auth['pass'])) $auth['pass'] = '';
	if ($auth['pass'] == $hash)
		{
		$_SESSION['auth'] = 1;
		}
	else
		{
		unset($_SESSION);
		session_destroy();
		setcookie('id', '', $_SERVER['REQUEST_TIME'] - 3600);
		setcookie('hash', '', $_SERVER['REQUEST_TIME'] - 3600);
		setcookie('lgn', '', $_SERVER['REQUEST_TIME'] - 3600);
		}
	}
if (!empty($_SESSION['auth'])) require_once('inc/check.php');
echo '<div class="verx"><img src="pic/logo.png" alt=""/></div>';
if (!empty($_SESSION['auth']))
	{
	header("location: loc.php");
	fin();
	}
require_once("inc/head.php");
msg2('Это многопользовательская игра, в которой одновременно могут участвовать несколько тысяч персонажей, контролируемых людьми. 
Средневековый, сказочный мир, наполненный чудесами и опасностями, монстрами и героями откроется для Вас.');
echo '<div class="board">';
echo '<form action="start.php?auth" method="POST">
<input type="text" name="login" placeholder="Логин"/><br/>
<input type="password" name="pass" placeholder="Пароль"/><br/>
<input type="submit" value="Войти"/></form>
</div>';
if ($settings['reg'] == 1)
	{
	knopka('start.php?start', 'Начать новую игру');
	}
knopka('pass.php', 'Восстановление пароля');
knopka('news.php', DateNews());
knopka('lib.php', 'Библиотека');
echo '<div class="board2"><small>Зарегистрировано '.$countreg.' игроков</small></div>';
fin();
?>
