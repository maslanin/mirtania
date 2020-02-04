<?php
##############
# 24.12.2014 #
##############

echo '<div class="head">';
echo '<center><b>'.date('H:i:s').'</b>';
if(!empty($_SESSION['auth']))
	{
	require_once('inc/check.php');
	echo '<span style="float:left;"><a href="anketa.php">';
	if($f['sex'] == 1) echo '<img src="pic/m.png" value="*"/></a>'; else echo '<img src="pic/f.png" value="*"/></a>';
	if(!empty($count_pm) and $_SERVER['PHP_SELF'] != '/pm.php') echo ' <a href="pm.php"><img src="pic/newletter.gif" alt="'.$count_pm.' новых писем"/></a>';
	if($f['status'] == 1 and $_SERVER['PHP_SELF'] != '/battle.php') echo ' <a href="battle.php"><img src="pic/boi.png" value="*"/></a>';
	echo '</span><span style="float:right;">
	<a href="'.$_SERVER['PHP_SELF'].'"><img src="pic/reload.png" value="*"/></a> 
	<a href="menu.php"><img src="pic/menu.png" value="*"/></a> 
	<a href="loc.php"><img src="pic/ingame.png" value="*"/></a></span>';
	echo '</center></div>';
	if(!empty($f['newsdate']) and $_SERVER['PHP_SELF'] != '/news.php') knopka('news.php', '<span style="color:'.$logincolor.'">Свежие новости!</span>');
	if(!empty($f['autoreg']) and !substr_count($_SERVER['PHP_SELF'], '/start.php')) knopka('start.php?reg', '<b>Сохранить персонажа</b>');
	}
else
	{
	if($_SERVER['PHP_SELF'] != '/index.php') echo '<span style="float:right;"><a href="index.php">[Главная]</a>';
	echo '</center></div>';
	}

if(!isset($f['login'])) $f['login'] = '';
if(!isset($f['admin'])) $f['admin'] = 0;
if(!empty($settings['mess']) and !empty($f['login']) and $admin != $f['login']) msg2($settings['mess'],1);
?>
