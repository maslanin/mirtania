<?php
##############
# 24.12.2014 #
##############

switch($f['loc']):
case 1:
	if($f['lvl'] <= 3) knopka2('act.php', '<b>Тренер</b>');
	knopka2('shop.php', 'Торговец');
	$q = $db->query("select count(*) from `invent` where flag_rinok=1;");
	$a = $q->fetch_assoc();
	$a = $a['count(*)'];
	knopka2('rinok.php', 'Рынок (<b>'.$a.'</b>)');
	knopka2('bank.php', 'Банк');
	knopka2('news.php', DateNews());
	knopka2('lib.php', 'Библиотека');
	$q = $db->query("select count(*) from `arena` where time>'{$t}';");
	$a = $q->fetch_assoc();
	$a = $a['count(*)'];
	knopka2('arena.php', 'Арена боев (<b>'.$a.'</b>)');
break;

case 5:
	knopka2('act.php', 'Атаковать!');
break;

case 8:
	knopka2('act.php', 'Напасть!');
	if(!empty($f['klan'])) knopka2('rabota.php', 'Идти к шахте');
break;

case 9:
	knopka2('act.php', 'Бить жука!');
break;

case 11:
	knopka2('act.php', 'Напасть!');
break;

case 13:
	knopka2('act.php', 'Напасть на шершней!');
break;

case 14:
	knopka2('act.php', 'Напасть!');
break;

case 15:
	knopka2('kvest.php', 'Собирать рис');
break;

case 18:
	knopka2('kvest.php', 'Собирать целебную траву');
break;

case 19:
	knopka2('rabota.php', 'Старая шахта');
break;

case 20:
	knopka2('act.php', 'Охотиться на остеров');
break;

case 21:
	knopka2('act.php', 'Драться с гоблинами');
break;

case 22:
	knopka2('act.php', 'Заглянуть в пещеру');
break;

case 23:
	knopka2('kvest.php', 'Клановый распорядитель');
break;

case 31:
	knopka2('kvest.php', 'Собирать корень маны');
break;

case 37:
	knopka2('shop.php', 'Торговец');
	knopka2('bank.php', 'Банк');
	knopka2('news.php', DateNews());
	knopka2('lib.php', 'Библиотека');
break;

case 38:
	knopka2('taverna.php', 'В таверну');
break;

case 39:
	knopka2('act.php', 'Напасть на шмыга');
break;

case 41:
	knopka2('act.php', 'Напасть!');
break;

case 43:
	$kvest = unserialize($f['kvest']);
	if(!empty($kvest['loc56ks']) && $kvest['loc56ks']['nagrada'] == 0 && $kvest['loc56ks']['lg'] == 0)
		{
		knopka2('act.php', 'Подойти к глыбе льда');
		}
	unset($kvest);
break;

case 44:
	knopka2('rabota.php', 'Наняться надзирателем');
break;

case 45:
	knopka2('kvest.php', 'Наковальня');
break;

case 49:
	knopka2('act.php', 'Напасть!');
break;

case 51:
	knopka2('kvest.php', 'Собирать виноград');
break;

case 55:
	$kvest = unserialize($f['kvest']);
	if(!empty($kvest['loc56ks']) && $kvest['loc56ks']['nagrada'] == 0 && $kvest['loc56ks']['og'] == 0)
		{
		knopka2('act.php', 'Подойти к горе огня');
		}
	unset($kvest);
break;

case 56:
	knopka2('kvest.php', 'Зайти в башню');
break;

case 62:
	$kvest = unserialize($f['kvest']);
	if(!empty($kvest['loc56ks']) && $kvest['loc56ks']['nagrada'] == 0 && $kvest['loc56ks']['kg'] == 0)
		{
		knopka2('act.php', 'Наброситься на каменного голема');
		}
	unset($kvest);
break;

case 67:
	knopka2('act.php', 'Осмотреть гнездо');
break;

case 68:
	knopka2('kvest.php', 'Собирать солод');
break;

case 69:
	knopka2('kvest.php', 'Грабить корован');
break;

case 73:
	knopka2('act.php', 'Осмотреться');
break;

case 77:
	knopka2('kvest.php', 'Подойти к старику');
break;

case 78:
	knopka2('kvest.php', 'Собирать мёд');
break;

case 83:
	knopka2('kvest.php', 'Собирать хмель');
break;

case 89:
	knopka2('act.php', 'Бить болотожора');
break;

case 91:
	knopka2('shop.php', 'Торговец');
	knopka2('bank.php', 'Банк');
	knopka2('news.php', DateNews());
	knopka2('lib.php', 'Библиотека');
break;

case 92:
	knopka2('act.php', 'Вступить в бой');
break;

case 95:
	knopka2('act.php', 'Приоткрыть дверь');
break;

case 96:
	knopka2('act.php', 'Напасть на монстра');
break;

case 99:
	knopka2('kvest.php', 'Подойти к старику');
break;

case 100:
	knopka2('act.php', 'Атаковать');
break;

case 103:
	knopka2('act.php', 'Заглянуть в гнездо');
break;

case 105:
	if($f['fishrod']>0) knopka2('kvest.php', 'Забросить удочку');
break;

case 106:
	knopka2('kvest.php', 'Залезть на дерево');
break;

case 107:
	knopka2('kvest.php', 'Искать наживку');
break;

case 109:
	knopka2('kvest.php', 'Подойти к куче костей');
break;
endswitch;

$q = $db->query("select name,loc,point from `klans` where loc={$f['loc']} or point={$f['loc']} limit 1;");
if($q->num_rows > 0)
	{
	$a = $q->fetch_assoc();
	knopka2('zamok.php', 'Замок клана '.$a['name']);
	}

?>
