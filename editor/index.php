<?php
/**
 * Редактор карт и локаций.
 * PHP 8.2-совместимая версия.
 *
 * ВНИМАНИЕ: локальный инструмент. В прод не выкладывать.
 */

require_once __DIR__ . '/DBC.php';

// Убрал echo '<title>' — чтобы header() работал.
$PHP_SELF = 'index.php';

$mod   = isset($_REQUEST['mod'])   ? $_REQUEST['mod'] : '';
$name  = isset($_REQUEST['name'])  ? (string)$_REQUEST['name'] : '';
$info  = isset($_REQUEST['info'])  ? (string)$_REQUEST['info'] : '';
$way   = isset($_REQUEST['way'])   ? (string)$_REQUEST['way'] : '';
$id    = isset($_REQUEST['id'])    ? (int)$_REQUEST['id'] : 0;
$ok    = isset($_REQUEST['ok'])    ? $_REQUEST['ok'] : 0;
$X     = isset($_REQUEST['x'])     ? (int)$_REQUEST['x'] : 0;
$Y     = isset($_REQUEST['y'])     ? (int)$_REQUEST['y'] : 0;
$mir   = isset($_REQUEST['mir'])   ? (int)$_REQUEST['mir'] : 0;
$map   = isset($_REQUEST['map'])   ? (int)$_REQUEST['map'] : 0;

// Автоустановка таблиц
$db->query("CREATE TABLE IF NOT EXISTS `loc` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;");

$db->query("CREATE TABLE IF NOT EXISTS `map` (
`id` int(12) NOT NULL AUTO_INCREMENT,
`x` int(12) NOT NULL DEFAULT '1',
`y` int(12) NOT NULL DEFAULT '1',
`name` text NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;");

switch ($mod) {
    default:
        if (empty($map)) {
            echo 'Выберите карту для редактирования (<a href="' . $PHP_SELF . '?mod=new">создать новую</a>):<br/>';
            $q = $db->query("SELECT `name`, `id` FROM `map`;");
            if (!$q || $q->num_rows == 0) exit('Нет ни одной карты.');
            while ($a = $q->fetch_assoc()) {
                echo '<a href="' . $PHP_SELF . '?map=' . $a['id'] . '">' . htmlspecialchars($a['name'], ENT_QUOTES, 'UTF-8') . '</a> <a href="' . $PHP_SELF . '?mod=del&id=' . $a['id'] . '">[x]</a><br/>';
            }
            exit;
        }
        echo '<title>Редактор карт v0.0.4</title>';
        echo '<form action="index.php?map=' . $map . '" method="POST">';
        echo '<a href="' . $PHP_SELF . '?mod=del&id=' . $map . '">Удалить карту</a> / ';
        echo '<a href="' . $PHP_SELF . '?mod=mapedit&id=' . $map . '">Настройки карты</a> / ';
        echo '<a href="' . $PHP_SELF . '">К выбору карт</a><hr/>';
        echo 'Добавить: <input type="submit" value="Строку сверху" name="addup"/>';
        echo '<input type="submit" value="Строку снизу" name="adddown"/>';
        echo '<input type="submit" value="Столбец слева" name="addleft"/>';
        echo '<input type="submit" value="Столбец справа" name="addright"/><br/>';
        echo 'Удалить: <input type="submit" value="Строку сверху" name="delup"/>';
        echo '<input type="submit" value="Строку снизу" name="deldown"/>';
        echo '<input type="submit" value="Столбец слева" name="delleft"/>';
        echo '<input type="submit" value="Столбец справа" name="delright"/>';
        echo '</form>';

        $q = $db->query("SELECT * FROM `map` WHERE `id` = '{$map}' LIMIT 1;");
        if (!$q || $q->num_rows == 0) exit('Карта не найдена.');
        $a = $q->fetch_assoc();
        $qq = $db->query("SELECT COUNT(*) AS `c` FROM `loc` WHERE `map_id` = {$map};");
        $c = $qq ? (int)$qq->fetch_assoc()['c'] : 0;
        echo 'Название: <b>' . htmlspecialchars($a['name'], ENT_QUOTES, 'UTF-8') . '</b>, Размер: ' . $a['x'] . ':' . $a['y'] . ', ' . $c . ' локаций<hr/>';

        $x1 = 1; $y1 = 1; $x2 = (int)$a['x']; $y2 = (int)$a['y'];

        if (isset($_REQUEST['addup']) && $a['y'] < 200) {
            $db->query("UPDATE `map` SET `y` = `y` + 1 WHERE `id` = {$map};");
            header('Location: ' . $PHP_SELF . '?map=' . $map);
            exit;
        }
        if (isset($_REQUEST['addright']) && $a['x'] < 200) {
            $db->query("UPDATE `map` SET `x` = `x` + 1 WHERE `id` = {$map};");
            header('Location: ' . $PHP_SELF . '?map=' . $map);
            exit;
        }
        if (isset($_REQUEST['adddown']) && $a['y'] < 200) {
            $db->query("UPDATE `map` SET `y` = `y` + 1 WHERE `id` = {$map};");
            // ИСПРАВЛЕНО: добавил WHERE map_id
            $db->query("UPDATE `loc` SET `Y` = `Y` + 1 WHERE `map_id` = {$map};");
            header('Location: ' . $PHP_SELF . '?map=' . $map);
            exit;
        }
        if (isset($_REQUEST['addleft']) && $a['x'] < 200) {
            $db->query("UPDATE `map` SET `x` = `x` + 1 WHERE `id` = {$map};");
            // ИСПРАВЛЕНО: добавил WHERE map_id
            $db->query("UPDATE `loc` SET `X` = `X` + 1 WHERE `map_id` = {$map};");
            header('Location: ' . $PHP_SELF . '?map=' . $map);
            exit;
        }
        if (isset($_REQUEST['delup']) && $a['y'] > 1) {
            $db->query("UPDATE `map` SET `y` = `y` - 1 WHERE `id` = {$map};");
            $db->query("DELETE FROM `loc` WHERE `Y` >= " . (int)$a['y'] . " AND `map_id` = {$map};");
            header('Location: ' . $PHP_SELF . '?map=' . $map);
            exit;
        }
        if (isset($_REQUEST['delright']) && $a['x'] > 1) {
            $db->query("UPDATE `map` SET `x` = `x` - 1 WHERE `id` = {$map};");
            $db->query("DELETE FROM `loc` WHERE `X` >= " . (int)$a['x'] . " AND `map_id` = {$map};");
            header('Location: ' . $PHP_SELF . '?map=' . $map);
            exit;
        }
        if (isset($_REQUEST['deldown']) && $a['y'] > 1) {
            $db->query("UPDATE `map` SET `y` = `y` - 1 WHERE `id` = {$map};");
            $db->query("DELETE FROM `loc` WHERE `Y` <= 1 AND `map_id` = {$map};");
            $db->query("UPDATE `loc` SET `Y` = `Y` - 1 WHERE `map_id` = {$map};");
            header('Location: ' . $PHP_SELF . '?map=' . $map);
            exit;
        }
        if (isset($_REQUEST['delleft']) && $a['x'] > 1) {
            $db->query("UPDATE `map` SET `x` = `x` - 1 WHERE `id` = {$map};");
            $db->query("DELETE FROM `loc` WHERE `X` <= 1 AND `map_id` = {$map};");
            $db->query("UPDATE `loc` SET `X` = `X` - 1 WHERE `map_id` = {$map};");
            header('Location: ' . $PHP_SELF . '?map=' . $map);
            exit;
        }

        echo '<table name="loc" border="1">';
        for ($i = $y2; $i >= $y1; $i--) {
            echo '<tr>';
            for ($j = $x1; $j <= $x2; $j++) {
                $q = $db->query("SELECT `id`, `N`, `S`, `W`, `E` FROM `loc` WHERE `X` = {$j} AND `Y` = {$i} AND `map_id` = {$map} LIMIT 1;");
                $a2 = $q ? $q->fetch_assoc() : null;
                if (!$a2) {
                    $locId = 0;
                    $nameImg = '0.png';
                } else {
                    $locId = (int)$a2['id'];
                    $nameImg = '.png';
                    if (!empty($a2['W'])) $nameImg = 'W' . $nameImg;
                    if (!empty($a2['E'])) $nameImg = 'E' . $nameImg;
                    if (!empty($a2['S'])) $nameImg = 'S' . $nameImg;
                    if (!empty($a2['N'])) $nameImg = 'N' . $nameImg;
                }
                echo '<td><a href="' . $PHP_SELF . '?mod=edit&map=' . $map . '&id=' . $locId . '&x=' . $j . '&y=' . $i . '"><img src="pic/' . $nameImg . '" width="24" height="24"/></a></td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        break;

    case 'new':
        if (empty($ok)) {
            echo '<title>Редактор карт</title>';
            echo '<form action="' . $PHP_SELF . '?mod=new&ok=1" method="POST">';
            echo 'Создание новой карты<br/><br/>';
            echo 'Название:<br/><input type="text" name="name"/><br/>';
            echo 'Ширина:<br/><input type="text" name="x"/><br/>';
            echo 'Высота:<br/><input type="text" name="y"/><br/>';
            echo '<input type="submit" value="Далее"/>';
            echo '</form>';
            echo '<a href="' . $PHP_SELF . '">К выбору карт</a><br/>';
            exit;
        }
        if (empty($name)) exit('Введите название карты!');
        if (empty($X) || $X < 1 || $X > 200) exit('Неверная ширина, можно от 1 до 200!');
        if (empty($Y) || $Y < 1 || $Y > 200) exit('Неверная высота, можно от 1 до 200!');
        $name_esc = $db->real_escape_string($name);
        $db->query("INSERT INTO `map` VALUES (0, {$X}, {$Y}, '{$name_esc}');");
        echo '<title>Редактор карт</title>';
        echo 'Карта создана. <a href="' . $PHP_SELF . '?map=' . $db->insert_id() . '">Перейти</a>';
        exit;

    case 'mapedit':
        if ($id <= 0) exit('Неверный ID карты');
        $q = $db->query("SELECT * FROM `map` WHERE `id` = '{$id}' LIMIT 1;");
        if (!$q || $q->num_rows == 0) exit('Нет такой карты');
        $a = $q->fetch_assoc();
        if (empty($ok)) {
            echo '<title>Редактор карт</title>';
            echo '<form action="' . $PHP_SELF . '?mod=mapedit&ok=1&id=' . $id . '" method="POST">';
            echo 'Редактирование карты<br/><br/><br/>';
            echo 'Название:<br/><input type="text" name="name" value="' . htmlspecialchars($a['name'], ENT_QUOTES, 'UTF-8') . '"/><br/>';
            echo '<input type="submit" value="Сохранить"/>';
            echo '</form>';
            echo '<a href="' . $PHP_SELF . '?map=' . $id . '">Вернуться к карте</a><br/>';
            echo '<a href="' . $PHP_SELF . '">К выбору карт</a><br/>';
            exit;
        }
        if (empty($name)) exit('Введите название карты!');
        $name_esc = $db->real_escape_string($name);
        $db->query("UPDATE `map` SET `name` = '{$name_esc}' WHERE `id` = '{$id}' LIMIT 1;");
        echo '<title>Редактор карт</title>';
        echo 'Карта отредактирована. <a href="' . $PHP_SELF . '?map=' . $id . '">Перейти</a>';
        exit;

    case 'del':
        if ($id <= 0) exit('Неверный ИД карты!');
        $q = $db->query("SELECT * FROM `map` WHERE `id` = {$id};");
        if (!$q || $q->num_rows == 0) exit('Карты с таким ИД не существует!');
        $a = $q->fetch_assoc();
        if (empty($ok)) {
            echo '<title>Редактор карт</title>';
            echo 'Вы хотите удалить карту "' . htmlspecialchars($a['name'], ENT_QUOTES, 'UTF-8') . '"?<br/><br/>';
            echo '<a href="' . $PHP_SELF . '?mod=del&id=' . $id . '&ok=1">Удалить</a><br/><br/>';
            echo '<a href="' . $PHP_SELF . '">К списку карт</a>';
            exit;
        }
        $db->query("DELETE FROM `map` WHERE `id` = {$id} LIMIT 1;");
        $db->query("DELETE FROM `loc` WHERE `map_id` = {$id};");
        echo '<title>Редактор карт</title>';
        exit('Карта "' . htmlspecialchars($a['name'], ENT_QUOTES, 'UTF-8') . '" полностью удалена.<br/><a href="' . $PHP_SELF . '">К списку карт</a>');

    case 'edit':
        echo '<title>Редактор карт</title>';
        echo '<a href="' . $PHP_SELF . '">К списку карт</a>';
        echo ' / <a href="' . $PHP_SELF . '?map=' . $map . '">К карте</a>';
        echo '<br/>';
        if (empty($X) || empty($Y)) exit('Неверные координаты!');
        if ($id < 0) exit('Неверный ИД локации!');
        if ($map <= 0) exit('Неверный ИД карты!');
        $q = $db->query("SELECT * FROM `map` WHERE `id` = {$map} LIMIT 1;");
        if (!$q || $q->num_rows == 0) exit('Нет такой карты.');
        $a = $q->fetch_assoc();
        $karta = ['name' => '', 'info' => '', 'W' => 0, 'N' => 0, 'S' => 0, 'E' => 0, 'way' => ''];
        if ($X > $a['x'] || $X < 1) exit('Неправильная координата X');
        if ($Y > $a['y'] || $Y < 1) exit('Неправильная координата Y');
        if ($id > 0) {
            $q = $db->query("SELECT * FROM `loc` WHERE `map_id` = '{$map}' AND `X` = '{$X}' AND `Y` = '{$Y}' AND `id` = '{$id}' LIMIT 1;");
            if (!$q || $q->num_rows == 0) exit('Нет такой локации.');
            $a = $q->fetch_assoc();
            $karta['name'] = $a['name'];
            $karta['info'] = $a['info'];
            $karta['W'] = (int)$a['W'];
            $karta['N'] = (int)$a['N'];
            $karta['S'] = (int)$a['S'];
            $karta['E'] = (int)$a['E'];
            $karta['way'] = '.png';
            if (!empty($a['W'])) $karta['way'] = 'W' . $karta['way'];
            if (!empty($a['E'])) $karta['way'] = 'E' . $karta['way'];
            if (!empty($a['S'])) $karta['way'] = 'S' . $karta['way'];
            if (!empty($a['N'])) $karta['way'] = 'N' . $karta['way'];
        }
        if (empty($ok)) {
            echo '<form action="' . $PHP_SELF . '?mod=edit&map=' . $map . '&x=' . $X . '&y=' . $Y . '&id=' . $id . '&ok=1" method="POST">';
            echo '<b>X: ' . $X . ', Y: ' . $Y . '</b><br/>';
            if (!empty($karta['way'])) echo 'Текущая картинка путей: <img src="pic/' . $karta['way'] . '" width="24" height="24"/><br/>';
            echo 'Картинка путей:<br/>';
            $ways = ['E', 'N', 'W', 'S', 'EW', 'NE', 'NS', 'NW', 'SE', 'SW', 'NEW', 'NSE', 'NSW', 'SEW', 'NSEW'];
            foreach ($ways as $w) {
                echo '<input type="radio" name="way" value="' . $w . '"/><img src="pic/' . $w . '.png" width="24" height="24"/> ';
            }
            echo '<input type="radio" name="way" value="no"/><img src="pic/0.png" width="24" height="24"/><br/>';
            echo '<input type="submit" value="Сохранить"/></form>';
            exit;
        }
        $N = substr_count($way, 'N') ? 1 : 0;
        $S = substr_count($way, 'S') ? 1 : 0;
        $E = substr_count($way, 'E') ? 1 : 0;
        $W = substr_count($way, 'W') ? 1 : 0;
        if ($id > 0) {
            if ($N == 1 || $S == 1 || $E == 1 || $W == 1) {
                $db->query("UPDATE `loc` SET `name` = '', `info` = '', `N` = {$N}, `S` = {$S}, `E` = {$E}, `W` = {$W}, `peace` = 0 WHERE `map_id` = '{$map}' AND `X` = '{$X}' AND `Y` = '{$Y}' AND `id` = '{$id}' LIMIT 1;");
            } else {
                $db->query("DELETE FROM `loc` WHERE `map_id` = '{$map}' AND `X` = '{$X}' AND `Y` = '{$Y}' AND `id` = '{$id}' LIMIT 1;");
            }
        } else {
            if ($N == 1 || $S == 1 || $E == 1 || $W == 1) {
                $db->query("INSERT INTO `loc` VALUES (0, '{$map}', '', '{$N}', '{$S}', '{$W}', '{$E}', '{$X}', '{$Y}', '0', '');");
            }
        }
        header('Location: ' . $PHP_SELF . '?map=' . $map);
        exit;
}

exit;
