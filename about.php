<?php
##############
# 24.12.2014 #
##############

require_once('inc/top.php');
require_once('inc/head.php');

$mod = (isset($_REQUEST['mod'])) ? $_REQUEST['mod'] : '';
switch($mod):
case 'avt':
	msg2('
	<b>maslanin</b> (Андрей) - программирование, текст.<br/>
	<b>frin</b> (Макс) - Дизайн<br/>
	Официальный сайт игры - <a href="http://hmr.su">HMR.SU</a>');
break;

case 'donate':
	msg2('Кошельки для пожертвований в пользу оплаты сервера игры<br/><b>R422552891903<br/>Z420596185021<br/>E100952040300</b>');
break;

default:
	msg2('Это многопользовательская игра последнего поколения. 
В игре одновременно могут участвовать несколько тысяч персонажей контролируемых людьми. 
Средневековый, сказочный мир, наполненный чудесами и опасностями, монстрами и героями откроется для Вас.');
break;
endswitch;

knopka('index.php', 'На главную', 1);
fin();
?>
