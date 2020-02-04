<?php

$q = $db->query("select * from `settings` where id=1 limit 1;");
$settings = $q->fetch_assoc();

//фильтрация от взломов
function ekr($a)
	{
	$db = DBC::instance();
	$a = trim($a);
	$a = htmlspecialchars($a);
	$a = $db->real_escape_string($a);
	return $a;
	}

function calcparam($s)
	{
	$db = DBC::instance();
	require_once('class/items.php');
	$items = items::instance(); 
	$s['krit'] = $s['inta'] * 10;				//изначальный крит = инта * 10;
	$s['uvorot'] = $s['lovka'] * 10;			//изначальный уворот = ловка * 10
	$s['uron'] = intval($s['sila'] * 1.2);		//урон = силе * 1.2
	$s['bron'] = $s['sila'];					//бронь = силе
	$s['hpmax'] = $s['zdor'] * 10;				//хп = здоровье * 10 + со шмота
	$s['manamax'] = $s['intel'] * 10;			//мана = интеллект * 10
	if($s['hpmax'] < 10) $s['hpmax'] = 10;		//лимит будет 10
	if($s['manamax'] < 10) $s['manamax'] = 10;	//лимит будет 10

	$q = $db->query("select `invent`.`id` from `invent`,`item` where ((`invent`.`login`='{$s['login']}' and `invent`.`flag_arenda`=0) or (`invent`.`arenda_login`='{$s['login']}' and `invent`.`flag_arenda`=1)) and `invent`.`flag_rinok`=0 and `invent`.`flag_equip`=1 and (`item`.`equip`<>'' and `item`.`equip`<>'sumka') and `invent`.`ido`=`item`.`id`;");
	while($a = $q->fetch_assoc())
		{
		$item = $items->shmot($a['id']);
		if(empty($s['art'][$item['art']])) $s['art'][$item['art']] = 0;
		$s['krit']		+= ceil($item['krit']	+ ($item['up'] * $item['krit']	/ 100));
		$s['uvorot']	+= ceil($item['uvorot']	+ ($item['up'] * $item['uvorot']	/ 100));
		$s['uron']		+= ceil($item['uron']	+ ($item['up'] * $item['uron']	/ 100));
		$s['bron']		+= ceil($item['bron']	+ ($item['up'] * $item['bron']	/ 100));
		$s['hpmax']		+= ceil($item['hp']	+ ($item['up'] * $item['hp']	/ 100));
		if(!empty($item['art'])) $s['art'][$item['art']] ++;
		}
	if($s['altar'] > 0)
		{
		if($s['altar_time'] < $_SERVER['REQUEST_TIME'])
			{
			$s['altar'] = 0;
			$s['altar_time'] = 0;
			$q = $db->query("update `users` set altar=0,altar_time=0 where id='{$s['id']}' limit 1;");
			}
		}
	if($s['altar'] > 0)
		{
		$mnozh = $s['altar'] * 0.01;
		$s['krit']		+= ceil($s['krit']		* $mnozh);
		$s['uvorot']	+= ceil($s['uvorot']	* $mnozh);
		$s['uron']		+= ceil($s['uron']		* $mnozh);
		$s['bron']		+= ceil($s['bron']		* $mnozh);
		}
	if($s['doping'] > 0)
		{
		if($s['doping_time'] < $_SERVER['REQUEST_TIME'])
			{
			$s['doping'] = 0;
			$s['doping_time'] = 0;
			$q = $db->query("update `users` set doping=0,doping_time=0 where id='{$s['id']}' limit 1;");
			}
		}
	if($s['doping'] > 0) require_once('inc/doping.php');
	$q = $db->query("update `users` set krit={$s['krit']},uvorot={$s['uvorot']},bron={$s['bron']},uron={$s['uron']},hpmax={$s['hpmax']},manamax={$s['manamax']} where id={$s['id']} limit 1;");
	return $s;
	}

//функция вывода смайлов :)
function smile($s)
	{
	$s = str_replace('.афтар.', '<img src="smile/aftar.gif"/>', $s);
	$s = str_replace('.бан.', '<img src="smile/ban.gif"/>', $s);
	$s = str_replace('.банан.', '<img src="smile/banan.gif"/>', $s);
	$s = str_replace('.банан1.', '<img src="smile/banan1.gif"/>', $s);
	$s = str_replace('.бомж.', '<img src=\'smile/bomj.gif\'/>', $s);
	$s = str_replace('.браво.', '<img src=\'smile/bravo.gif\'/>', $s);
	$s = str_replace('.чмак.', '<img src=\'smile/chmak.gif\'/>', $s);
	$s = str_replace('.дедмороз.', '<img src=\'smile/dedmoroz.gif\'/>', $s);
	$s = str_replace('.дети.', '<img src=\'smile/deti.gif\'/>', $s);
	$s = str_replace('.днюха.', '<img src=\'smile/denrojd.gif\'/>', $s);
	$s = str_replace('.добрый.', '<img src=\'smile/dobrij.gif\'/>', $s);
	$s = str_replace('.достали.', '<img src=\'smile/dostali.gif\'/>', $s);
	$s = str_replace('.драка.', '<img src=\'smile/draka.gif\'/>', $s);
	$s = str_replace('.дум.', '<img src=\'smile/dum.gif\'/>', $s);
	$s = str_replace('.душ.', '<img src=\'smile/dush.gif\'/>', $s);
	$s = str_replace('.дятел.', '<img src=\'smile/djatel.gif\'/>', $s);
	$s = str_replace('.елка.', '<img src=\'smile/elka.gif\'/>', $s);
	$s = str_replace('.ёлка.', '<img src=\'smile/elka.gif\'/>', $s);
	$s = str_replace('.фан.', '<img src=\'smile/fan.gif\'/>', $s);
	$s = str_replace('.фанаты.', '<img src=\'smile/fans.gif\'/>', $s);
	$s = str_replace('.фигасе.', '<img src=\'smile/figase.gif\'/>', $s);
	$s = str_replace('.флаг.', '<img src=\'smile/flag.gif\'/>', $s);
	$s = str_replace('.флаг1.', '<img src=\'smile/flag1.gif\'/>', $s);
	$s = str_replace('.флуд.', '<img src=\'smile/flud.gif\'/>', $s);
	$s = str_replace('.говнецо.', '<img src=\'smile/govneco.gif\'/>', $s);
	$s = str_replace('.грабли.', '<img src=\'smile/grabli.gif\'/>', $s);
	$s = str_replace('.грамота.', '<img src=\'smile/gramota.gif\'/>', $s);
	$s = str_replace('.сердце.', '<img src=\'smile/heart.gif\'/>', $s);
	$s = str_replace('.хор.', '<img src=\'smile/hor.gif\'/>', $s);
	$s = str_replace('.истерика.', '<img src=\'smile/isterika.gif\'/>', $s);
	$s = str_replace('.яд.', '<img src=\'smile/jad.gif\'/>', $s);
	$s = str_replace('.карты.', '<img src=\'smile/karty.gif\'/>', $s);
	$s = str_replace('.каток.', '<img src=\'smile/katok.gif\'/>', $s);
	$s = str_replace('.король.', '<img src=\'smile/king.gif\'/>', $s);
	$s = str_replace('.конфета.', '<img src=\'smile/konfeta.gif\'/>', $s);
	$s = str_replace('.кофе.', '<img src=\'smile/kofe.gif\'/>', $s);
	$s = str_replace('.комп.', '<img src=\'smile/komp.gif\'/>', $s);
	$s = str_replace('.конфетти.', '<img src=\'smile/konfetti.gif\'/>', $s);
	$s = str_replace('.конь.', '<img src=\'smile/konj.gif\'/>', $s);
	$s = str_replace('.курю.', '<img src=\'smile/kurju.gif\'/>', $s);
	$s = str_replace('.ладно.', '<img src=\'smile/ladno.gif\'/>', $s);
	$s = str_replace('.ляля.', '<img src=\'smile/ljalja.gif\'/>', $s);
	$s = str_replace('.медик.', '<img src=\'smile/medic.gif\'/>', $s);
	$s = str_replace('.молоток.', '<img src=\'smile/molotok.gif\'/>', $s);
	$s = str_replace('.нефлуди.', '<img src=\'smile/nefludi.gif\'/>', $s);
	$s = str_replace('.новыйгод.', '<img src=\'smile/newyear.gif\'/>', $s);
	$s = str_replace('.небань.', '<img src=\'smile/noban.gif\'/>', $s);
	$s = str_replace('.номер.', '<img src=\'smile/nomer.gif\'/>', $s);
	$s = str_replace('.ох.', '<img src=\'smile/oh.gif\'/>', $s);
	$s = str_replace('.пасиба.', '<img src=\'smile/pasiba.gif\'/>', $s);
	$s = str_replace('.песочница.', '<img src=\'smile/pesochnica.gif\'/>', $s);
	$s = str_replace('.пионер.', '<img src=\'smile/pioner.gif\'/>', $s);
	$s = str_replace('.письмо.', '<img src=\'smile/pismo.gif\'/>', $s);
	$s = str_replace('.пифпаф.', '<img src=\'smile/pifpaf.gif\'/>', $s);
	$s = str_replace('.пиво.', '<img src=\'smile/pivo.gif\'/>', $s);
	$s = str_replace('.плак.', '<img src=\'smile/plac.gif\'/>', $s);
	$s = str_replace('.плохо.', '<img src=\'smile/ploho.gif\'/>', $s);
	$s = str_replace('.плюсодин.', '<img src=\'smile/plusodin.gif\'/>', $s);
	$s = str_replace('.побили.', '<img src=\'smile/pobili.gif\'/>', $s);
	$s = str_replace('.подарок.', '<img src=\'smile/podarok.gif\'/>', $s);
	$s = str_replace('.пока.', '<img src=\'smile/poka.gif\'/>', $s);
	$s = str_replace('.попа.', '<img src=\'smile/popa.gif\'/>', $s);
	$s = str_replace('.превед.', '<img src=\'smile/preved.gif\'/>', $s);
	$s = str_replace('.привет.', '<img src=\'smile/privet.gif\'/>', $s);
	$s = str_replace('.прыг.', '<img src=\'smile/pryg.gif\'/>', $s);
	$s = str_replace('.репка.', '<img src=\'smile/repka.gif\'/>', $s);
	$s = str_replace('.ромашка.', '<img src=\'smile/romashka.gif\'/>', $s);
	$s = str_replace('.роза.', '<img src=\'smile/roza.gif\'/>', $s);
	$s = str_replace('.русский.', '<img src=\'smile/russkij.gif\'/>', $s);
	$s = str_replace('.русский1.', '<img src=\'smile/russkij1.gif\'/>', $s);
	$s = str_replace('.ржу.', '<img src=\'smile/rzhu.gif\'/>', $s);
	$s = str_replace('.секас.', '<img src=\'smile/sekas.gif\'/>', $s);
	$s = str_replace('.семья.', '<img src=\'smile/semja.gif\'/>', $s);
	$s = str_replace('.сиськи.', '<img src=\'smile/siski.gif\'/>', $s);
	$s = str_replace('.смех.', '<img src=\'smile/smeh.gif\'/>', $s);
	$s = str_replace('.сигарета.', '<img src=\'smile/smoke.gif\'/>', $s);
	$s = str_replace('.солнце.', '<img src=\'smile/solnce.gif\'/>', $s);
	$s = str_replace('.спам.', '<img src=\'smile/spam.gif\'/>', $s);
	$s = str_replace('.стих.', '<img src=\'smile/stih.gif\'/>', $s);
	$s = str_replace('.сцуко.', '<img src=\'smile/scuko.gif\'/>', $s);
	$s = str_replace('.свадьба.', '<img src=\'smile/svadba.gif\'/>', $s);
	$s = str_replace('.свист.', '<img src=\'smile/svist.gif\'/>', $s);
	$s = str_replace('.согласен.', '<img src=\'smile/soglasen.gif\'/>', $s);
	$s = str_replace('.танцы.', '<img src=\'smile/tancy.gif\'/>', $s);
	$s = str_replace('.тема.', '<img src=\'smile/tema.gif\'/>', $s);
	$s = str_replace('.тормоз.', '<img src=\'smile/tormoz.gif\'/>', $s);
	$s = str_replace('.туса.', '<img src=\'smile/tusa.gif\'/>', $s);
	$s = str_replace('.утро.', '<img src=\'smile/utro.gif\'/>', $s);
	$s = str_replace('.велик.', '<img src=\'smile/velik.gif\'/>', $s);
	$s = str_replace('.велком.', '<img src=\'smile/wellcome.gif\'/>', $s);
	$s = str_replace('.вестерн.', '<img src=\'smile/vestern.gif\'/>', $s);
	$s = str_replace('.винсент.', '<img src=\'smile/vinsent.gif\'/>', $s);
	$s = str_replace('.язык.', '<img src=\'smile/yazik.gif\'/>', $s);
	$s = str_replace('.зяфк.', '<img src=\'smile/zjafk.gif\'/>', $s);
	return $s;
	}

//отправка УТФ-8 почты
function mail_utf8($to, $subject = '(No subject)', $message = '', $from)
	{
	$header = "MIME-Version: 1.0"."\n"."Content-type: text/plain; charset=UTF-8"."\n"."From: ".$from."\n";
	return mail($to, "=?UTF-8?B?".base64_encode($subject)."?=", $message, $header, '-f '.$from);
	}

function link_it($s)
	{
	$s = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"out.php?url=$3\" >$3</a>", $s);
	$s = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"out.php?url=http://$3\" >$3</a>", $s);
	$s = preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\ .]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $s);
	return($s);
	}

function DateNews()
	{
	$db = DBC::instance();
	$q = $db->query("select datenews from `news` order by id desc limit 1;");
	if ($q->num_rows == 0) $news = 'Новостей нет';
	else
		{
		$news = $q->fetch_assoc();
		$news = 'Новости ('.Date('d.m.Y H:i', $news['datenews']).')';
		}
	return $news;
	}

function get_login($lgn = '')
	{
	$db = DBC::instance();
	if (preg_match("/[^a-zA-Z0-9_а-яА-ЯёЁ]/u", $lgn)) msg('Неверно набран логин!', 1);
	$q = $db->query("select * from `users` where login='{$lgn}' limit 1;");
	if ($q->num_rows == 0) msg('Логин не найден!', 1);
	$lgn = $q->fetch_assoc();
	return $lgn;
	}

function GetDay($s)
	{
	// ф-я возвращает разницу в днях между указанной датой и текущей.
	$another = mktime(0, 0, 0, date("m", $s), date("d", $s), date("Y", $s));
	$now = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	$diff = $now - $another;
	$diff = intval($diff / 86400);
	if ($diff < 0) $diff *= -1;
	return $diff;
	}

function klan_points($name, $i)
	{
	$db = DBC::instance();
	$q = $db->query("select * from `klans` where name='{$name}' limit 1;");
	$a = $q->fetch_assoc();
	if ($a['points'] >= $a['lvl'] * 1000)
		{
		$a['points'] = 0;
		$a['lvl'] += 1;
		}
	if ($a['points'] + $i < 0) $a['points'] = 0;
	else $a['points'] += $i;
	$q = $db->query("update `klans` set points={$a['points']},lvl={$a['lvl']} where name='{$name}' limit 1;");
	return true;
	}

function addexp($id,$num)
	{
	$db = DBC::instance();
	$q = $db->query("update `users` set exp=exp+'{$num}' where id='{$id}' limit 1;");
	return true;
	}

function msg($s = '', $stop = 0)
	{
	echo '<div class="board2">'.$s.'</div>';
	echo '<div style="width:100%;height:4px;border: 0px solid #4f4f4f; border-top: 0px solid #4f4f4f; position:relative;background-color:#8b7e66;margin: 0px 0px 0px 0px;text-align:center;"></div>';
	if (!empty($stop))
		{
		if(!empty($_SESSION['auth']))
			{
			knopka('javascript:history.go(-1)', 'Вернуться');
			knopka('loc.php', 'В игру');
			}
		fin();
		}
	return true;
	}

function msg2($s = '', $stop = 0)
	{
	echo '<div class="board"><div class="board2">'.$s.'</div></div>';
	echo '<div style="width:100%;height:4px;border: 0px solid #4f4f4f; border-top: 0px solid #4f4f4f; position:relative;background-color:#8b7e66;margin: 0px 0px 0px 0px;text-align:center;"></div>';
	if (!empty($stop))
		{
		if(!empty($_SESSION['auth']))
			{
			knopka('javascript:history.go(-1)', 'Вернуться');
			knopka('loc.php', 'В игру');
			}
		fin();
		}
	return true;
	}

function msg3($s = '', $stop = 0)
	{
	echo '<div class="board">'.$s.'</div>';
	echo '<div style="width:100%;height:4px;border: 0px solid #4f4f4f; border-top: 0px solid #4f4f4f; position:relative;background-color:#8b7e66;margin: 0px 0px 0px 0px;text-align:center;"></div>';
	if (!empty($stop))
		{
		if(!empty($_SESSION['auth']))
			{
			knopka('javascript:history.go(-1)', 'Вернуться');
			knopka('loc.php', 'В игру');
			}
		fin();
		}
	return true;
	}

function knopka($url, $name = '', $kart = 0)
	{ // кнопка
	if (empty($url)) $url = 'http://'.$_SERVER['SERVER_NAME'];
	if (empty($name)) $name = $url;
	echo '<div class="menu_j"><a href="'.$url.'" class="top_menu_j">';
	if (!empty($kart)) echo '<img src="pic/k.png" alt=""/> ';
	echo $name.'</a></div>';
	return true;
	}

function knopka2($url, $name)
	{
	if (empty($url)) $url = 'http://'.$_SERVER['SERVER_NAME'];
	if (empty($name)) $name = $url;
	echo '<a class="main-knopki" href="'.$url.'">'.$name.'</a>';
	return true;
	}

function fin($s = '')
	{
	global $time_start,$version;
	if (!empty($s)) echo $s.'<br/>';
	echo '</div>';
	$time_end = microtime(true);
	$alltime = round($time_end - $time_start, 5);
	echo '<div class="head" align="center">';
	echo $alltime.' сек';
	echo '</div>';
	echo '<center><small>';
	echo '<font color="white">Игра на реконструкции';
	//echo '<font color="white">&copy; maslanin 2006';
	//if(date('Y') > 2006) echo ' - '.date('Y');
	echo '</small>';
	if(empty($_SESSION['mobtop']) or $_SESSION['mobtop'] < $_SERVER['REQUEST_TIME'])
		{
		echo '<br/><script type="text/javascript" src="http://mobtop.ru/c/42587.js"></script><noscript><a href="http://mobtop.ru/in/42587"><img src="http://mobtop.ru/42587.gif" alt="MobTop.Ru - рейтинг мобильных сайтов"/></a></noscript>';
		$_SESSION['mobtop'] = $_SERVER['REQUEST_TIME'] += mt_rand(200,600);
		}
	echo '</center></body></html>';
	exit();
	}

?>
