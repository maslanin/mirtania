<?php
##############
# 24.12.2014 #
##############
require_once('inc/top.php');
require_once('inc/check.php');
require_once('inc/head.php');
require_once('inc/hpstring.php');
$_SESSION['lasthod'] = '';

if(empty($f['loc']))
	{
	// перенос на локу с горным озером
	$f['loc'] = 1;
	$q = $db->query("update `users` set loc=1 where id='{$f['id']}' limit 1;");
	}
if($f['status'] == 1)
	{
	knopka('battle.php', 'Вы в бою!', 1);
	fin();
	}
if($f['status'] == 2)
	{
	knopka('arena.php', 'У вас заявка на арене!', 1);
	fin();
	}
// определение переменных в обход register globals
$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';
$go = isset($_REQUEST['go']) ? $_REQUEST['go'] : 0;
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : 1;
$hint = isset($_REQUEST['hint']) ? $_REQUEST['hint'] : 0;
if(!isset($_SESSION['hint'])) $_SESSION['hint'] = 'no';

if($f['rabota'] > $t) msg2('Вы работаете еще '.ceil(($f['rabota'] - $t)/60).' мин.');
if($f['rabota'] > 0 and $f['rabota'] < $t) knopka('rabota.php', 'Вы отработали свое время', 1);

$q = $db->query("select * from `loc` where id={$f['loc']} limit 1;");
$loc = $q->fetch_assoc();
$x = $loc['X'];
$y = $loc['Y'];
if(!empty($_REQUEST['sever']) and !empty($loc['N']) and $hint == $_SESSION['hint']) {$y++; $_SESSION['lasthod'] = 'sever';}
if(!empty($_REQUEST['jug']) and !empty($loc['S']) and $hint == $_SESSION['hint']) {$y--; $_SESSION['lasthod'] = 'jug';}
if(!empty($_REQUEST['zapad']) and !empty($loc['W']) and $hint == $_SESSION['hint']) {$x--; $_SESSION['lasthod'] = 'zapad';}
if(!empty($_REQUEST['vostok']) and !empty($loc['E']) and $hint == $_SESSION['hint']) {$x++; $_SESSION['lasthod'] = 'vostok';}
if($y != $loc['Y'] or $x != $loc['X'])
	{
	$q = $db->query("select * from `loc` where X='{$x}' and Y='{$y}' and map_id={$loc['map_id']} limit 1;");
	$loc = $q->fetch_assoc();
	$f['loc'] = $loc['id'];
	$q = $db->query("update `users` set loc={$f['loc']},rabota=0,kvest_step=0 where id='{$f['id']}' limit 1;");
	$f['kvest_step'] = 0;
	}
if(!empty($f['kvest_step'])) knopka('kvest.php?qv_id='.$f['kvest_now'], 'Продолжить задание', 1);
if(!empty($stats_free)) knopka('anketa.php?mod=stats', 'Вам необходимо <span style="color:red">распределить статы</span>');
if($mod == 'get' or !empty($_REQUEST['get']))
	{
	msg('Вы открыли портал! Осталось выбрать куда прыгнем:');
	if($f['loc'] != $f['lastportal'] and !empty($f['lastportal'])) knopka('loc.php?mod=portal&num=4', 'Последний Портал',1);
	if($f['loc'] != 1)  knopka('loc.php?mod=portal&num=1', 'Старый Лагерь',1);
	if($f['loc'] != 37) knopka('loc.php?mod=portal&num=2', 'Новый Лагерь',1);
	if($f['loc'] != 91) knopka('loc.php?mod=portal&num=3', 'Болотный Лагерь',1);
	if(!empty($f['klan']))
		{
		$q = $db->query("select loc,point from `klans` where name='{$f['klan']}' limit 1;");
		if($q->num_rows == 1) knopka('loc.php?mod=portal&num=5', 'Клановый замок',1);
		}
	fin();
	}
if($mod == 'portal')
	{
	$lastportal = $f['loc'];
	if($f['mananow'] < 1) msg2('Недостаточно маны!',1);
	if($num == 1 and $f['loc'] != 1)
		{
		$f['mananow'] -= 1;
		$f['loc'] = 1;
		$q = $db->query("select * from `loc` where id='{$f['loc']}' limit 1;");
		if($q->num_rows == 0) msg2('Перемещение в это место невозможно!',1);
		$loc = $q->fetch_assoc();
		$q = $db->query("update `users` set loc='{$f['loc']}', mananow='{$f['mananow']}',manatime='{$t}',lastportal='{$lastportal}',rabota=0,kvest_step=0 where id='{$f['id']}' limit 1;");
		msg2('Вы шагнули в портал и оказались в Старом лагере. Это стоило вам 1 маны.');
		}
	elseif($num == 2 and $f['loc'] != 37)
		{
		$f['mananow'] -= 1;
		$f['loc'] = 37;
		$q = $db->query("select * from `loc` where id='{$f['loc']}' limit 1;");
		if($q->num_rows == 0) msg2('Перемещение в это место невозможно!',1);
		$loc = $q->fetch_assoc();
		$q = $db->query("update `users` set loc='{$f['loc']}', mananow='{$f['mananow']}',manatime='{$t}',lastportal='{$lastportal}',rabota=0,kvest_step=0 where id='{$f['id']}' limit 1;");
		msg2('Вы шагнули в портал и оказались в Новом лагере. Это стоило вам 1 маны.');
		}
	elseif($num == 3 and $f['loc'] != 91)
		{
		$f['mananow'] -= 1;
		$f['loc'] = 91;
		$q = $db->query("select * from `loc` where id='{$f['loc']}' limit 1;");
		if($q->num_rows == 0) msg2('Перемещение в это место невозможно!',1);
		$loc = $q->fetch_assoc();
		$q = $db->query("update `users` set loc='{$f['loc']}', mananow='{$f['mananow']}',manatime='{$t}',lastportal='{$lastportal}',rabota=0,kvest_step=0 where id='{$f['id']}' limit 1;");
		msg2('Вы шагнули в портал и оказались в Болотном лагере. Это стоило вам 1 маны.');
		}
	elseif($num == 4 and $f['loc'] != $f['lastportal'] and !empty($f['lastportal']))
		{
		$f['mananow'] -= 1;
		$f['loc'] = $f['lastportal'];
		$q = $db->query("select * from `loc` where id='{$f['loc']}' limit 1;");
		if($q->num_rows == 0) msg2('Перемещение в это место невозможно!',1);
		$loc = $q->fetch_assoc();
		$q = $db->query("update `users` set loc='{$f['loc']}', mananow='{$f['mananow']}',manatime='{$t}',lastportal='{$lastportal}',rabota=0,kvest_step=0 where id='{$f['id']}' limit 1;");
		msg2('Вы шагнули в портал и оказались в другом месте. Это стоило вам 1 маны.');
		}
	elseif($num == 5)
		{
		if(empty($f['klan'])) msg2('Вы не в клане',1);
		$q = $db->query("select loc,point from `klans` where name='{$f['klan']}' limit 1;");
		if($q->num_rows == 1)
			{
			$a = $q->fetch_assoc();
			if(empty($a['loc']) and empty($a['point'])) msg2('У вашего клана нет замка!', 1);
			$f['mananow'] -= 1;
			if(!empty($a['loc'])) $f['loc'] = $a['loc']; else $f['loc'] = $a['point'];
			$q = $db->query("select * from `loc` where id='{$f['loc']}' limit 1;");
			if($q->num_rows == 0) msg2('Перемещение в это место невозможно!',1);
			$loc = $q->fetch_assoc();
			$q = $db->query("update `users` set loc='{$f['loc']}', mananow='{$f['mananow']}',manatime='{$t}',lastportal='{$lastportal}',rabota=0 where id='{$f['id']}' limit 1;");
			msg2('Вы шагнули в портал и оказались у своего клан замка. Это стоило вам 1 маны.');
			}
		}
	}
echo '<div class="board">';

$rd = mt_rand(999,99999);
$hint2 = md5($rd);
if($f['grafika'] == 1 or $f['grafika'] == 3) echo '<img src="locimg.php?r='.mt_rand(1000000,99999999).'" width="120" height="120" style="border: 1px outset black;"/><br/>';

if(!empty($loc['info'])) echo '<small>'.$loc['info'].'</small><br/><br/>';
echo '<form action="loc.php" method="post">';
echo '<input type="hidden" name="hint" value="'.$hint2.'">';
$_SESSION['hint'] = $hint2;
if($f['strelki'] == 1)
	{
	if(!empty($loc['N'])) echo '<input type="submit" value="Север" name="sever" style="width:50%"><br/>';
	if(!empty($loc['S'])) echo '<input type="submit" value="Юг" name="jug" style="width:50%"><br/>';
	if(!empty($loc['W'])) echo '<input type="submit" value="Запад" name="zapad" style="width:50%"><br/>';
	if(!empty($loc['E'])) echo '<input type="submit" value="Восток" name="vostok" style="width:50%"><br/>';
	}
elseif($f['strelki'] == 2)
	{
	echo '<input type="submit" value="Север" name="sever"';
	if(empty($loc['N'])) echo ' disabled="disabled" style="color:gray"';
	echo '/><br/>';
	echo '<input type="submit" value="Запад" name="zapad"';
	if(empty($loc['W'])) echo ' disabled="disabled" style="color:gray"';
	echo '/>';
	echo '<input type="submit" value="Восток" name="vostok"';
	if(empty($loc['E'])) echo ' disabled="disabled" style="color:gray"';
	echo '/><br/>';
	echo '<input type="submit" value=" Юг " name="jug"';
	if(empty($loc['S'])) echo ' disabled="disabled" style="color:gray"';
	echo '/>';
	}
else
	{
	echo '<input type="submit" value="&#8593;" name="sever"';
	if(empty($loc['N'])) echo ' disabled="disabled" style="color:gray"';
	echo '/><br/>';
	echo '<input type="submit" value="&#8592;" name="zapad"';
	if(empty($loc['W'])) echo ' disabled="disabled" style="color:gray"';
	echo '/>';
	echo '<input type="submit" style="background:#c3a86b;" value="Т" name="get"/>';
	echo '<input type="submit" value="&#8594;" name="vostok"';
	if(empty($loc['E'])) echo ' disabled="disabled" style="color:gray"';
	echo '/><br/>';
	echo '<input type="submit" value="&#8595;" name="jug"';
	if(empty($loc['S'])) echo ' disabled="disabled" style="color:gray"';
	echo '/>';
	}
echo '</form><br/>';
require_once('inc/locs.php');
echo '</div>';
if($f['strelki'] == 1 or $f['strelki'] == 2) knopka('loc.php?mod=get', 'Портал', 1);
//knopka('infa.php?mod=who', 'Кто рядом?', 1);
$timer1 = $t - 900;
$q = $db->query("select login,lvl,sex,status,klan from `users` where loc='{$f['loc']}' AND login<>'{$f['login']}' AND lastdate>'{$timer1}' order by lvl;");
if ($q->num_rows == 0) msg2('Рядом никого нет');
else msg2('Рядом с вами:');
while ($array_onl = $q->fetch_assoc())
	{
	if ($array_onl['sex'] == 1) $color_login = $male;
	else $color_login = $female;
	$str = '';
	$str .= '<span style="color:'.$color_login.'">'.$array_onl['login'].' ['.$array_onl['lvl'].']</span>';
	if (!empty($array_onl['klan'])) $str .= ' ('.$array_onl['klan'].')';
	if ($array_onl['status'] == 1) $str .= ' [Б]';
	knopka('infa.php?mod=uzinfa&lgn='.$array_onl['login'], $str);
	}
if($f['admin'] >= 3) msg('loc: '.$f['loc']);
fin();
?>
