<?php
##############
# 24.12.2014 #
##############

switch($f['lvl']):
case 1:
	$exp = 500;
break;

case 2:
	$exp = 2000;
break;

case 3:
	$exp = 5000;
break;

case 4:
	$exp = 10000;
break;

case 5:
	$exp = 20000;
break;

case 6:
	$exp = 50000;
break;

case 7:
	$exp = 100000;
break;

case 8:
	$exp = 200000;
break;

case 9:
	$exp = 500000;
break;

case 10:
	$exp = 1000000;
break;

case 11:
	$exp = 2000000;
break;

case 12:
	$exp = 5000000;
break;

case 13:
	$exp = 10000000;
break;

case 14:
	$exp = 20000000;
break;

case 15:
	$exp = 50000000;
break;

case 16:
	$exp = 100000000;
break;

case 17:
	$exp = 200000000;
break;

case 18:
	$exp = 500000000;
break;

case 19:
	$exp = 1000000000;
break;

default:
	$exp = 2000000000;
break;
endswitch;
// опыта до апа
$tolev = $exp - $f['exp'];

$all_stats = 0;
$mlvl = $f['lvl'];
if($mlvl > 25) $mlvl = 25;
for($i = 1; $i <= $mlvl; $i++)
	{
	$all_stats += (10 + $i);
	}
// свободных статов
$stat_free = $all_stats - ($f['sila'] + $f['inta'] + $f['lovka'] + $f['intel'] + $f['zdor']); //свободные статы
?>
