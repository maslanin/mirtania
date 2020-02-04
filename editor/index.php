<?php
error_reporting(-1);                    //вывод ВСЕВОЗМОЖНЫХ ошибок
ini_set('display_errors',TRUE);
ini_set('display_startup_errors',TRUE);
Header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); //Дата в прошлом
Header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
Header('Pragma: no-cache'); // HTTP/1.1
Header('Last-Modified: '.gmdate("D, d M Y H:i:s").'GMT');
Header('Content-Type: text/html; charset=utf-8');
setlocale(LC_CTYPE, 'ru_RU.UTF-8');
echo '<title>Редактор карт v0.0.3</title>';
require_once('DBC.php');
$PHP_SELF = 'index.php';
$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';
$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
$info = isset($_REQUEST['info']) ? $_REQUEST['info'] : '';
$way = isset($_REQUEST['way']) ? $_REQUEST['way'] : '';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : 0;
$X = isset($_REQUEST['x']) ? $_REQUEST['x'] : 0;
$Y = isset($_REQUEST['y']) ? $_REQUEST['y'] : 0;
$mir = isset($_REQUEST['mir']) ? $_REQUEST['mir'] : 0;
$map = isset($_REQUEST['map']) ? $_REQUEST['map'] : 0;

$q = $db->query("CREATE TABLE IF NOT EXISTS `loc` (
`id` int(12) NOT NULL AUTO_INCREMENT,
`map_id` int(12) NOT NULL DEFAULT '0',
`name` text NOT NULL,
`N` int(1) NOT NULL DEFAULT '0',
`S` int(1) NOT NULL DEFAULT '0',
`W` int(1) NOT NULL DEFAULT '0',
`E` int(1) NOT NULL DEFAULT '0',
`X` int(12) NOT NULL DEFAULT '0',
`Y` int(12) NOT NULL DEFAULT '0',
`peace` int(1) NOT NULL DEFAULT '0',
`info` text NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

$q = $db->query("CREATE TABLE IF NOT EXISTS `map` (
`id` int(12) NOT NULL AUTO_INCREMENT,
`x` int(12) NOT NULL DEFAULT '1',
`y` int(12) NOT NULL DEFAULT '1',
`name` text NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
switch($mod):
default:
	if(empty($map))
		{
		echo 'Выберите карту для редактирования (<a href="'.$PHP_SELF.'?mod=new">создать новую</a>):<br/>';
		$q = $db->query("select name,id from map;");
		if($q->num_rows == 0) exit('Нет ни одной карты.');
		while($a = $q->fetch_assoc())
			{
			echo '<a href="'.$PHP_SELF.'?map='.$a['id'].'">'.$a['name'].'</a> <a href="'.$PHP_SELF.'?mod=del&id='.$a['id'].'">[x]</a><br/>';
			}
		exit;
		}
	$map = intval($map);
	echo '<form action="index.php?map='.$map.'" method="POST">';
	echo '<a href="'.$PHP_SELF.'?mod=del&id='.$map.'">Удалить карту</a> / ';
	echo '<a href="'.$PHP_SELF.'?mod=mapedit&id='.$map.'">Настройки карты</a> / ';
	echo '<a href="'.$PHP_SELF.'">К выбору карт</a><hr/>';
	echo 'Добавить: <input type="submit" value="Строку сверху" name="addup"/>';
	echo '<input type="submit" value="Строку снизу" name="adddown"/>';
	echo '<input type="submit" value="Столбец слева" name="addleft"/>';
	echo '<input type="submit" value="Столбец справа" name="addright"/><br/>';
	echo 'Удалить: <input type="submit" value="Строку сверху" name="delup"/>';
	echo '<input type="submit" value="Строку снизу" name="deldown"/>';
	echo '<input type="submit" value="Столбец слева" name="delleft"/>';
	echo '<input type="submit" value="Столбец справа" name="delright"/>';
	echo '</form>';
	$q = $db->query("select * from `map` where id='{$map}' limit 1;");
	if($q->num_rows == 0) exit('Карта не найдена.');
	$a = $q->fetch_assoc();
	$qq = $db->query("select count(*) from `loc` where map_id={$map};");
	$c = $qq->fetch_assoc();
	$c = $c['count(*)'];
	echo 'Название: <b>'.$a['name'].'<b>, Размер: '.$a['x'].':'.$a['y'].', '.$c.' локаций<hr/>';
	$x1 = 1;
	$y1 = 1;
	$x2 = $a['x'];
	$y2 = $a['y'];
	if(isset($_REQUEST['addup']) and $a['y'] < 200)
		{
		$q = $db->query("update `map` set y=y+1 where id={$map};"); // просто меняем размер по игрекам
		echo '<script>window.location="'.$PHP_SELF.'?map='.$map.'"</script>';
		//header("location: ".$PHP_SELF."?map=".$map);
		exit;
		}
	if(isset($_REQUEST['addright']) and $a['x'] < 200)
		{
		$q = $db->query("update `map` set x=x+1 where id={$map};"); // просто меняем размер по иксам
		echo '<script>window.location="'.$PHP_SELF.'?map='.$map.'"</script>';
		//header("location: ".$PHP_SELF."?map=".$map);
		exit;
		}
	if(isset($_REQUEST['adddown']) and $a['y'] < 200)
		{
		$q = $db->query("update `map` set y=y+1 where id={$map};"); // меняем размер по игрекам и сдвигаем все локации по игрекам вверх на 1
		$q = $db->query("update `loc` set Y=Y+1 where map_id={$map};");
		echo '<script>window.location="'.$PHP_SELF.'?map='.$map.'"</script>';
		//header("location: ".$PHP_SELF."?map=".$map);
		exit;
		}
	if(isset($_REQUEST['addleft']) and $a['x'] < 200)
		{
		$q = $db->query("update `map` set x=x+1 where id={$map};"); // меняем размер по иксам и сдвигаем все локации по иксам вправо на 1
		$q = $db->query("update `loc` set X=X+1 where map_id={$map};");
		echo '<script>window.location="'.$PHP_SELF.'?map='.$map.'"</script>';
		//header("location: ".$PHP_SELF."?map=".$map);
		exit;
		}
	if(isset($_REQUEST['delup']) and $a['y'] > 1)
		{
		$q = $db->query("update `map` set y=y-1 where id={$map};"); // отнимаем единицу по игрекам, удаляем локи, которые вышли за пределы оставшейся карты
		$q = $db->query("delete from `loc` where Y>=".($a['y'])." and map_id={$map};");
		echo '<script>window.location="'.$PHP_SELF.'?map='.$map.'"</script>';
		//header("location: ".$PHP_SELF."?map=".$map);
		exit;
		}
	if(isset($_REQUEST['delright']) and $a['x'] > 1)
		{
		$q = $db->query("update `map` set x=x-1 where id={$map};"); // отнимаем единицу по иксам, удаляем локи, которые вышли за пределы оставшейся карты
		$q = $db->query("delete from `loc` where X>=".($a['x'])." and map_id={$map};");
		echo '<script>window.location="'.$PHP_SELF.'?map='.$map.'"</script>';
		//header("location: ".$PHP_SELF."?map=".$map);
		exit;
		}
	if(isset($_REQUEST['deldown']) and $a['y'] > 1)
		{
		$q = $db->query("update `map` set y=y-1 where id={$map};"); // отнимаем единицу размера по игреку, удаляем все НИЖНИЕ локи (у них игрек = 1), переназначаем новые игреки локам
		$q = $db->query("delete from `loc` where Y<=1 and map_id={$map};");
		$q = $db->query("update `loc` set Y=Y-1 where map_id={$map};");
		echo '<script>window.location="'.$PHP_SELF.'?map='.$map.'"</script>';
		//header("location: ".$PHP_SELF."?map=".$map);
		exit;
		}
	if(isset($_REQUEST['delleft']) and $a['x'] > 1)
		{
		$q = $db->query("update `map` set x=x-1 where id={$map};"); // отнимаем единицу размера по иксам, удаляем все ЛЕВЫЕ локи (у них икс = 1), переназначаем новые иксы локам
		$q = $db->query("delete from `loc` where X<=1 and map_id={$map};");
		$q = $db->query("update `loc` set X=X-1 where map_id={$map};");
		echo '<script>window.location="'.$PHP_SELF.'?map='.$map.'"</script>';
		//header("location: ".$PHP_SELF."?map=".$map);
		exit;
		}
	echo '<table name="loc" border="1">';
	for($i = $y2; $i >= $y1; $i--)
		{
		echo '<tr>';
		for($j = $x1; $j <= $x2; $j++)
			{
			$q = $db->query("select id,N,S,W,E from `loc` where X={$j} and Y={$i} and map_id={$map} limit 1;");
			$a = $q->fetch_assoc() or $a['id'] = 0;
			if(empty($a['id'])) $name = '0.png';
			else $name = '.png';
			if(!empty($a['W'])) $name = 'W'.$name;
			if(!empty($a['E'])) $name = 'E'.$name;
			if(!empty($a['S'])) $name = 'S'.$name;
			if(!empty($a['N'])) $name = 'N'.$name;
			echo '<td><a href="'.$PHP_SELF.'?mod=edit&map='.$map.'&id='.$a['id'].'&x='.$j.'&y='.$i.'"><img src="pic/'.$name.'" width="24" height="24"/></a></td>';
			}
		echo '</tr>';
		}
	echo '</table>';
break;

case 'new':
	if(empty($ok))
		{
		echo '<form action="'.$PHP_SELF.'?mod=new&ok=1" method="POST">';
		echo 'Создание новой карты<br/><br/></b><br/>';
		echo 'Название:<br/><input type="text" name="name"/><br/>';
		echo 'Ширина:<br/><input type="text" name="x"/><br/>';
		echo 'Высота:<br/><input type="text" name="y"/><br/>';
		echo '<input type="submit" value="Далее"/>';
		echo '</form>';
		echo '<a href="'.$PHP_SELF.'">К выбору карт</a><br/>';
		exit;
		}
	if(empty($name)) exit('Введите название карты!');
	if(empty($X) or $X < 0 or $X > 200) exit('Неверная ширина, можно от 1 до 200!');
	if(empty($Y) or $Y < 0 or $Y > 200) exit('Неверная высота, можно от 1 до 200!');
	if($mir == 1) $p = 1; else $p = 0;
	$q = $db->query("insert into `map` values(0,{$X},{$Y},'".$db->real_escape_string($name)."');");
	echo 'Карта создана. <a href="'.$PHP_SELF.'?map='.$db->insert_id().'">Перейти</a>';
	exit;
break;

case 'mapedit':
	$id = intval($id);
	if($id <= 0) exit('Неверный ID карты');
	$q = $db->query("select * from `map` where id='{$id}' limit 1;");
	if($q->num_rows == 0) exit('Нет такой карты');
	$a = $q->fetch_assoc();
	if(empty($ok))
		{
		echo '<form action="'.$PHP_SELF.'?mod=mapedit&ok=1&id='.$id.'" method="POST">';
		echo 'Редактирование карты<br/><br/><br/>';
		echo 'Название:<br/><input type="text" name="name" value="'.$a['name'].'"/><br/>';
		echo '<input type="submit" value="Сохранить"/>';
		echo '</form>';
		echo '<a href="'.$PHP_SELF.'?map='.$id.'">Вернуться к карте</a><br/>';
		echo '<a href="'.$PHP_SELF.'">К выбору карт</a><br/>';
		exit;
		}
	if(empty($name)) exit('Введите название карты!');
	if($mir == 1) $p = 1; else $p = 0;
	$q = $db->query("update `map` set name='".$db->real_escape_string($name)."' where id='{$id}' limit 1;");
	echo 'Карта отредактирована. <a href="'.$PHP_SELF.'?map='.$id.'">Перейти</a>';
	exit;
break;

case 'del':
	$id = intval($id);
	if($id <= 0) exit('Неверный ИД карты!');
	$q = $db->query("select * from `map` where id={$id};");
	if($q->num_rows == 0) exit('Карты с таким ИД не существует!');
	$a = $q->fetch_assoc();
	if(empty($ok))
		{
		echo 'Вы хотите удалить карту "'.$a['name'].'"?<br/><br/>';
		echo '<a href="'.$PHP_SELF.'?mod=del&id='.$id.'&ok=1">Удалить</a><br/><br/>';
		echo '<a href="'.$PHP_SELF.'">К списку карт</a>';
		exit;
		}
	$q = $db->query("delete from `map` where id={$id} limit 1;");
	$q = $db->query("delete from `loc` where map_id={$id};");
	exit('Карта "'.$a['name'].'" полностью удалена.<br/><a href="'.$PHP_SELF.'">К списку карт</a>');
break;

case 'edit':
	echo '<a href="'.$PHP_SELF.'">К списку карт</a>';
	echo ' / <a href="'.$PHP_SELF.'?map='.$map.'">К карте</a>';
	echo '<br/>';
	if(empty($X) or empty($Y)) exit('Неверные координаты!');
	$id = intval($id);
	$map = intval($map);
	if($id < 0) exit('Неверный ИД локации!');
	if($map <= 0) exit('Неверный ИД карты!');
	$q = $db->query("select * from `map` where id={$map} limit 1;");
	if($q->num_rows == 0) exit('Нет такой карты.');
	$a = $q->fetch_assoc();
	$karta['name'] = ''; // заготовки
	$karta['info'] = ''; // заготовки
	$karta['W'] = 0; // заготовки
	$karta['N'] = 0; // заготовки
	$karta['S'] = 0; // заготовки
	$karta['E'] = 0; // заготовки
	$karta['way'] = ''; // заготовки
	if($X > $a['x'] or $X < 1) exit('Неправильная координата X');
	if($Y > $a['y'] or $Y < 1) exit('Неправильная координата Y');
	if($id > 0)
		{
		$q = $db->query("select * from `loc` where map_id='{$map}' and X='{$X}' and Y='{$Y}' and id='{$id}' limit 1;");
		if($q->num_rows == 0) exit('Нет такой локации.');
		$a = $q->fetch_assoc();
		$karta['name'] = $a['name'];
		$karta['info'] = $a['info'];
		$karta['W'] = $a['W'];
		$karta['N'] = $a['N'];
		$karta['S'] = $a['S'];
		$karta['E'] = $a['E'];
		$karta['way'] = '.png';
		if(!empty($a['W'])) $karta['way'] = 'W'.$karta['way'];
		if(!empty($a['E'])) $karta['way'] = 'E'.$karta['way'];
		if(!empty($a['S'])) $karta['way'] = 'S'.$karta['way'];
		if(!empty($a['N'])) $karta['way'] = 'N'.$karta['way'];
		}
	if(empty($ok))
		{
		echo '<form action="'.$PHP_SELF.'?mod=edit&map='.$map.'&x='.$X.'&y='.$Y.'&id='.$id.'&ok=1" method="POST">';
		echo '<b>X: '.$X.', Y: '.$Y.'</b><br/>';
		//echo 'Название локации:<br/><input type="text" name="name" value="'.$karta['name'].'"/><br/>';
		//echo 'Описание локации:<br/><textarea name="info" rows="10" style="width:80%;">'.$karta['info'].'</textarea><br/>';
		//echo 'Мирная:<br/><select name="mir"><option selected value="1">Да</option><option value="2">Нет</option></select><br/>';
		if(!empty($karta['way'])) echo 'Текущая картинка путей: <img src="pic/'.$karta['way'].'" width="24" height="24"/><br/>';
		echo 'Картинка путей:<br/>';
		echo '<input type="radio" name="way" value="E"/><img src="pic/E.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="N"/><img src="pic/N.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="W"/><img src="pic/W.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="S"/><img src="pic/S.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="EW"/><img src="pic/EW.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="NE"/><img src="pic/NE.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="NS"/><img src="pic/NS.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="NW"/><img src="pic/NW.png" width="24" height="24"/><br/>';
		echo '<input type="radio" name="way" value="SE"/><img src="pic/SE.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="SW"/><img src="pic/SW.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="NEW"/><img src="pic/NEW.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="NSE"/><img src="pic/NSE.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="NSW"/><img src="pic/NSW.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="SEW"/><img src="pic/SEW.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="NSEW"/><img src="pic/NSEW.png" width="24" height="24"/> | ';
		echo '<input type="radio" name="way" value="no"/><img src="pic/0.png" width="24" height="24"/><br/>';
		echo '<input type="submit" value="Сохранить"/></form>';
		exit;
		}
	//$name = $db->real_escape_string($name);
	//if(empty($name)) exit('Вы не ввели название!');
	//$info = $db->real_escape_string($info);
	//if(empty($info)) exit('Вы не ввели описание!');
	if(substr_count($way, 'N')) $N = 1; else $N = 0;
	if(substr_count($way, 'S')) $S = 1; else $S = 0;
	if(substr_count($way, 'E')) $E = 1; else $E = 0;
	if(substr_count($way, 'W')) $W = 1; else $W = 0;
	//if($mir == 1) $p = 1; else $p = 0;
	//echo $N.'|'.$S.'|'.$E.'|'.$W.'|'.$way;
	//exit;
	if($id > 0) // если редактировали имеющуюся локу
		{
		if($N == 1 or $S == 1 or $E == 1 or $W == 1) // если есть картинка
			{
			$q = $db->query("update `loc` set
							name='',
							info='',
							N={$N},
							S={$S},
							E={$E},
							W={$W},
							peace='0'
							where map_id='{$map}'
							and X='{$X}'
							and Y='{$Y}'
							and id='{$id}' limit 1;");
			}
		else // если без картинки - удаляем локу
			{
			$q = $db->query("delete from `loc` where map_id='{$map}'
							and X='{$X}'
							and Y='{$Y}'
							and id='{$id}' limit 1;");
			}
		}
	else // если новая лока, добавим (если есть картинка)
		{
		if($N == 1 or $S == 1 or $E == 1 or $W == 1)
			{
			$q = $db->query("insert into `loc` values(0,'{$map}','','{$N}','{$S}','{$W}','{$E}','{$X}','{$Y}','0','');") or exit('Error');
			}
		}
	echo '<script>window.location="'.$PHP_SELF.'?map='.$map.'"</script>';
	//header("location: ".$PHP_SELF."?map=".$map);
	exit;
break;
endswitch;
exit;
/*
0.0.1 - Первая версия
0.0.2 - Header('location: '); заменен на яваскрипт, ибо не корректно работал.
0.0.3 - добавлен автоустановщик таблиц
*/
?>
