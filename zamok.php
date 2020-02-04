<?php
##############
# 24.12.2014 #
##############
require_once('inc/top.php');		// вывод на экран
require_once('inc/check.php');	// вход в игру
require_once('inc/head.php');
require_once('class/items.php');
require_once('inc/hpstring.php');
$q = $db->query("select * from `klans` where loc={$f['loc']} or point={$f['loc']} limit 1;");
if($q->num_rows == 0)
	{
	knopka('loc.php', 'Замка здесь нет', 1);
	fin();
	}
$klan = $q->fetch_assoc();
// объявим переменные чтоб не зависеть от register globals
$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';
$go = isset($_REQUEST['go']) ? $_REQUEST['go'] : 0;
$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$iid = isset($_REQUEST['iid']) ? intval($_REQUEST['iid']) : 0;

if(!empty($klan['point']) and empty($klan['loc']))
	{
	msg2('Недостроенный замок '.$klan['name']);
	switch($mod):
	default:
		if($f['klan'] != $klan['name']) msg2('Этот замок необходимо отстроить, а пока лишь ветер гуляет среди руин...',1);
		msg2('У вас '.$klan['kamni'].' камней.');
		knopka('zamok.php?mod=build', 'Отстроить замок (100 камней)', 1);
		if(3 <= $f['klan_status']) knopka('zamok.php?mod=drop', 'Отказаться от замка', 1);
		fin();
	break;

	case 'drop':
		if($f['klan_status'] < 3) msg('Недоступно для вас', 1);
		if(empty($go))
			{
			msg2('Вы уверены, что хотите освободить это место?');
			knopka('zamok.php?mod=drop&go=1', 'Отказаться от места', 1);
			knopka('loc.php', 'В игру', 1);
			fin();
			}
		$log = $f['login'].' ['.$f['lvl'].'] освобождает занятое для замка место.';
		$q = $db->query("insert into `klan_log` values(0,'{$f['login']}','{$log}','{$f['klan']}','{$t}');");
		$q = $db->query("update `klans` set point=0 where name='{$f['klan']}' limit 1;");
		msg2('Вы успешно отказались от места, теперь здесь может строиться любой клан.', 1);
	break;

	case 'build':
		if($klan['kamni'] < 100) msg('Недостаточно камней.', 1);
		if(empty($go))
			{
			msg2('Вы уверены, что хотите построить замок?');
			knopka('zamok.php?mod=build&go=1', 'Построить замок', 1);
			knopka('loc.php', 'В игру', 1);
			fin();
			}
		$log = $f['login'].' ['.$f['lvl'].'] строит замок для клана из 100 камней.';
		$q = $db->query("insert into `klan_log` values(0,'{$f['login']}','{$log}','{$f['klan']}','{$t}');");
		$q = $db->query("update `klans` set point=0, loc={$f['loc']},kamni=kamni-100 where name='{$f['klan']}' limit 1;");
		msg2('Вы успешно построили замок.');
		knopka('zamok.php', 'В замок', 1);
		knopka('loc.php', 'В игру', 1);
		fin();
	break;
	endswitch;
	fin();
	}

// этот код выполнится, только если тут будет замок.
msg2('Замок клана '.$klan['name']);

switch($mod):
default:
	knopka('zamok.php?mod=info', 'Информация', 1);
	if($klan['name'] == $f['klan'] and !empty($klan['altar'])) knopka('zamok.php?mod=altar', 'Алтарь', 1);
	if($klan['name'] == $f['klan'] and !empty($klan['pivo'])) knopka('zamok.php?mod=pivo', 'Пивоварня', 1);
	if($klan['name'] == $f['klan'] and !empty($klan['laba'])) knopka('zamok.php?mod=laba', 'Лаборатория', 1);
	if($klan['name'] == $f['klan'] and !empty($klan['kuznica'])) knopka('zamok.php?mod=kuznica', 'Кузница', 1);
	if($klan['name'] == $f['klan'] and !empty($klan['oruzh'])) knopka('zamok.php?mod=oruzh', 'Оружейная мастерская', 1);
	if($klan['name'] == $f['klan'] and 2 <= $f['klan_status']) knopka('zamok.php?mod=buildings', 'Управление постройками', 1);
break;

case 'info':
	echo '<div class="board"">';
	echo 'Сводка';
	echo '</div>';
	echo '<div class="board2" style="text-align:left">Казна: '.$klan['kazna'].'</div>';
	echo '<div class="board2" style="text-align:left">Камни: '.$klan['kamni'].'</div>';
	if($klan['altar'] > 0) echo '<div class="board2" style="text-align:left">Уровень алтаря: '.$klan['altar'].'</div>';
	if($klan['pivo'] > 0) echo '<div class="board2" style="text-align:left">Уровень пивоварни: '.$klan['pivo'].'</div>';
	if($klan['laba'] > 0) echo '<div class="board2" style="text-align:left">Уровень лаборатории: '.$klan['laba'].'</div>';
	if($klan['kuznica'] > 0) echo '<div class="board2" style="text-align:left">Уровень кузницы: '.$klan['kuznica'].'</div>';
	if($klan['oruzh'] > 0) echo '<div class="board2" style="text-align:left">Уровень оружейной мастерской: '.$klan['oruzh'].'</div>';
	if($klan['name'] == $f['klan']) knopka('zamok.php?mod=log','Дворовая книга', 1);
break;

case 'buildings':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
	if($f['klan_status'] < 2) msg('Вам нечего тут делать, доступно главам и наместникам!', 1);
	if($klan['altar'] == 0) $str = 'Построить алтарь'; else $str = 'Улучшить алтарь';
	if($klan['altar'] < 5) knopka('zamok.php?mod=upaltar', $str);
	if($klan['altar'] == 0) $str = 'Построить пивоварню'; else $str = 'Улучшить пивоварню';
	if($klan['pivo'] < 5) knopka('zamok.php?mod=uppivo', $str);
	if($klan['altar'] == 0) $str = 'Построить лабораторию'; else $str = 'Улучшить лабораторию';
	if($klan['laba'] < 5) knopka('zamok.php?mod=uplaba', $str);
	if($klan['altar'] == 0) $str = 'Построить кузницу'; else $str = 'Улучшить кузницу';
	if($klan['kuznica'] < 5) knopka('zamok.php?mod=upkuznica', $str);
	if($klan['altar'] == 0) $str = 'Построить оружейную мастерскую'; else $str = 'Улучшить оружейную мастерскую';
	if($klan['oruzh'] < 3) knopka('zamok.php?mod=uporuzh', $str);
break;

case 'altar':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!',1);
	if(empty($klan['altar'])) msg2('В замке нет алтаря!',1);
	msg('Небольшой постамент, на котором расположен жертвенный камень. От него ощутимо веет мощью.');
	$timer = $t + 86400 * 7;
	$f['altar'] = $klan['altar'];
	$f['altar_time'] = $timer;
	$q = $db->query("update `users` set altar={$klan['altar']},altar_time='{$timer}' where id={$f['id']} limit 1;");
	$f = calcparam($f);
	msg2('Боги довольны вами, усиление +'.$klan['altar'].'% на 7 суток');
break;

case 'upaltar':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!',1);
	if($f['klan_status'] < 2) msg2('Вы не можете строить в замке.', 1);
	if(empty($klan['altar'])) { $money = 500; $kamni = 100;}
	elseif($klan['altar'] == 1) { $money = 1000; $kamni = 200;}
	elseif($klan['altar'] == 2) { $money = 2000; $kamni = 300;}
	elseif($klan['altar'] == 3) { $money = 5000; $kamni = 400;}
	elseif($klan['altar'] == 4) { $money = 10000; $kamni = 500;}
	else msg2('У вас алтарь максимального 5 уровня.',1);
	if(empty($go))
		{
		msg2('Вы действительно хотите построить или улучшить алтарь за '.$money.' монет и '.$kamni.' камней?');
		knopka('zamok.php?mod=upaltar&go=1', 'Продолжаем!', 1);
		knopka('zamok.php', 'Отказаться', 1);
		fin();
		}
	if($klan['kazna'] < $money) msg('В казне недостаточно денег для постройки алтаря, нужно '.$money, 1);
	if($klan['kamni'] < $kamni) msg('У клана недостаточно камней для постройки алтаря, нужно '.$kamni, 1);
	$klan['altar']++;
	$klan['kazna'] -= $money;
	$klan['kamni'] -= $kamni;
	if($klan['pivo'] == 1) $log = $f['login'].' ['.$f['lvl'].'] строит алтарь клана за '.$kamni.' камней и '.$money.' монет.';
	else $log = $f['login'].' ['.$f['lvl'].'] улучшает алтарь клана до '.$klan['altar'].' уровня за '.$kamni.' камней и '.$money.' монет.';
	$q = $db->query("insert into `klan_log` values(0,'{$f['login']}','{$log}','{$f['klan']}','{$t}');");
	$q = $db->query("update `klans` set altar={$klan['altar']},kazna={$klan['kazna']},kamni={$klan['kamni']} where id={$klan['id']} limit 1;");
	if($klan['altar'] == 1) msg2('Вы построили алтарь!');
	else msg2('Вы улучшили алтарь до '.$klan['altar'].' уровня!');
	knopka('zamok.php', 'В замок', 1);
break;

case 'pivo':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!',1);
	if(empty($klan['pivo'])) msg2('В замке нет пивоварни!',1);
	if(empty($go))
		{
		if(1 <= $klan['pivo']) knopka('zamok.php?mod=pivo&go=1','Брага (нужен хмель)',1);
		if(2 <= $klan['pivo']) knopka('zamok.php?mod=pivo&go=2','Пиво (нужен солод)',1);
		if(3 <= $klan['pivo']) knopka('zamok.php?mod=pivo&go=3','Вино (нужен виноград)',1);
		if(4 <= $klan['pivo']) knopka('zamok.php?mod=pivo&go=4','Самогон (нужен мёд)',1);
		if(5 <= $klan['pivo']) knopka('zamok.php?mod=pivo&go=5','Рисовый шнапс (нужен рис)',1);
		}
	elseif($go == 1)
		{
		$need_id = 640;
		$res = 635;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет хмеля!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили брагу.');
		}
	elseif($go == 2)
		{
		if($klan['pivo'] < 2) msg('Необходима пивоварня минимум 2 уровня!',1);
		$need_id = 641;
		$res = 636;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет солода!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили пиво.');
		}
	elseif($go == 3)
		{
		if($klan['pivo'] < 3) msg('Необходима пивоварня минимум 3 уровня!',1);
		$need_id = 642;
		$res = 637;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет винограда!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили вино.');
		}
	elseif($go == 4)
		{
		if($klan['pivo'] < 4) msg('Необходима пивоварня минимум 4 уровня!',1);
		$need_id = 643;
		$res = 638;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет мёда!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили самогон.');
		}
	elseif($go == 5)
		{
		if($klan['pivo'] < 5) msg('Необходима пивоварня минимум 5 уровня!',1);
		$need_id = 644;
		$res = 639;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет риса!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили рисовый шнапс.');
		}
	else
		{
		$m = $f['lvl'] * 1000;
		$q = $db->query("update `users` set money=money-'{$m}' where id={$f['id']} limit 1;");
		msg2('Подменять ссылки нехорошо. С вас списан штраф '.$m.' монет за баловство.');
		}
break;

case 'uppivo':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!',1);
	if($f['klan_status'] < 2) msg2('Вы не можете строить в замке.', 1);
	if(empty($klan['pivo'])) { $money = 500; $kamni = 100;}
	elseif($klan['pivo'] == 1) { $money = 1000; $kamni = 250;}
	elseif($klan['pivo'] == 2) { $money = 2000; $kamni = 500;}
	elseif($klan['pivo'] == 3) { $money = 5000; $kamni = 750;}
	elseif($klan['pivo'] == 4) { $money = 10000; $kamni = 1000;}
	else msg2('У вас пивоварня максимального 5 уровня.',1);
	if(empty($go))
		{
		msg2('Вы действительно хотите построить или улучшить пивоварню за '.$money.' монет и '.$kamni.' камней?');
		knopka('zamok.php?mod=uppivo&go=1', 'Продолжаем!', 1);
		knopka('zamok.php', 'Отказаться', 1);
		fin();
		}
	if($klan['kazna'] < $money) msg('В казне недостаточно денег для постройки или улучшения пивоварни, нужно '.$money, 1);
	if($klan['kamni'] < $kamni) msg('У клана недостаточно камней для постройки или улучшения пивоварни, нужно '.$kamni, 1);
	$klan['pivo']++;
	$klan['kazna'] -= $money;
	$klan['kamni'] -= $kamni;
	if($klan['pivo'] == 1) $log = $f['login'].' ['.$f['lvl'].'] строит пивоварню клана за '.$kamni.' камней и '.$money.' монет.';
	else $log = $f['login'].' ['.$f['lvl'].'] улучшает пивоварню клана до '.$klan['pivo'].' уровня за '.$kamni.' камней и '.$money.' монет.';
	$q = $db->query("insert into `klan_log` values(0,'{$f['login']}','{$log}','{$f['klan']}','{$t}');");
	$q = $db->query("update `klans` set pivo={$klan['pivo']},kazna={$klan['kazna']},kamni={$klan['kamni']} where id={$klan['id']} limit 1;");
	if($klan['pivo'] == 1) msg2('Вы построили пивоварню!');
	else msg2('Вы улучшили пивоварню до '.$klan['pivo'].' уровня!');
	knopka('zamok.php', 'В замок', 1);
break;

case 'laba':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!',1);
	if(empty($klan['laba'])) msg2('В замке нет лаборатории!',1);
	if(empty($go))
		{
		msg('Для приготовления напитков лечения необходима целебная трава');
		if(1 <= $klan['laba']) knopka('zamok.php?mod=laba&go=1','Великая эссенця исцеления (+350HP)',1);
		if(2 <= $klan['laba']) knopka('zamok.php?mod=laba&go=2','Великая вытяжка исцеления (+500HP)',1);
		if(3 <= $klan['laba']) knopka('zamok.php?mod=laba&go=3','Великий эликсир лечения (+750HP)',1);
		if(4 <= $klan['laba']) knopka('zamok.php?mod=laba&go=4','Великий напиток лечения (+1000HP)',1);
		if(5 <= $klan['laba']) knopka('zamok.php?mod=laba&go=5','Лечебный экстракт (+1500HP)',1);
		msg('Для приготовления напитков маны необходим корень маны');
		if(1 <= $klan['laba']) knopka('zamok.php?mod=laba&go=6','Великая эссенция мудрости (+350MP)',1);
		if(2 <= $klan['laba']) knopka('zamok.php?mod=laba&go=7','Великая вытяжка мудрости (+500MP)',1);
		if(3 <= $klan['laba']) knopka('zamok.php?mod=laba&go=8','Великий эликсир мудрости (+750MP)',1);
		if(4 <= $klan['laba']) knopka('zamok.php?mod=laba&go=9','Великий напиток мудрости (+1000MP)',1);
		if(5 <= $klan['laba']) knopka('zamok.php?mod=laba&go=10','Экстракт мудрости (+1500MP)',1);
		}
	elseif($go == 1)
		{
		$need_id = 705;
		$res = 625;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет целебной травы!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили зелье.');
		}
	elseif($go == 2)
		{
		if($klan['laba'] < 2) msg('Необходима лаборатория минимум 2 уровня!',1);
		$need_id = 705;
		$res = 626;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет целебной травы!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили зелье.');
		}
	elseif($go == 3)
		{
		if($klan['laba'] < 3) msg('Необходима лаборатория минимум 3 уровня!',1);
		$need_id = 705;
		$res = 627;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет целебной травы!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили зелье.');
		}
	elseif($go == 4)
		{
		if($klan['laba'] < 4) msg('Необходима лаборатория минимум 4 уровня!',1);
		$need_id = 705;
		$res = 628;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет целебной травы!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили зелье.');
		}
	elseif($go == 5)
		{
		if($klan['laba'] < 5) msg('Необходима лаборатория минимум 5 уровня!',1);
		$need_id = 705;
		$res = 629;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет целебной травы!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили зелье.');
		}
	elseif($go == 6)
		{
		$need_id = 706;
		$res = 630;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет корня маны!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили зелье.');
		}
	elseif($go == 7)
		{
		if($klan['laba'] < 2) msg('Необходима лаборатория минимум 2 уровня!',1);
		$need_id = 706;
		$res = 631;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет корня маны!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили зелье.');
		}
	elseif($go == 8)
		{
		if($klan['laba'] < 3) msg('Необходима лаборатория минимум 3 уровня!',1);
		$need_id = 706;
		$res = 632;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет корня маны!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили зелье.');
		}
	elseif($go == 9)
		{
		if($klan['laba'] < 4) msg('Необходима лаборатория минимум 4 уровня!',1);
		$need_id = 706;
		$res = 633;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет корня маны!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили зелье.');
		}
	elseif($go == 10)
		{
		if($klan['laba'] < 5) msg('Необходима лаборатория минимум 5 уровня!',1);
		$need_id = 706;
		$res = 634;
		$need = $items->count_base_item($f['login'], $need_id);
		if($need == 0) msg2('У вас нет корня маны!',1);
		$items->del_base_item($f['login'], $need_id,1);
		$items->add_item($f['login'], $res, 1);
		msg2('Вы приготовили зелье.');
		}
	else
		{
		$m = $f['lvl'] * 1000;
		$q = $db->query("update `users` set money=money-'{$m}' where id={$f['id']} limit 1;");
		msg2('Подменять ссылки нехорошо. С вас списан штраф '.$m.' монет за баловство.');
		}
break;

case 'uplaba':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!',1);
	if($f['klan_status'] < 2) msg2('Вы не можете строить в замке.', 1);
	if(empty($klan['laba'])) { $money = 500; $kamni = 100;}
	elseif($klan['laba'] == 1) { $money = 1000; $kamni = 250;}
	elseif($klan['laba'] == 2) { $money = 2000; $kamni = 500;}
	elseif($klan['laba'] == 3) { $money = 5000; $kamni = 750;}
	elseif($klan['laba'] == 4) { $money = 10000; $kamni = 1000;}
	else msg2('У вас лаборатория максимального 5 уровня.',1);
	if(empty($go))
		{
		msg2('Вы действительно хотите построить или улучшить лабораторию за '.$money.' монет и '.$kamni.' камней?');
		knopka('zamok.php?mod=uplaba&go=1', 'Продолжаем!', 1);
		knopka('zamok.php', 'Отказаться', 1);
		fin();
		}
	if($klan['kazna'] < $money) msg('В казне недостаточно денег для постройки или улучшения лаборатории, нужно '.$money, 1);
	if($klan['kamni'] < $kamni) msg('У клана недостаточно камней для постройки или улучшения лаборатории, нужно '.$kamni, 1);
	$klan['laba']++;
	$klan['kazna'] -= $money;
	$klan['kamni'] -= $kamni;
	if($klan['laba'] == 1) $log = $f['login'].' ['.$f['lvl'].'] строит лабораторию клана за '.$kamni.' камней и '.$money.' монет.';
	else $log = $f['login'].' ['.$f['lvl'].'] улучшает лабораторию клана до '.$klan['laba'].' уровня за '.$kamni.' камней и '.$money.' монет.';
	$q = $db->query("insert into `klan_log` values(0,'{$f['login']}','{$log}','{$f['klan']}','{$t}');");
	$q = $db->query("update `klans` set laba={$klan['laba']},kazna={$klan['kazna']},kamni={$klan['kamni']} where id={$klan['id']} limit 1;");
	if($klan['laba'] == 1) msg2('Вы построили лабораторию!');
	else msg2('Вы улучшили лабораторию до '.$klan['laba'].' уровня!');
	knopka('zamok.php', 'В замок', 1);
break;

case 'kuznica':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!',1);
	if(empty($klan['kuznica'])) msg2('В замке нет кузницы!',1);
	$q = $db->query("select max(lvl) from `item`;");
	$a = $q->fetch_assoc();
	$max_lvl = $a['max(lvl)'];
	if($klan['kuznica'] == 1) $max_lvl = 9;
	if($klan['kuznica'] == 2) $max_lvl = 13;
	if($klan['kuznica'] == 3) $max_lvl = 17;
	if($klan['kuznica'] == 4) $max_lvl = 21;
	if (empty($start))
		{
		echo '<div class="board" style="text-align:left">';
		echo '<form action="zamok.php?mod=kuznica" method="POST">
		На какой уровень желаете собрать вещи?<br/>
		<select name="start">';
		for ($i = 6; $i <= $max_lvl; $i++) echo '<option value='.$i.'>'.$i.'</option>';
		echo '</select>';
		echo '<input type="submit" value="Далее"/></form>';
		fin();
		}
	if ($start < 6) $start = 6;
	if ($start > $max_lvl) $start = $max_lvl;
	if (empty($iid))
		{
		$it1 = $items->base_shmot(168); //тотем
		$it2 = $items->base_shmot(169); //брас
		$c = $start * 100;
		msg2('Вы хотите собрать вещи на '.$start.' уровень. Цена '.$c.' монет');
		knopka('zamok.php?mod=kuznica&start='.$start.'&iid=1', 'Амулет (с) (необходимо '.$it1['name'].')', 1);
		knopka('zamok.php?mod=kuznica&start='.$start.'&iid=2', 'Амулет (к) (необходимо '.$it1['name'].')', 1);
		knopka('zamok.php?mod=kuznica&start='.$start.'&iid=3', 'Амулет (у) (необходимо '.$it1['name'].')', 1);
		knopka('zamok.php?mod=kuznica&start='.$start.'&iid=4', 'Браслет (с) (необходимо '.$it2['name'].')', 1);
		knopka('zamok.php?mod=kuznica&start='.$start.'&iid=5', 'Браслет (к) (необходимо '.$it2['name'].')', 1);
		knopka('zamok.php?mod=kuznica&start='.$start.'&iid=6', 'Браслет (у) (необходимо '.$it2['name'].')', 1);
		fin();
		}
	$iid = intval($iid);
	if ($iid == 1) $num = $start * 3 - 17;
	elseif ($iid == 2) $num = $start * 3 - 16;
	elseif ($iid == 3) $num = $start * 3 - 15;
	elseif ($iid == 4) $num = $start * 3 + 43;
	elseif ($iid == 5) $num = $start * 3 + 44;
	elseif ($iid == 6) $num = $start * 3 + 45;
	else
		{
		$m = $f['lvl'] * 1000;
		$q = $db->query("update `users` set money=money-'{$m}' where id={$f['id']} limit 1;");
		msg2('Подменять ссылки нехорошо. С вас списан штраф '.$m.' монет за баловство.',1);
		}
	if ($f['money'] < ($start * 100)) msg2('У вас не хватает денег!', 1);
	$item = $items->base_shmot($num);
	if ($item['equip'] == 'braslet') $zzz = 169;
	if ($item['equip'] == 'amulet') $zzz = 168;
	$itz = $items->base_shmot($zzz);
	if ($items->count_base_item($f['login'], $zzz) == 0) msg2('У вас нет '.$itz['name'], 1);
	if (empty($go))
		{
		msg2('Вы уверены, что хотите собрать '.$item['name'].'?');
		knopka('zamok.php?mod=kuznica&start='.$start.'&iid='.$iid.'&go=1', 'Собрать', 1);
		knopka('loc.php', 'В игру', 1);
		fin();
		}
	$items->del_base_item($f['login'], $zzz, 1);
	$items->add_item($f['login'], $num, 1);
	$f['money'] -= ($start * 100);
	$q = $db->query("update `users` set money={$f['money']} where id={$f['id']} limit 1;");
	msg2('Вы перековали '.$itz['name'].' в '.$item['name'].'!');
break;

case 'upkuznica':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!',1);
	if($f['klan_status'] < 2) msg2('Вы не можете строить в замке.', 1);
	if(empty($klan['kuznica'])) { $money = 500; $kamni = 100;}
	elseif($klan['kuznica'] == 1) { $money = 1000; $kamni = 250;}
	elseif($klan['kuznica'] == 2) { $money = 2000; $kamni = 500;}
	elseif($klan['kuznica'] == 3) { $money = 5000; $kamni = 750;}
	elseif($klan['kuznica'] == 4) { $money = 10000; $kamni = 1000;}
	else msg2('У вас кузница максимального 5 уровня.',1);
	if(empty($go))
		{
		msg2('Вы действительно хотите построить или улучшить кузницу за '.$money.' монет и '.$kamni.' камней?');
		knopka('zamok.php?mod=upkuznica&go=1', 'Продолжаем!', 1);
		knopka('zamok.php', 'Отказаться', 1);
		fin();
		}
	if($klan['kazna'] < $money) msg('В казне недостаточно денег для постройки или улучшения кузницы, нужно '.$money, 1);
	if($klan['kamni'] < $kamni) msg('У клана недостаточно камней для постройки или улучшения кузницы, нужно '.$kamni, 1);
	$klan['kuznica']++;
	$klan['kazna'] -= $money;
	$klan['kamni'] -= $kamni;
	if($klan['kuznica'] == 1) $log = $f['login'].' ['.$f['lvl'].'] строит кузницу клана за '.$kamni.' камней и '.$money.' монет.';
	else $log = $f['login'].' ['.$f['lvl'].'] улучшает кузницу клана до '.$klan['kuznica'].' уровня за '.$kamni.' камней и '.$money.' монет.';
	$q = $db->query("insert into `klan_log` values(0,'{$f['login']}','{$log}','{$f['klan']}','{$t}');");
	$q = $db->query("update `klans` set kuznica={$klan['kuznica']},kazna={$klan['kazna']},kamni={$klan['kamni']} where id={$klan['id']} limit 1;");
	if($klan['kuznica'] == 1) msg2('Вы построили кузницу!');
	else msg2('Вы улучшили кузницу до '.$klan['kuznica'].' уровня!');
	knopka('zamok.php', 'В замок', 1);
break;

case 'oruzh':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!',1);
	if(empty($klan['oruzh'])) msg2('В замке нет оружейной мастерской!',1);
	$q = $db->query("select max(lvl) from `item`;");
	$a = $q->fetch_assoc();
	$max_lvl = $a['max(lvl)'];
	if (empty($start))
		{
		echo '<div class="board" style="text-align:left">';
		echo '<form action="zamok.php?mod=oruzh" method="POST">
		На какой уровень желаете изготовить вещи?<br/>
		<select name="start">';
		for ($i = 6; $i <= $max_lvl; $i++) echo '<option value='.$i.'>'.$i.'</option>';
		echo '</select>';
		echo '<input type="submit" value="Далее"/></form>';
		fin();
		}
	if ($start < 6) $start = 6;
	if ($start > $max_lvl) $start = $max_lvl;
	if (empty($iid))
		{ // ДОДЕЛАТЬ ДАЛЬШЕ!!! НАДО ПРОДУМАТЬ ЦЕНУ КАК СЧИТАТЬ!!!
		msg2('Вы хотите собрать вещи на '.$start.' уровень.');
		if(1 <= $klan['oruzh'])
			{
			$idd = $start + 639;
			$item = $items->base_shmot($idd);
			echo '<div class="board2" style="text-align:left">';
			echo '<a href="zamok.php?mod=oruzh&start='.$start.'&iid=1">'.$item['name'].'</a> ('.$item['price'].' монет)';
			echo ' <a href="shop.php?mod=iteminfa&iid='.$idd.'">[infa]</a>';
			echo '</div>';
			}
		if(2 <= $klan['oruzh'])
			{
			$idd = $start + 659;
			$item = $items->base_shmot($idd);
			echo '<div class="board2" style="text-align:left">';
			echo '<a href="zamok.php?mod=oruzh&start='.$start.'&iid=2">'.$item['name'].'</a> ('.$item['price'].' монет)';
			echo ' <a href="shop.php?mod=iteminfa&iid='.$idd.'">[infa]</a>';
			echo '</div>';
			}
		if(3 <= $klan['oruzh'])
			{
			$idd = $start + 679;
			$item = $items->base_shmot($idd);
			echo '<div class="board2" style="text-align:left">';
			echo '<a href="zamok.php?mod=oruzh&start='.$start.'&iid=3">'.$item['name'].'</a> ('.$item['price'].' монет)';
			echo ' <a href="shop.php?mod=iteminfa&iid='.$idd.'">[infa]</a>';
			echo '</div>';
			}
		fin();
		}
	$iid = intval($iid);
	if ($iid == 1) $num = $start + 639;
	elseif($iid == 2) $num = $start + 659;
	elseif($iid == 3) $num = $start + 679;
	else
		{
		$m = $f['lvl'] * 1000;
		$q = $db->query("update `users` set money=money-'{$m}' where id={$f['id']} limit 1;");
		msg2('Подменять ссылки нехорошо. С вас списан штраф '.$m.' монет за баловство.',1);
		}
	$item = $items->base_shmot($num);
	if ($f['money'] < $item['price']) msg2('У вас не хватает денег!', 1);
	if (empty($go))
		{
		msg2('Вы уверены, что хотите изготовить '.$item['name'].'?');
		knopka('zamok.php?mod=oruzh&start='.$start.'&iid='.$iid.'&go=1', 'Изготовить', 1);
		knopka('loc.php', 'В игру', 1);
		fin();
		}
	$items->add_item($f['login'], $num, 1);
	$f['money'] -= $item['price'];
	$q = $db->query("update `klans` set kazna=kazna+{$item['price']} where id={$klan['id']} limit 1;");
	$q = $db->query("update `users` set money={$f['money']} where id={$f['id']} limit 1;");
	$log = $f['login'].' ['.$f['lvl'].'] изготавливает '.$item['name'].' за '.$item['price'].' монет. Казна пополнена.';
	$q = $db->query("insert into `klan_log` values(0,'{$f['login']}','{$log}','{$f['klan']}','{$t}');");
	msg2('Вы изготовили '.$item['name'].'!');
break;

case 'uporuzh':
	if($f['klan'] != $klan['name']) msg2('Это не ваш замок!',1);
	if($f['klan_status'] < 2) msg2('Вы не можете строить в замке.', 1);
	if(empty($klan['oruzh'])) { $money = 500; $kamni = 100;}
	elseif($klan['oruzh'] == 1) { $money = 5000; $kamni = 500;}
	elseif($klan['oruzh'] == 2) { $money = 10000; $kamni = 1000;}
	else msg2('У вас оружейная мастерская максимального 3 уровня.',1);
	if(empty($go))
		{
		msg2('Вы действительно хотите построить или улучшить оружейную мастерскую за '.$money.' монет и '.$kamni.' камней?');
		knopka('zamok.php?mod=uporuzh&go=1', 'Продолжаем!', 1);
		knopka('zamok.php', 'Отказаться', 1);
		fin();
		}
	if($klan['kazna'] < $money) msg('В казне недостаточно денег для постройки или улучшения оружейной мастерской, нужно '.$money, 1);
	if($klan['kamni'] < $kamni) msg('У клана недостаточно камней для постройки или улучшения оружейной мастерской, нужно '.$kamni, 1);
	$klan['oruzh']++;
	$klan['kazna'] -= $money;
	$klan['kamni'] -= $kamni;
	if($klan['oruzh'] == 1) $log = $f['login'].' ['.$f['lvl'].'] строит оружейную мастерскую клана за '.$kamni.' камней и '.$money.' монет.';
	else $log = $f['login'].' ['.$f['lvl'].'] улучшает оружейную мастерскую клана до '.$klan['altar'].' уровня за '.$kamni.' камней и '.$money.' монет.';
	$q = $db->query("insert into `klan_log` values(0,'{$f['login']}','{$log}','{$f['klan']}','{$t}');");
	$q = $db->query("update `klans` set oruzh={$klan['oruzh']},kazna={$klan['kazna']},kamni={$klan['kamni']} where id={$klan['id']} limit 1;");
	if($klan['oruzh'] == 1) msg2('Вы построили оружейную мастерскую!');
	else msg2('Вы улучшили оружейную мастерскую до '.$klan['oruzh'].' уровня!');
	knopka('zamok.php', 'В замок', 1);
break;

case 'log':
	if($klan['name'] != $f['klan']) msg2('Смотреть дворовую книгу могут только члены клана '.$klan['name'],1);
	$numb = 50;			//записей на страницу
	$count = 0;
	$q = $db->query("select count(*) from `klan_log` where klan='{$f['klan']}';");
	$a = $q->fetch_assoc();
	$all_log = $a['count(*)'];
	if($start > intval($all_log / $numb)) $start = intval($all_log / $numb);
	if($start < 0) $start = 0;
	$limit = $start * $numb;
	$count = $limit;
	$q = $db->query("select * from `klan_log` where klan='{$f['klan']}' order by id desc limit {$limit},{$numb};");
	while($log = $q->fetch_assoc())
		{
		$count++;
		echo '<div class="board2" style="text-align:left">';
		echo $count.'. '.date('d.m.Y H:i',$log['date']).' - '.$log['log'];
		echo '</div>';
		}
	if($all_log > $numb)
		{
		echo '<div class="board">';
		if($start > 0) echo '<a href="zamok.php?mod=log&start='.($start - 1).'" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"> <-Назад</a>';
		echo ' | ';
		if($limit + $numb < $all_log) echo '<a href="zamok.php?mod=log&start='.($start + 1).'" class="navig" >Вперед-></a>'; else echo ' <a href="#" class="navig"> Вперед-></a>';
		echo '</div>';
		}
	fin();
break;
endswitch;
if(!empty($mod)) knopka('zamok.php', 'Вернуться', 1);
fin();
?>
