<?php
/**
 * Админка.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО:
 *  - в case 'admin': дублировалось if ($num == 3), второе должно быть $num == 4
 *  - в case 'spam': $srok = 68400 * 30 (19 часов) → 86400 * 30 (30 дней)
 *  - (int) везде
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';

$mod    = isset($_REQUEST['mod'])    ? $_REQUEST['mod'] : '';
$mid    = isset($_REQUEST['mid'])    ? (int)$_REQUEST['mid'] : 0;
$go     = isset($_REQUEST['go'])     ? $_REQUEST['go'] : '';
$ok     = isset($_REQUEST['ok'])     ? $_REQUEST['ok'] : '';
$lgn    = isset($_REQUEST['lgn'])    ? ekr($_REQUEST['lgn']) : '';
$lgn2   = isset($_REQUEST['lgn2'])   ? ekr($_REQUEST['lgn2']) : '';
$num    = isset($_REQUEST['num'])    ? (int)$_REQUEST['num'] : 0;
$lvl    = isset($_REQUEST['lvl'])    ? (int)$_REQUEST['lvl'] : 0;
$mysql  = isset($_REQUEST['mysql'])  ? $_REQUEST['mysql'] : '';
$start  = isset($_REQUEST['start'])  ? (int)$_REQUEST['start'] : 0;

if ($f['admin'] < 1) msg2('Недоступно для вас', 1);

// Чистка старых логов
$timer = $t - 604800;
$db->query("DELETE FROM `battle` WHERE `boistart` < '{$timer}';");
$db->query("DELETE FROM `battlelog` WHERE `timelog` < '{$timer}';");
$db->query("DELETE FROM `combat` WHERE `time` < '{$timer}';");
$timer = $t - 7200;
$db->query("UPDATE `users` SET `status` = 0, `hpnow` = 0 - 100000 * `hpmax` WHERE `status` = 1 AND `lastdate` < '{$timer}';");
require_once __DIR__ . '/inc/hpstring.php';

switch ($mod) {
    default:
        if (3 <= $f['admin']) {
            msg2('Функции админа:');
            knopka('adm.php?mod=addnews', 'Добавить новость');
            knopka('adm.php?mod=stopmsg', 'MSG: STOP');
            knopka('adm.php?mod=addklan', 'Добавить КЛАН');
            knopka('adm.php?mod=delklan', 'Удалить КЛАН');
            knopka('adm.php?mod=givemoney&lgn=' . $lgn, 'Дать монет персу');
            knopka('adm.php?mod=giveruda&lgn=' . $lgn, 'Руда');
            knopka('adm.php?mod=pass&lgn=' . $lgn, 'Сменить пасс');
            knopka('adm.php?mod=login&lgn=' . $lgn, 'Сменить логин');
            knopka('adm.php?mod=listitem', 'Все вещи списком');
            knopka('adm.php?mod=vip', 'V.I.P.');
            knopka('adm.php?mod=add_item', 'Добавить вещь в базу');
            knopka('adm.php?mod=up', 'Апнуться');
            knopka('adm.php?mod=null', 'Обнулиться');
            knopka('adm.php?mod=mysql', 'MySQL');
            knopka('adm.php?mod=mail', 'Mail');
            knopka('adm.php?mod=locedit', 'Редактировать локацию (id: ' . $f['loc'] . ')');
            knopka('adm.php?mod=lookpers&lgn=' . $lgn, 'Смотреть массив перса');
            knopka('adm.php?mod=lookbot', 'Лицензия бота');
        }
        if (2 <= $f['admin']) {
            msg2('Функции супермодера:');
            knopka('adm.php?mod=lookblok', 'Список кто в блоке');
            knopka('adm.php?mod=othelit', 'Вылечить всех');
            knopka('adm.php?mod=admin&lgn=' . $lgn, 'Управление статусами');
            knopka('adm.php?mod=spam', 'Рассылка сообщения');
            knopka('adm.php?mod=brak', 'Регистрация брака');
        }
        if (1 <= $f['admin']) {
            msg2('Функции модера:');
            knopka('adm.php?mod=logpered&lgn=' . $lgn, 'Лог передач');
            knopka('adm.php?mod=logklan', 'Лог кланов');
            knopka('adm.php?mod=lastonl', 'Дата захода (все)');
            knopka('adm.php?mod=ipsoft&lgn=' . $lgn, 'IP/SOFT (login)');
            knopka('adm.php?mod=chatunban&lgn=' . $lgn, 'Снять молчу');
            knopka('adm.php?mod=blok&lgn=' . $lgn, 'Заблокировать перса');
            knopka('adm.php?mod=battle2&lgn=' . $lgn, 'Просмотр боев (все)');
            knopka('adm.php?mod=battle&lgn=' . $lgn, 'Просмотр боев (login)');
            knopka('adm.php?mod=sovp', 'Совпадения паролей');
            knopka('adm.php?mod=sovpip', 'Совпадения IP (последний заход)');
            knopka('adm.php?mod=sovpipall', 'Совпадения IP (за всё время)');
            knopka('adm.php?mod=sovpsoftall', 'Совпадения SOFT (за всё время)');
            knopka('adm.php?mod=sovpall', 'Совпадения IP/SOFT (за всё время)');
            knopka('adm.php?mod=logipsoft', 'Лог IP/SOFT');
            knopka('adm.php?mod=spam', 'Рассылка сообщения (30 дн)');
        }
        fin();
        break;

    case 'lookbot':
        require_once __DIR__ . '/_sfdgh626xfy2j/class/DBCC.php';
        echo '<div class="board" style="text-align:left">';
        echo 'Список текущих лицензий</div>';
        $q = $db->query("SELECT * FROM `users` WHERE `time` = -1 OR `time` > UNIX_TIMESTAMP() ORDER BY `time`;");
        $c = 0;
        if ($q) {
            while ($a = $q->fetch_assoc()) {
                $c++;
                echo '<div class="board2" style="text-align:left">';
                echo $c . '. ' . $a['login'];
                if ($a['time'] == -1) echo ' (Постоянная)';
                else echo ' (До ' . date('d.m.Y H:i:s', (int)$a['time']) . ')';
                echo '</div>';
            }
        }
        fin();
        break;

    case 'addnews':
        if ($f['admin'] < 3) msg2('Вы не можете добавлять новости', 1);
        if (empty($go)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=addnews&go=1" method="POST">
            Заголовок: (2-50 символов)<br/>
            <input type="text" name="title" size="20" maxlength="50"/><br/>
            Текст новости: (5-5000 символов)<br/>
            <textarea name="news" cols=20 rows=5 maxlength=5000></textarea><br/>
            <input type="submit" value="Отправить"/></form>';
            echo '</div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $news = isset($_REQUEST['news']) ? ekr($_REQUEST['news']) : '';
        if (empty($news) || mb_strlen($news, 'UTF-8') < 5 || mb_strlen($news, 'UTF-8') > 5000) msg2('Неверно заполнено поле для новости', 1);
        $title = isset($_REQUEST['title']) ? ekr($_REQUEST['title']) : '';
        if (empty($title) || mb_strlen($title, 'UTF-8') < 2 || mb_strlen($title, 'UTF-8') > 50) msg2('Неверно заполнено поле для заголовка', 1);
        $login_esc = $db->real_escape_string($f['login']);
        $db->query("INSERT INTO `news` VALUES (0, '{$login_esc}', '" . $db->real_escape_string($title) . "', '" . $db->real_escape_string($news) . "', '{$t}');");
        $db->query("UPDATE `users` SET `newsdate` = '{$t}';");
        msg2('Новость успешно добавлена!');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'stopmsg':
        if ($f['admin'] < 3) msg2('Вы не можете останавливать игру', 1);
        if (empty($go)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=stopmsg&go=1" method="POST">
            Текст сообщения: (0-100 символов, оставить пустым для запуска игры)<br/>
            <textarea name="news" cols=20 rows=5 maxlength=100></textarea><br/>
            <input type="submit" value="Отправить"/></form>';
            echo '</div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $news = isset($_REQUEST['news']) ? ekr($_REQUEST['news']) : '';
        if (mb_strlen($news, 'UTF-8') > 100) $news = mb_substr($news, 0, 100, 'UTF-8');
        if (empty($news)) $db->query("UPDATE `settings` SET `mess` = '' WHERE `id` = 1;");
        else $db->query("UPDATE `settings` SET `mess` = '" . $db->real_escape_string($news) . "' WHERE `id` = 1;");
        msg2('Стоп сообщение успешно добавлено!');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'addklan':
        if ($f['admin'] < 3) msg2('Вы не можете создавать кланы', 1);
        if (empty($lgn) || empty($lgn2)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=addklan" method="POST">';
            echo 'Название будущего клана:<br/>';
            echo '<input type="text" name="lgn"/><br/>';
            echo 'Ник главы:<br/>';
            echo '<input type="text" name="lgn2"/><br/>';
            echo '<input type="submit" value="Далее"/></form>';
            echo '</div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        if (preg_match('/[^a-zA-Zа-яА-ЯёЁ ]/u', $lgn) || mb_strlen($lgn, 'UTF-8') > 40) msg2('Неверное название клана', 1);
        $TestLGN = get_login($lgn2);
        $lgn2 = $TestLGN['login'];
        if (!empty($TestLGN['klan'])) msg2('Этот персонаж не может быть главой клана, так как состоит в другом клане.', 1);
        $lgn_esc = $db->real_escape_string($lgn);
        $q = $db->query("SELECT * FROM `klans` WHERE `name` = '{$lgn_esc}' LIMIT 1;");
        if ($q && $q->num_rows > 0) msg2('Такой клан уже зарегистрирован', 1);
        $db->query("INSERT INTO `klans` (`id`, `name`, `datereg`) VALUES (0, '{$lgn_esc}', '{$t}');");
        $db->query("UPDATE `users` SET `klan` = '{$lgn_esc}', `klan_status` = 3, `klan_time` = '{$t}' WHERE `login` = '" . $db->real_escape_string($lgn2) . "' LIMIT 1;");
        msg2('Клан ' . $lgn . ' успешно создан. Глава ' . $lgn2 . '.');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'delklan':
        if ($f['admin'] < 3) msg2('Вы не можете удалять кланы', 1);
        if (empty($num)) {
            $q = $db->query("SELECT `id`, `name` FROM `klans` ORDER BY `id` ASC;");
            if ($q) {
                while ($a = $q->fetch_assoc()) {
                    knopka('adm.php?mod=delklan&num=' . $a['id'], 'Удалить клан "' . $a['name'] . '"', 1);
                }
            }
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        if ($num <= 0) msg2('Неверный выбор клана', 1);
        $q = $db->query("SELECT `id`, `name` FROM `klans` WHERE `id` = '{$num}' LIMIT 1;");
        if (!$q || $q->num_rows == 0) msg2('Такой клан не зарегистрирован', 1);
        $a = $q->fetch_assoc();
        if (empty($ok)) {
            msg2('Вы хотите удалить клан "' . $a['name'] . '", продолжить?');
            knopka('adm.php?mod=delklan&num=' . $a['id'] . '&ok=1', 'Продолжить', 1);
            knopka('adm.php', 'Вернуться', 1);
            fin();
        }
        $name_esc = $db->real_escape_string($a['name']);
        $db->query("UPDATE `users` SET `klan` = '', `klan_status` = 0, `klan_time` = 0, `klan_invite` = '' WHERE `klan` = '{$name_esc}' OR `klan_invite` = '{$name_esc}';");
        $db->query("DELETE FROM `klans` WHERE `id` = '{$a['id']}' LIMIT 1;");
        $db->query("DELETE FROM `klan_log` WHERE `name` = '{$name_esc}';");
        $db->query("ALTER TABLE `klans` DROP `id`;");
        $db->query("ALTER TABLE `klans` ADD `id` INT(12) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;");
        msg2('Клан ' . $a['name'] . ' успешно удален.');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'lookpers':
        if (empty($lgn)) $lgn = $f['login'];
        $l = get_login($lgn);
        echo '<div class="board" style="text-align:left"><pre>';
        print_r($l);
        echo '</pre></div>';
        fin();
        break;

    case 'listitem':
        if ($f['admin'] < 3) msg2('Вы не можете просматривать список вещей', 1);
        $q = $db->query("SELECT MAX(`lvl`) AS `m` FROM `item`;");
        $max_lvl = $q ? (int)$q->fetch_assoc()['m'] : 1;
        if (empty($lvl)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=listitem" method="POST">
            Выберите уровень<br/>
            <select name="lvl">';
            for ($i = 1; $i <= $max_lvl; $i++) echo '<option value=' . $i . '>' . $i . '</option>';
            echo '<input type="submit" value="Далее"/></form>';
            echo '</div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        if ($lvl < 1) $lvl = 1;
        if ($lvl > $max_lvl) $lvl = $max_lvl;
        if (empty($go)) {
            $q = $db->query("SELECT * FROM `item` WHERE `lvl` = {$lvl} ORDER BY `id`;");
            if ($q) {
                while ($i = $q->fetch_assoc()) {
                    echo '<div class="board2" style="text-align:left">';
                    echo '<a href="shop.php?mod=iteminfa&iid=' . $i['id'] . '"><span style="color:' . $male . '">' . $i['name'] . ' (' . $i['lvl'] . ' ур.)</span></a>';
                    echo ' <a href="adm.php?mod=item&num=' . $i['id'] . '">[get]</a>';
                    echo ' <a href="adm.php?mod=listitem&num=' . $i['id'] . '&go=1&lvl=' . $lvl . '">[del]</a>';
                    echo ' <a href="adm.php?mod=listitem&num=' . $i['id'] . '&go=2&lvl=' . $lvl . '">[edit]</a>';
                    echo '</div>';
                }
            }
            fin();
        } elseif ($go == 1) {
            if ($num <= 0) msg2('Неверный ИД вещи.', 1);
            $q = $db->query("SELECT `name` FROM `item` WHERE `id` = '{$num}' LIMIT 1;");
            if (!$q || $q->num_rows == 0) msg2('Вы хотите удалить несуществующую вещь.', 1);
            $i = $q->fetch_assoc();
            if (empty($ok)) {
                msg2('Вы действительно хотите удалить ' . $i['name'] . ' из базы?');
                knopka('adm.php?mod=listitem&go=1&ok=1&lvl=' . $lvl . '&num=' . $num, 'Удалить безвозвратно', 1);
                knopka('adm.php?mod=listitem', 'Не удалять', 1);
                fin();
            }
            $db->query("DELETE FROM `invent` WHERE `ido` = '{$num}';");
            $db->query("DELETE FROM `item` WHERE `id` = '{$num}' LIMIT 1;");
            msg2('Готово. Вещь "' . $i['name'] . '" удалена из базы');
            knopka('adm.php', 'В админку', 1);
            fin();
        } elseif ($go == 2) {
            if ($num <= 0) msg2('Неверный ID вещи.', 1);
            $q = $db->query("SELECT * FROM `item` WHERE `id` = '{$num}' LIMIT 1;");
            if (!$q || $q->num_rows == 0) msg2('Вы хотите редактировать несуществующую вещь.', 1);
            $i = $q->fetch_assoc();
            if (empty($ok)) {
                echo '<div class="board" style="text-align:left">';
                echo '<form action="adm.php?mod=listitem&go=2&ok=1&lvl=' . $lvl . '&num=' . $num . '" method="POST">';
                echo 'ID: ' . $i['id'] . '<br/>';
                echo 'Название: <input type="text" name="name" value="' . $i['name'] . '"/><br/>';
                echo 'Уровень: <input type="text" name="lvl" value="' . $i['lvl'] . '"/><br/>';
                echo 'Цена: <input type="text" name="price" value="' . $i['price'] . '"/><br/>';
                echo 'Крит: <input type="text" name="krit" value="' . $i['krit'] . '"/><br/>';
                echo 'Уворот: <input type="text" name="uvorot" value="' . $i['uvorot'] . '"/><br/>';
                echo 'Урон: <input type="text" name="uron" value="' . $i['uron'] . '"/><br/>';
                echo 'Бронь: <input type="text" name="bron" value="' . $i['bron'] . '"/><br/>';
                echo 'HP: <input type="text" name="hp" value="' . $i['hp'] . '"/><br/>';
                echo 'Здоровье: <input type="text" name="zdor" value="' . $i['zdor'] . '"/><br/>';
                echo 'Сила: <input type="text" name="sila" value="' . $i['sila'] . '"/><br/>';
                echo 'Инта: <input type="text" name="inta" value="' . $i['inta'] . '"/><br/>';
                echo 'Ловка: <input type="text" name="lovka" value="' . $i['lovka'] . '"/><br/>';
                echo 'Интеллект: <input type="text" name="intel" value="' . $i['intel'] . '"/><br/>';
                echo 'Арт: <input type="text" name="art" value="' . $i['art'] . '"/><br/>';
                echo 'Описание: <input type="text" name="info" value="' . $i['info'] . '"/><br/>';
                echo 'Слот: <input type="text" name="equip" value="' . $i['equip'] . '"/><br/>';
                echo '<input type="submit" value="Изменить"/></form></div>';
                fin();
            }
            $name = ekr($_REQUEST['name'] ?? '');
            $price = (int)($_REQUEST['price'] ?? 0);
            if ($price < 0) msg2('Цена не может быть меньше 0', 1);
            $lvl_new = (int)($_REQUEST['lvl'] ?? 1);
            if ($lvl_new < 1) msg2('Уровень не может быть меньше 1', 1);
            $krit = (int)($_REQUEST['krit'] ?? 0);
            $uvorot = (int)($_REQUEST['uvorot'] ?? 0);
            $uron = (int)($_REQUEST['uron'] ?? 0);
            $bron = (int)($_REQUEST['bron'] ?? 0);
            $hp = (int)($_REQUEST['hp'] ?? 0);
            $zdor = (int)($_REQUEST['zdor'] ?? 0);
            if ($zdor < 0) msg2('Здоровье не может быть меньше 0', 1);
            $sila = (int)($_REQUEST['sila'] ?? 0);
            if ($sila < 0) msg2('Сила не может быть меньше 0', 1);
            $inta = (int)($_REQUEST['inta'] ?? 0);
            if ($inta < 0) msg2('Интуиция не может быть меньше 0', 1);
            $lovka = (int)($_REQUEST['lovka'] ?? 0);
            if ($lovka < 0) msg2('Ловкость не может быть меньше 0', 1);
            $intel = (int)($_REQUEST['intel'] ?? 0);
            if ($intel < 0) msg2('Интеллект не может быть меньше 0', 1);
            $art = ekr($_REQUEST['art'] ?? '');
            $info = ekr($_REQUEST['info'] ?? '');
            $equip = ekr($_REQUEST['equip'] ?? '');
            if (!empty($equip) && preg_match('/[^a-zA-Z0-9]/', $equip)) msg2('Нерно заполнен слот', 1);
            $db->query("UPDATE `item` SET `name` = '{$name}', `lvl` = {$lvl_new}, `price` = {$price}, `krit` = {$krit}, `uvorot` = {$uvorot}, `uron` = {$uron}, `bron` = {$bron}, `hp` = {$hp}, `zdor` = {$zdor}, `sila` = {$sila}, `inta` = {$inta}, `lovka` = {$lovka}, `intel` = {$intel}, `art` = '{$art}', `info` = '{$info}', `equip` = '{$equip}' WHERE `id` = " . (int)$i['id'] . " LIMIT 1;");
            msg2("Вещь " . $name . " успешно отредактирована.");
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        break;

    case 'add_item':
        if ($f['admin'] < 3) msg2('Вы не можете создавать вещи', 1);
        if (empty($ok)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=add_item&ok=1" method="POST">';
            echo 'Название: <input type="text" name="name" value=""/><br/>';
            echo 'Уровень: <input type="number" name="lvl" value="0"/><br/>';
            echo 'Цена: <input type="number" name="price" value="0"/><br/>';
            echo 'Крит: <input type="number" name="krit" value="0"/><br/>';
            echo 'Уворот: <input type="number" name="uvorot" value="0"/><br/>';
            echo 'Урон: <input type="number" name="uron" value="0"/><br/>';
            echo 'Бронь: <input type="number" name="bron" value="0"/><br/>';
            echo 'ХП: <input type="number" name="hp" value="0"/><br/>';
            echo 'Здоровье: <input type="number" name="zdor" value="0"/><br/>';
            echo 'Сила: <input type="number" name="sila" value="0"/><br/>';
            echo 'Инта: <input type="number" name="inta" value="0"/><br/>';
            echo 'Ловка: <input type="number" name="lovka" value="0"/><br/>';
            echo 'Интеллект: <input type="number" name="intel" value="0"/><br/>';
            echo 'Арт: <input type="text" name="art" value=""/><br/>';
            echo 'Описание: <input type="text" name="info" value=""/><br/>';
            echo 'Слот: <input type="text" name="equip" value=""/><br/>';
            echo '<input type="submit" value="Создать"/></form>';
            echo '</div>';
            knopka('adm.php', 'Админка', 1);
            fin();
        }
        $name = ekr($_REQUEST['name'] ?? '');
        $price = (int)($_REQUEST['price'] ?? 0);
        if ($price < 0) msg2('Цена не может быть меньше 0', 1);
        $lvl_new = (int)($_REQUEST['lvl'] ?? 1);
        if ($lvl_new < 1) msg2('Уровень не может быть меньше 1', 1);
        $krit = (int)($_REQUEST['krit'] ?? 0);
        $uvorot = (int)($_REQUEST['uvorot'] ?? 0);
        $uron = (int)($_REQUEST['uron'] ?? 0);
        $bron = (int)($_REQUEST['bron'] ?? 0);
        $hp = (int)($_REQUEST['hp'] ?? 0);
        $zdor = (int)($_REQUEST['zdor'] ?? 0);
        if ($zdor < 0) msg2('Здоровье не может быть меньше 0', 1);
        $sila = (int)($_REQUEST['sila'] ?? 0);
        if ($sila < 0) msg2('Сила не может быть меньше 0', 1);
        $inta = (int)($_REQUEST['inta'] ?? 0);
        if ($inta < 0) msg2('Интуиция не может быть меньше 0', 1);
        $lovka = (int)($_REQUEST['lovka'] ?? 0);
        if ($lovka < 0) msg2('Ловкость не может быть меньше 0', 1);
        $intel = (int)($_REQUEST['intel'] ?? 0);
        if ($intel < 0) msg2('Интеллект не может быть меньше 0', 1);
        $art = ekr($_REQUEST['art'] ?? '');
        $info = ekr($_REQUEST['info'] ?? '');
        $equip = ekr($_REQUEST['equip'] ?? '');
        if (!empty($equip) && preg_match('/[^a-zA-Z0-9]/', $equip)) msg2('Нерно заполнен слот', 1);
        $db->query("INSERT INTO `item` VALUES (0, '{$name}', {$lvl_new}, {$price}, {$krit}, {$uvorot}, {$uron}, {$bron}, {$hp}, {$zdor}, {$sila}, {$inta}, {$lovka}, {$intel}, '{$art}', '{$info}', '{$equip}');");
        msg2('Вещь ' . $name . ' успешно создана. (ID: ' . $db->insert_id() . ')');
        knopka('adm.php?mod=add_item', 'Продолжить', 1);
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'logipsoft':
        if ($f['admin'] < 1) msg2('Вы не можете просматривать лог IP/SOFT', 1);
        $numb = 10;
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `log_ipsoft`;");
        $all_log = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($all_log <= 0) msg('Лог IP/SOFT пуст!', 1);
        if ($start > (int)($all_log / $numb)) $start = (int)($all_log / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        $count = $limit;
        $q = $db->query("SELECT * FROM `log_ipsoft` LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($a = $q->fetch_assoc()) {
                $count++;
                echo '<div class="board2" style="text-align:left;">';
                echo $count . ') <b>' . date('d.m.Y H:i', (int)$a['date']) . '</b> - <a href="infa.php?mod=uzinfa&lgn=' . $a['login'] . '">' . $a['login'] . '</a>';
                echo '<br/>';
                echo $a['log'];
                echo '</div>';
            }
        }
        echo '<div class="board">';
        if ($start > 0) echo '<a href="adm.php?mod=logipsoft&start=' . ($start - 1) . '" class="navig"><-Назад</a>';
        else echo '<a href="#" class="navig"> <-Назад</a>';
        echo ' | ';
        if ($limit + $numb < $all_log) echo '<a href="adm.php?mod=logipsoft&start=' . ($start + 1) . '" class="navig" >Вперед-></a>';
        else echo ' <a href="#" class="navig"> Вперед-></a>';
        echo '</div>';
        fin();
        break;

    case 'logpered':
        if ($f['admin'] < 1) msg2('Вы не можете просматривать лог передач', 1);
        $numb = 100;
        if (empty($lgn)) {
            $q = $db->query("SELECT COUNT(*) AS `c` FROM `log_peredach`;");
            $all_log = $q ? (int)$q->fetch_assoc()['c'] : 0;
            if ($start > (int)($all_log / $numb)) $start = (int)($all_log / $numb);
            if ($start < 0) $start = 0;
            $limit = $start * $numb;
            $count = $limit;
            $q = $db->query("SELECT * FROM `log_peredach` ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
            if ($q) {
                while ($log = $q->fetch_assoc()) {
                    $count++;
                    echo '<div class="board2" style="text-align:left">' . $count . ') <a href="adm.php?mod=logpered&lgn=' . $log['login'] . '">[>>>]</a> ' . date('d.m.Y H:i', (int)$log['dateper']) . ' - ' . $log['log'] . '</div>';
                }
            }
            echo '<div class="board">';
            if ($start > 0) echo '<a href="adm.php?mod=logpered&start=' . ($start - 1) . '" class="navig"><-Назад</a>';
            else echo '<a href="#" class="navig"> <-Назад</a>';
            echo ' | ';
            if ($limit + $numb < $all_log) echo '<a href="adm.php?mod=logpered&start=' . ($start + 1) . '" class="navig" >Вперед-></a>';
            else echo ' <a href="#" class="navig"> Вперед-></a>';
            echo '</div>';
            fin();
        }
        $TestLGN = get_login($lgn);
        $lgn = $TestLGN['login'];
        msg2('Лог передач персонажа <a href="infa.php?mod=uzinfa&lgn=' . $lgn . '">' . $lgn . '</a>');
        $lgn_esc = $db->real_escape_string($lgn);
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `log_peredach` WHERE `login` = '{$lgn_esc}' OR `login_per` = '{$lgn_esc}';");
        $all_log = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($start > (int)($all_log / $numb)) $start = (int)($all_log / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        $count = $limit;
        $q = $db->query("SELECT * FROM `log_peredach` WHERE `login` = '{$lgn_esc}' OR `login_per` = '{$lgn_esc}' ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($log = $q->fetch_assoc()) {
                $count++;
                echo '<div class="board2" style="text-align:left">' . $count . ') ' . date('d.m.Y H:i', (int)$log['dateper']) . ' - ' . $log['log'] . '</div>';
            }
        }
        echo '<div class="board">';
        if ($start > 0) echo '<a href="adm.php?mod=logpered&lgn=' . $lgn . '&start=' . ($start - 1) . '" class="navig"><-Назад</a>';
        else echo '<a href="#" class="navig"> <-Назад</a>';
        echo ' | ';
        if ($limit + $numb < $all_log) echo '<a href="adm.php?mod=logpered&lgn=' . $lgn . '&start=' . ($start + 1) . '" class="navig" >Вперед-></a>';
        else echo ' <a href="#" class="navig"> Вперед-></a>';
        echo '</div>';
        fin();
        break;

    case 'battle':
        if ($f['admin'] < 1) msg2('Вы не можете просматривать лог боев', 1);
        $numb = 100;
        if (empty($lgn)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=battle" method="POST">';
            echo 'Введите логин:<br/>';
            echo '<input type="text" name="lgn"/><br/>';
            echo '<input type="submit" value="Go"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $TestLGN = get_login($lgn);
        msg2('Список боев персонажа <a href="infa.php?mod=uzinfa&lgn=' . $lgn . '">' . $lgn . '</a>');
        $lgn_esc = $db->real_escape_string($lgn);
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `battle` WHERE `login` = '{$lgn_esc}' OR `login2` = '{$lgn_esc}';");
        $all_log = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($start > (int)($all_log / $numb)) $start = (int)($all_log / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        $count = $limit;
        $q = $db->query("SELECT * FROM `battle` WHERE `login` = '{$lgn_esc}' OR `login2` = '{$lgn_esc}' ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($log = $q->fetch_assoc()) {
                $count++;
                knopka('adm.php?mod=lookbattle&num=' . $log['id'], $count . ' [' . date('d.m.Y H:i:s', (int)$log['boistart']) . ']: ' . $log['login'] . ' vs ' . $log['login2']);
            }
        }
        echo '<div class="board">';
        if ($start > 0) echo '<a href="adm.php?mod=battle&lgn=' . $lgn . '&start=' . ($start - 1) . '" class="navig"><-Назад</a>';
        else echo '<a href="#" class="navig"> <-Назад</a>';
        echo ' | ';
        if ($limit + $numb < $all_log) echo '<a href="adm.php?mod=battle&lgn=' . $lgn . '&start=' . ($start + 1) . '" class="navig" >Вперед-></a>';
        else echo ' <a href="#" class="navig"> Вперед-></a>';
        echo '</div>';
        fin();
        break;

    case 'battle2':
        if ($f['admin'] < 1) msg2('Вы не можете просматривать лог боев', 1);
        $numb = 100;
        msg2('Список всех боев');
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `battle`;");
        $all_log = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($start > (int)($all_log / $numb)) $start = (int)($all_log / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        $count = $limit;
        $q = $db->query("SELECT * FROM `battle` ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($log = $q->fetch_assoc()) {
                $count++;
                knopka('adm.php?mod=lookbattle&num=' . $log['id'], $count . ' [' . date('d.m.Y H:i:s', (int)$log['boistart']) . ']: ' . $log['login'] . ' vs ' . $log['login2']);
            }
        }
        echo '<div class="board">';
        if ($start > 0) echo '<a href="adm.php?mod=battle2&start=' . ($start - 1) . '" class="navig"><-Назад</a>';
        else echo '<a href="#" class="navig"> <-Назад</a>';
        echo ' | ';
        if ($limit + $numb < $all_log) echo '<a href="adm.php?mod=battle2&start=' . ($start + 1) . '" class="navig" >Вперед-></a>';
        else echo ' <a href="#" class="navig"> Вперед-></a>';
        echo '</div>';
        fin();
        break;

    case 'lookbattle':
        if ($f['admin'] < 1) msg2('Вы не можете просматривать лог боев', 1);
        if ($num <= 0) msg2('Бой не найден!', 1);
        echo '<div class="board" style="text-align:left">';
        $q = $db->query("SELECT * FROM `battle` WHERE `id` = '{$num}' LIMIT 1;");
        if (!$q || $q->num_rows == 0) msg2('Бой не найден!', 1);
        $q = $db->query("SELECT * FROM `battlelog` WHERE `boi_id` = '{$num}' ORDER BY `id`;");
        if ($q) {
            while ($log = $q->fetch_assoc()) {
                echo $log['log'];
            }
        }
        echo '</div>';
        fin();
        break;

    case 'logklan':
        if ($f['admin'] < 1) msg2('Вы не можете просматривать лог кланов', 1);
        $numb = 100;
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `klan_log`;");
        $all_log = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($start > (int)($all_log / $numb)) $start = (int)($all_log / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        $count = $limit;
        $q = $db->query("SELECT * FROM `klan_log` ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($log = $q->fetch_assoc()) {
                $count++;
                echo '<div class="board2" style="text-align:left">' . $count . ') ' . date('d.m.Y H:i', (int)$log['date']) . ' [' . $log['klan'] . '] - ' . $log['log'] . '</div>';
            }
        }
        echo '<div class="board">';
        if ($start > 0) echo '<a href="adm.php?mod=logklan&start=' . ($start - 1) . '" class="navig"><-Назад</a>';
        else echo '<a href="#" class="navig"> <-Назад</a>';
        echo ' | ';
        if ($limit + $numb < $all_log) echo '<a href="adm.php?mod=logklan&start=' . ($start + 1) . '" class="navig" >Вперед-></a>';
        else echo ' <a href="#" class="navig"> Вперед-></a>';
        echo '</div>';
        fin();
        break;

    case 'vip':
        if ($f['admin'] < 3) msg2('Вы не можете просматривать список VIP', 1);
        $q = $db->query("SELECT `lvl`, `login`, `lastdate`, `vip` FROM `users` WHERE `vip` > '{$t}' AND `autoreg` = 0 ORDER BY `vip` ASC;");
        $count = 0;
        if (!$q || $q->num_rows == 0) msg('Список пуст', 1);
        if ($q) {
            while ($Arr = $q->fetch_assoc()) {
                $count++;
                echo '<div class="board2" style="text-align:left">' . $count . ') <a href="infa.php?mod=uzinfa&lgn=' . $Arr['login'] . '">' . $Arr['login'] . ' [' . $Arr['lvl'] . ']</a> (до ' . date('d.m.Y H:i', (int)$Arr['vip']) . ')</div>';
            }
        }
        fin();
        break;

    case 'ipsoft':
        if ($f['admin'] < 1) msg2('Просмотр данных по заходам вам недоступен.', 1);
        if (empty($lgn)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=ipsoft" method="POST">';
            echo 'Введите логин:<br/>';
            echo '<input type="text" name="lgn"/><br/>';
            echo '<input type="submit" value="Go"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $TestLGN = get_login($lgn);
        $lgn = $TestLGN['login'];
        $count = 0;
        msg2('login: <a href="infa.php?mod=uzinfa&lgn=' . $lgn . '">' . $lgn . '</a><br/>Последний заход:');
        $ip = !empty($TestLGN['ip']) ? $TestLGN['ip'] : '127.0.0.1';
        echo '<div class="board2" style="text-align:left">IP: ' . $ip . '</div>';
        echo '<div class="board2" style="text-align:left">SOFT: ' . $TestLGN['soft'] . '</div>';
        echo '<div class="board2" style="text-align:left">HOST: ' . $TestLGN['host'] . '</div>';
        knopka('adm.php?mod=ipsoftall&lgn=' . $lgn, 'Все заходы ' . $lgn, 1);
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'ipsoftall':
        if ($f['admin'] < 1) msg2('Просмотр данных по заходам вам недоступен.', 1);
        $numb = 100;
        if (empty($lgn)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=ipsoftall" method="POST">';
            echo 'Введите логин:<br/>';
            echo '<input type="text" name="lgn"/><br/>';
            echo '<input type="submit" value="Go"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $TestLGN = get_login($lgn);
        $lgn = $TestLGN['login'];
        $count = 0;
        msg2('login: <a href="infa.php?mod=uzinfa&lgn=' . $lgn . '">' . $lgn . '</a><br/>Все заходы:');
        $lgn_esc = $db->real_escape_string($lgn);
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `ipsoft` WHERE `login` = '{$lgn_esc}';");
        $all_log = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($start > (int)($all_log / $numb)) $start = (int)($all_log / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        $count = $limit;
        $q = $db->query("SELECT * FROM `ipsoft` WHERE `login` = '{$lgn_esc}' ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($log = $q->fetch_assoc()) {
                $count++;
                echo '<div class="board2" style="text-align:left">' . $count . ') ' . date('d.m.Y H:i', (int)$log['date']);
                if (!empty($log['ip'])) echo '<br/>IP: ' . $log['ip'];
                if (!empty($log['soft'])) echo '<br/>SOFT: ' . $log['soft'];
                if (!empty($log['host'])) echo '<br/>HOST: ' . $log['host'];
                echo '</div>';
            }
        }
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'blok':
        if ($f['admin'] < 1) msg2('Вы не можете блокировать персонажей.', 1);
        if (empty($lgn) || empty($num) || empty($mysql)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=blok" method="POST">';
            echo 'Введите логин:<br/>';
            echo '<input type="text" name="lgn" value="' . $lgn . '"/><br/>';
            echo '<select name="num">
            <option selected value="1">1 Сутки</option>
            <option value="2">2 Суток</option>
            <option value="3">3 Суток</option>
            <option value="4">4 Суток</option>
            <option value="5">5 Суток</option>
            <option value="6">6 Суток</option>
            <option value="7">7 Суток</option>';
            if (2 <= $f['admin']) echo '<option value="8">Пожизненно</option>';
            echo '</select><br/>';
            echo 'Комментарий (100 симв):<br/>';
            echo '<input type="text" name="mysql" maxlength=100/><br/>';
            echo '<input type="submit" value="Go"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $TestLGN = get_login($lgn);
        if ($f['login'] == $TestLGN['login']) msg2('Нельзя отправить в блок самого себя', 1);
        if ($f['admin'] < $TestLGN['admin']) msg2('Ваш статус не позволяет блокировать этого персонажа', 1);
        if ($TestLGN['flag_blok'] == 1) msg2('Данный персонаж уже заблокирован', 1);
        $lgn = $TestLGN['login'];
        $mysql = ekr($mysql);
        $num = (int)$num;
        $mysql = mb_substr($mysql, 0, 100, 'UTF-8');
        $mysql .= '<br/>Заблокирован by ' . $f['login'];
        $mysql_esc = $db->real_escape_string($mysql);
        $lgn_esc = $db->real_escape_string($lgn);
        if ($num == 8 && 2 <= $f['admin']) {
            $mess = '<b>Админка</b>: персонаж ' . $f['login'] . ' заблокировал персонажа ' . $lgn . ' пожизненно.';
            $db->query("INSERT INTO `letters` VALUES (0, 0, '{$t}', '" . $db->real_escape_string($admin) . "', '" . $db->real_escape_string($settings['bot']) . "', '" . $db->real_escape_string($mess) . "', 0, 0);");
            $db->query("UPDATE `users` SET `flag_blok` = 1, `zachto_blok` = '{$mysql_esc}', `ban` = 0, `lastdate` = '{$t}' WHERE `login` = '{$lgn_esc}' LIMIT 1;");
            msg2('Персонажу ' . $lgn . ' наложен пожизненный блок');
            knopka('adm.php', 'В админку', 1);
            fin();
        } elseif ($num >= 1 && $num <= 7) {
            $ban = $t + (86400 * $num);
            $mess = '<b>Админка</b>: персонаж ' . $f['login'] . ' заблокировал персонажа ' . $lgn . ' на ' . $num . ' суток.';
            $db->query("INSERT INTO `letters` VALUES (0, 0, '{$t}', '" . $db->real_escape_string($admin) . "', '" . $db->real_escape_string($settings['bot']) . "', '" . $db->real_escape_string($mess) . "', 0, 0);");
            $db->query("UPDATE `users` SET `flag_blok` = 1, `zachto_blok` = '{$mysql_esc}', `ban` = '{$ban}', `lastdate` = '{$t}' WHERE `login` = '{$lgn_esc}' LIMIT 1;");
            msg2('Персонажу ' . $lgn . ' наложен блок на ' . $num . ' сут.');
            knopka('adm.php', 'В админку', 1);
            fin();
        } else {
            msg2('Нельзя отправить в блок на столько дней', 1);
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        break;

    case 'unblok':
        if ($f['admin'] < 2) msg2('Вы не можете разюлокировать персонажей', 1);
        $TestLGN = get_login($lgn);
        $lgn = $TestLGN['login'];
        if ($TestLGN['flag_blok'] == 0) msg2('Персонаж ' . $lgn . ' не в блоке', 1);
        $db->query("UPDATE `users` SET `flag_blok` = 0, `zachto_blok` = '', `ban` = 0, `lastdate` = '{$t}' WHERE `login` = '" . $db->real_escape_string($lgn) . "' LIMIT 1;");
        msg2('Персонаж ' . $lgn . ' успешно разблокирован.');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'spam':
        if ($f['admin'] < 2) msg2('Вы не можете давать спам', 1);
        if (empty($mysql)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=spam" method="POST">';
            echo 'Это сообщение получат все, кто не в блоке и были в игре в ближайшие 30 дней:<br/>';
            echo '<input type="text" name="mysql"/><br/>';
            echo '<input type="submit" value="Далее"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        // ИСПРАВЛЕНО: было 68400 (19 часов), стало 86400 (сутки)
        $srok = 86400 * 30;
        $count = 0;
        $mess = '<b><img src="pic/1429.png" alt=""/>Системное сообщение</b>: ' . ekr($mysql);
        $mess_esc = $db->real_escape_string($mess);
        $timer = $t - $srok;
        $q = $db->query("SELECT `login` FROM `users` WHERE `lastdate` > '{$timer}' AND `flag_blok` = 0;");
        if ($q) {
            while ($b = $q->fetch_assoc()) {
                $count++;
                $db->query("INSERT INTO `letters` VALUES (0, 0, '{$t}', '" . $db->real_escape_string($b['login']) . "', '" . $db->real_escape_string($settings['bot']) . "', '{$mess_esc}', 0, 0);");
            }
        }
        msg2('Отправлено ' . $count . ' сообщений.');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'pass':
        if ($f['admin'] < 3) msg2('Вы не можете менять пароли игрокам', 1);
        if (empty($lgn) || empty($mysql)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=pass" method="POST">
            Логин:<br/><input type="text" name="lgn" value="' . $lgn . '"/><br/>
            Новый пароль<br/><input type="text" name="mysql"/><br/>
            <input type="submit" value="Ок"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $TestLGN = get_login($lgn);
        $lgn = $TestLGN['login'];
        if (preg_match('/[^a-zA-Z0-9]/', $mysql)) msg2('Недопустимый пароль', 1);
        $mysql = md5($mysql);
        $db->query("UPDATE `users` SET `pass` = '{$mysql}' WHERE `login` = '" . $db->real_escape_string($lgn) . "' LIMIT 1;");
        msg2('Персонажу ' . $lgn . ' успешно изменен пароль.');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'login':
        if ($f['admin'] < 3) msg2('Вы не можете менять логины игрокам', 1);
        if (empty($lgn) || empty($mysql)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=pass" method="POST">
            Логин:<br/><input type="text" name="lgn" value="' . $lgn . '"/><br/>
            Новый логин<br/><input type="text" name="mysql"/><br/>
            <input type="submit" value="Ок"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $a = get_login($lgn);
        $lgn = $a['login'];
        if (preg_match('/[^a-zA-Z0-9_]/', $mysql)) msg2('Недопустимый логин', 1);
        $mysql_esc = $db->real_escape_string($mysql);
        $q = $db->query("SELECT `login` FROM `users` WHERE `login` = '{$mysql_esc}' LIMIT 1;");
        if ($q && $q->num_rows > 0) {
            msg2('<span style="color:red"><b>Логин ' . $mysql . ' уже занят. Выберите другой.</b></span>');
            knopka('javascript:history.go(-1)', 'Назад', 1);
            fin();
        }
        $old_esc = $db->real_escape_string($lgn);
        $db->query("UPDATE `chat` SET `login` = '{$mysql_esc}' WHERE `login` = '{$old_esc}';");
        $db->query("UPDATE `chat` SET `privat` = '{$mysql_esc}' WHERE `privat` = '{$old_esc}';");
        $db->query("UPDATE `combat` SET `login` = '{$mysql_esc}' WHERE `login` = '{$old_esc}';");
        $db->query("UPDATE `forum_comm` SET `login` = '{$mysql_esc}' WHERE `login` = '{$old_esc}';");
        $db->query("UPDATE `forum_topic` SET `login` = '{$mysql_esc}' WHERE `login` = '{$old_esc}';");
        $db->query("UPDATE `invent` SET `login` = '{$mysql_esc}' WHERE `login` = '{$old_esc}';");
        $db->query("UPDATE `invent` SET `arenda_login` = '{$mysql_esc}' WHERE `arenda_login` = '{$old_esc}';");
        $db->query("UPDATE `ipsoft` SET `login` = '{$mysql_esc}' WHERE `login` = '{$old_esc}';");
        $db->query("UPDATE `letters` SET `login` = '{$mysql_esc}' WHERE `login` = '{$old_esc}';");
        $db->query("UPDATE `letters` SET `login_from` = '{$mysql_esc}' WHERE `login_from` = '{$old_esc}';");
        $db->query("UPDATE `log_peredach` SET `login` = '{$mysql_esc}' WHERE `login` = '{$old_esc}';");
        $db->query("UPDATE `log_peredach` SET `login_per` = '{$mysql_esc}' WHERE `login_per` = '{$old_esc}';");
        $db->query("UPDATE `magic` SET `login` = '{$mysql_esc}' WHERE `login` = '{$old_esc}';");
        $db->query("UPDATE `users` SET `login` = '{$mysql_esc}' WHERE `login` = '{$old_esc}' LIMIT 1;");
        msg2('Персонажу ' . $lgn . ' успешно изменен логин на ' . $mysql . '.');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'res':
        if ($f['admin'] < 3) msg2('Воскрешение вам недоступно', 1);
        $db->query("UPDATE `users` SET `hpnow` = `hpmax`, `mananow` = `manamax` WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2('Ваши жизни и мана восстановлены!');
        knopka('adm.php', 'В админку', 1);
        knopka('loc.php', 'В игру', 1);
        fin();
        break;

    case 'brak':
        if (empty($lgn) || empty($lgn2)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=brak" method="POST">
            Первый логин:<br/><input type="text" name="lgn" value="' . $lgn . '"/><br/>
            Второй логин<br/><input type="text" name="lgn2" value="' . $lgn2 . '"/><br/>
            <input type="submit" value="Ок"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $lgn_tmp = get_login($lgn);
        $lgn = $lgn_tmp['login'];
        $lgn2_tmp = get_login($lgn2);
        $lgn2 = $lgn2_tmp['login'];
        $db->query("UPDATE `users` SET `brak` = '" . $db->real_escape_string($lgn) . "' WHERE `login` = '" . $db->real_escape_string($lgn2) . "' LIMIT 1;");
        $db->query("UPDATE `users` SET `brak` = '" . $db->real_escape_string($lgn2) . "' WHERE `login` = '" . $db->real_escape_string($lgn) . "' LIMIT 1;");
        msg2('Брак между ' . $lgn . ' и ' . $lgn2 . ' успешно заключен!');
        knopka('adm.php', 'В админку', 1);
        knopka('loc.php', 'В игру', 1);
        fin();
        break;

    case 'up':
        if ($f['admin'] < 3) msg2('Взятие уровня вам недоступно', 1);
        $db->query("UPDATE `users` SET `exp` = 0, `lvl` = `lvl` + 1 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2('LVL +1');
        knopka('adm.php', 'В админку', 1);
        knopka('loc.php', 'В игру', 1);
        fin();
        break;

    case 'null':
        if ($f['admin'] < 3) fin('Обнуление вам недоступно', 1);
        $db->query("UPDATE `users` SET `exp` = 0, `zdor` = 1, `sila` = 1, `lovka` = 1, `inta` = 1, `lvl` = 1, `intel` = 1 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2('LVL = 1');
        knopka('adm.php', 'В админку', 1);
        knopka('loc.php', 'В игру', 1);
        fin();
        break;

    case 'chatban':
        if ($f['admin'] < 1) msg('Недоступно для вас', 1);
        if (empty($lgn)) msg('Вы не ввели логин!', 1);
        $l = get_login($lgn);
        $lgn = $l['login'];
        if (empty($_REQUEST['ok']) || empty($_REQUEST['mid'])) {
            msg2('Вы собираетесь поставить молчу персонажу ' . $lgn);
            knopka('adm.php?mod=chatban&lgn=' . $lgn . '&mid=1&ok=1', '5 минут');
            knopka('adm.php?mod=chatban&lgn=' . $lgn . '&mid=2&ok=1', '15 минут');
            knopka('adm.php?mod=chatban&lgn=' . $lgn . '&mid=3&ok=1', '30 минут');
            knopka('adm.php?mod=chatban&lgn=' . $lgn . '&mid=4&ok=1', '1 час');
            knopka('adm.php?mod=chatban&lgn=' . $lgn . '&mid=5&ok=1', '1 сутки');
            if (2 <= $f['admin']) knopka('adm.php?mod=chatban&lgn=' . $lgn . '&mid=6&ok=1', '2 суток');
            if (3 <= $f['admin']) knopka('adm.php?mod=chatban&lgn=' . $lgn . '&mid=7&ok=1', '7 суток');
            knopka('chat.php', 'В чат');
            knopka('adm.php', 'В админку');
            fin();
        }
        if ($f['admin'] < $l['admin']) msg2('Недостаточно прав.', 1);
        if ($l['ban'] > $t || !empty($l['flag_blok'])) msg2('У персонажа ' . $lgn . ' уже установлена молча или он в блоке.', 1);
        if ($l['login'] == $f['login']) msg2('Нельзя поставить молчу самому себе, попросите админа :)', 1);
        $timeban = 0;
        $mid = (int)$mid;
        if ($mid == 1) $timeban = 60 * 5;
        if ($mid == 2) $timeban = 60 * 15;
        if ($mid == 3) $timeban = 60 * 30;
        if ($mid == 4) $timeban = 60 * 60;
        if ($mid == 5) $timeban = 60 * 60 * 24;
        if ($mid == 6 && $f['admin'] >= 2) $timeban = 60 * 60 * 24 * 2;
        if ($mid == 7 && $f['admin'] >= 3) $timeban = 60 * 60 * 24 * 7;
        if ($mid < 0 || $mid > 7) msg2('Недостаточно прав', 1);
        if (empty($_REQUEST['mess'])) {
            echo '<form action="adm.php?mod=chatban&lgn=' . $lgn . '&mid=' . $mid . '&ok=1" method="POST">';
            echo '<div class="board" style="text-align:left">';
            echo 'Вы собираетесь поставить молчу на ' . ceil($timeban / 60) . ' минут персонажу ' . $lgn . '. Причина:<br/>';
            echo '<input type="text" class="name" name="mess"/><br/>';
            echo '<input type="submit" class="btn" value="Продолжить"/></form>';
            echo '</div>';
            knopka('chat.php', 'В чат');
            knopka('adm.php', 'В админку');
            fin();
        }
        $mess = ekr($_REQUEST['mess']);
        $room = (int)$f['chatroom'];
        $timeban += $t;
        $db->query("UPDATE `users` SET `ban` = {$timeban}, `zachto_blok` = '" . $db->real_escape_string($mess) . "' WHERE `id` = '" . $l['id'] . "' LIMIT 1;");
        $mess = '<span style="color:' . $notice . ';"><img src="pic/1429.png" alt=""/><b>' . $f['login'] . ': персонаж ' . $lgn . ' получает молчу на ' . (int)(($timeban - $t) / 60) . ' минут. (' . $mess . ')</b></span>';
        $db->query("INSERT INTO `chat` VALUES (0, '" . $db->real_escape_string($settings['bot']) . "', '" . $db->real_escape_string($mess) . "', '{$room}', '', 0, 1, '{$t}', '', 0, 0);");
        msg2('Вы поставили молчу персонажу ' . $lgn . ' на ' . ceil(($timeban - $t) / 60) . ' минут.');
        knopka('chat.php', 'В чат');
        knopka('adm.php', 'В админку');
        fin();
        break;

    case 'chatunban':
        if ($f['admin'] < 1) msg('Недостаточно прав!', 1);
        if (empty($lgn)) {
            echo '<form action="adm.php?mod=chatunban" method="POST">';
            echo 'Введите логин:<br/>';
            echo '<input type="text" class="name" name="lgn"/>';
            echo '<input type="submit" class="btn" value="Далее"/>';
            echo '</form>';
            fin();
        }
        $l = get_login($lgn);
        $lgn = $l['login'];
        if ($f['admin'] < $l['admin']) msg2('Недостаточно прав', 1);
        if ($l['ban'] < $t) msg2('У персонажа ' . $lgn . ' отсутствует молча.', 1);
        if ($l['flag_blok'] == 1) msg2('Персонаж ' . $lgn . ' заблокирован.', 1);
        if ($l['login'] == $f['login']) msg2('Нельзя снять молчу у себя, попросите админа :)', 1);
        if (empty($_REQUEST['ok'])) {
            msg2('У персонажа ' . $lgn . ' молча за ' . $l['zachto_blok'] . '. Вы уверены, что хотите снять молчу?');
            knopka('adm.php?mod=chatunban&lgn=' . $lgn . '&ok=1', 'Снять молчу');
            knopka('chat.php', 'В чат');
            knopka('adm.php', 'В админку');
            fin();
        }
        $room = (int)$f['chatroom'];
        $mess = '<span style="color:' . $notice . ';"><img src="pic/1429.png" alt=""/><b>' . $f['login'] . ': персонажу ' . $lgn . ' снята молча.</b></span>';
        $db->query("INSERT INTO `chat` VALUES (0, '" . $db->real_escape_string($settings['bot']) . "', '" . $db->real_escape_string($mess) . "', '{$room}', '', 0, 1, '{$t}', '', 0, 0);");
        $db->query("UPDATE `users` SET `ban` = 0, `zachto_blok` = '' WHERE `id` = '" . $l['id'] . "' LIMIT 1;");
        msg2('Вы сняли молчу персонажу ' . $lgn . '.');
        knopka('chat.php', 'В чат');
        knopka('adm.php', 'В админку');
        fin();
        break;

    case 'chatclear':
        if ($f['admin'] < 3) msg('Недостаточно прав!', 1);
        if (empty($_REQUEST['ok'])) {
            msg2('Все сообщения во всех комнатах будут удалены!');
            knopka('adm.php?mod=chatclear&ok=1', 'Продолжить');
            knopka('chat.php', 'В чат');
            knopka('adm.php', 'В админку');
            fin();
        }
        $db->query("TRUNCATE TABLE `chat`;");
        msg('Все чаты очищены');
        knopka('chat.php', 'В чат');
        knopka('adm.php', 'В админку');
        fin();
        break;

    case 'chatroomclear':
        if ($f['admin'] < 2) msg('Недостаточно прав!', 1);
        if (empty($_REQUEST['ok'])) {
            msg2('Все сообщения в этой комнате будут удалены!');
            knopka('adm.php?mod=chatroomclear&ok=1', 'Продолжить');
            knopka('chat.php', 'В чат');
            knopka('adm.php', 'В админку');
            fin();
        }
        $room = (int)$f['chatroom'];
        if ($room == 1) $db->query("DELETE FROM `chat` WHERE `room` = {$room} AND `login` <> '" . $db->real_escape_string($settings['bot']) . "';");
        elseif ($room == 2) $db->query("DELETE FROM `chat` WHERE `room` = {$room} AND `klan` = '" . $db->real_escape_string($f['klan']) . "' AND `login` <> '" . $db->real_escape_string($settings['bot']) . "';");
        elseif ($room == 3) $db->query("DELETE FROM `chat` WHERE `room` = {$room} AND `party` = '" . $db->real_escape_string($f['party']) . "' AND `login` <> '" . $db->real_escape_string($settings['bot']) . "';");
        msg('Все сообщения в комнате чата удалены.');
        knopka('chat.php', 'В чат');
        knopka('adm.php', 'В админку');
        fin();
        break;

    case 'item':
        if ($f['admin'] < 3) msg2('Недоступно для вас', 1);
        if (empty($go) && empty($num)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=item&go=1" method="POST">
            Введите ID вещи:<br/>
            <input type="number" name="num" value="1"/><br/>
            <input type="submit" value="Далее"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $item = $items->base_shmot($num);
        if ($item === null) msg2('Вещь не найдена', 1);
        $items->add_item($f['login'], $num, 1);
        msg2('Вы получили ' . $item['name']);
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'othelit':
        if ($f['admin'] < 2) msg2('Вы не можете лечить игроков', 1);
        if (empty($ok)) {
            msg2('Вы хотите вылечить всех игроков?');
            knopka('adm.php?mod=othelit&ok=1', 'Вылечить', 1);
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $db->query("UPDATE `users` SET `hpnow` = `hpmax`, `mananow` = `manamax` WHERE `status` <> 1;");
        msg2('Готово! Жизни и Мана ВСЕМ игрокам восстановлены.');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'givemoney':
        if ($f['admin'] < 3) msg2('Вы не можете начислять монеты игрокам', 1);
        if (empty($lgn) || empty($num)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=givemoney" method="POST">
            Введите логин получателя:<br/>
            <input type="text" name="lgn" value="' . $lgn . '"/><br/>
            Введите количество монет:<br/>
            <input type="text" name="num" value="' . $num . '"/><br/>
            <input type="submit" value="Далее" />
            </form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $TestLGN = get_login($lgn);
        $lgn = $TestLGN['login'];
        $db->query("UPDATE `users` SET `money` = `money` + {$num} WHERE `login` = '" . $db->real_escape_string($lgn) . "' LIMIT 1;");
        $log = $f['login'] . ' [' . $f['lvl'] . '] передал ' . $TestLGN['login'] . ' [' . $TestLGN['lvl'] . '] ' . $num . ' монет - ';
        $mess = '<b>Админка</b>: персонаж ' . $f['login'] . ' передал персонажу ' . $lgn . ' ' . $num . ' монет.';
        require_once __DIR__ . '/inc/i.php';
        $db->query("INSERT INTO `letters` VALUES (0, 0, '{$t}', '" . $db->real_escape_string($admin) . "', '" . $db->real_escape_string($settings['bot']) . "', '" . $db->real_escape_string($mess) . "', 0, 0);");
        $db->query("INSERT INTO `log_peredach` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($lgn) . "', '{$t}');");
        msg2('Персонажу ' . $lgn . ' передано ' . $num . ' монет!');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'giveruda':
        if ($f['admin'] < 3) msg2('Вы не можете начислять руду игрокам', 1);
        if (empty($num) || empty($lgn)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=giveruda&go=1" method="POST">
            Передаем руду персонажу.<br/><br/>
            Логин:<br/>
            <input type="text" name="lgn" value="' . $lgn . '"/><br/>
            Введите количество руды:<br/>
            <input type="number" name="num" value="' . $num . '"/><br/>
            <input type="submit" value="Далее"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        if ($num < 1) $num = 1;
        $l = get_login($lgn);
        $lgn = $l['login'];
        $mess = '<b>Админка</b>: персонаж ' . $f['login'] . ' передал ' . $num . ' руды персонажу ' . $lgn . '.';
        require_once __DIR__ . '/inc/i.php';
        $db->query("INSERT INTO `letters` VALUES (0, 0, '{$t}', '" . $db->real_escape_string($admin) . "', '" . $db->real_escape_string($settings['bot']) . "', '" . $db->real_escape_string($mess) . "', 0, 0);");
        $db->query("UPDATE `users` SET `ruda` = `ruda` + {$num} WHERE `login` = '" . $db->real_escape_string($lgn) . "' LIMIT 1;");
        msg2($num . ' руды персонажу ' . $lgn . ' успешно передано.');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'mysql':
        if ($f['admin'] < 3) msg2('Вы не можете работать с MySQL', 1);
        if (empty($mysql)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=mysql&go=1" method="POST">
            Введите mysql-запрос:<br/>
            <input type="text" name="mysql" /><br/>
            <input type="submit" value="Далее" />
            </form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        $q = $db->query($mysql);
        if ($q === false) msg2('Ошибка MySQL: ' . htmlspecialchars($db->error(), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 1);
        msg2('Запрос ' . htmlspecialchars($mysql, ENT_QUOTES | ENT_HTML5, 'UTF-8') . ' выполнен успешно');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'lastonl':
        if ($f['admin'] < 1) msg2('Вы не можете просматривать полный список игроков', 1);
        $q = $db->query("SELECT `lvl`, `login`, `lastdate` FROM `users` ORDER BY `lastdate` DESC;");
        $count = 0;
        if ($q) {
            while ($Arr = $q->fetch_assoc()) {
                $count++;
                echo '<div class="board2" style="text-align:left">' . $count . ') <a href="infa.php?mod=uzinfa&lgn=' . $Arr['login'] . '">' . $Arr['login'] . ' [' . $Arr['lvl'] . ']</a> (' . date('d.m.Y H:i', (int)$Arr['lastdate']) . ')';
                echo ' <a href="adm.php?mod=ipsoft&lgn=' . $Arr['login'] . '">[ip/soft]</a></div>';
            }
        }
        fin();
        break;

    case 'lookblok':
        if ($f['admin'] < 2) msg2('Вы не можете работать с заблокированными персонажами', 1);
        $q = $db->query("SELECT `lvl`, `login`, `ban` FROM `users` WHERE `flag_blok` = 1 ORDER BY `lastdate` DESC;");
        if ($q) {
            while ($Arr = $q->fetch_assoc()) {
                echo '<div class="board2" style="text-align:left"><a href="infa.php?mod=uzinfa&lgn=' . $Arr['login'] . '">' . $Arr['login'] . '</a> [' . $Arr['lvl'] . ']';
                if ($Arr['ban'] > $t) echo ' (до ' . date('d.m.Y H:i', (int)$Arr['ban']) . ')';
                echo ' <a href="adm.php?mod=ipsoft&lgn=' . $Arr['login'] . '">[ip/soft]</a>';
                echo ' <a href="adm.php?mod=unblok&lgn=' . $Arr['login'] . '">[unblock]</a></div>';
            }
        }
        fin();
        break;

    case 'sovp':
        if ($f['admin'] < 1) msg2('Совпадения паролей недоступны для вас', 1);
        msg2('Совпадение по паролю:');
        echo '<div class="board" style="text-align:left">';
        $q = $db->query("SELECT `pass` FROM `users` GROUP BY `pass` HAVING COUNT(`pass`) > 1;");
        if ($q) {
            while ($a = $q->fetch_assoc()) {
                echo '<table border=1>';
                if (3 <= $f['admin']) echo '<tr><td width="240" height="16">' . $a['pass'] . '</td></tr>';
                else echo '<tr><td width="240" height="16">Совпадение пароля!!!</td></tr>';
                $b = $db->query("SELECT `login`, `lvl` FROM `users` WHERE `pass` = '{$a['pass']}';");
                if ($b) {
                    while ($c = $b->fetch_assoc()) {
                        echo '<tr><td><a href="infa.php?mod=uzinfa&lgn=' . $c['login'] . '">' . $c['login'] . '</a> [' . $c['lvl'] . ']</td></tr>';
                    }
                }
                echo '</table><br/>';
            }
        }
        echo '</div>';
        fin();
        break;

    case 'sovpip':
        if ($f['admin'] < 1) msg2('Совпадения IP недоступны для вас', 1);
        msg2('Совпадение по IP:');
        $q = $db->query("SELECT `ip` FROM `users` WHERE `ip` <> '' GROUP BY `ip` HAVING COUNT(`ip`) > 1;");
        if (!$q || $q->num_rows == 0) msg2('Совпадений IP не обнаружено.', 1);
        echo '<div class="board" style="text-align:left">';
        if ($q) {
            while ($a = $q->fetch_assoc()) {
                echo '<table border=1>';
                echo '<tr><td width="240" height="16">' . $a['ip'] . '</td></tr>';
                $b = $db->query("SELECT `login`, `lvl` FROM `users` WHERE `ip` = '{$a['ip']}';");
                if ($b) {
                    while ($c = $b->fetch_assoc()) {
                        echo '<tr><td><a href="infa.php?mod=uzinfa&lgn=' . $c['login'] . '">' . $c['login'] . '</a> [' . $c['lvl'] . ']</td></tr>';
                    }
                }
                echo '</table><br/>';
            }
        }
        echo '</div>';
        fin();
        break;

    case 'sovpipall':
        if ($f['admin'] < 1) msg2('Совпадения IP недоступны для вас', 1);
        if (!empty($_SESSION['ip']) && $_SESSION['ip'] > $t) msg2('Нельзя использовать данную функцию чаще, чем раз в минуту, слишком большая нагрузка на сервер!', 1);
        msg2('Совпадение по IP (архив):');
        $_SESSION['ip'] = 60 + $t;
        $q = $db->query("SELECT `ip`, `login` FROM `ipsoft` WHERE `ip` <> '' AND `login` <> '' GROUP BY `ip` HAVING COUNT(`login`) > 1;");
        $count = 0;
        if ($q) {
            while ($a = $q->fetch_assoc()) {
                $qq = $db->query("SELECT `login` FROM `ipsoft` WHERE `ip` = '{$a['ip']}' AND `login` <> '' AND `login` <> '{$a['login']}' GROUP BY `login`;");
                if ($qq && $qq->num_rows > 0) {
                    $count++;
                    echo '<div class="board" style="text-align:left">';
                    echo '<table border=1>';
                    echo '<tr><td width="240" height="16">' . $a['ip'] . '</td></tr>';
                    echo '<tr><td><a href="infa.php?mod=uzinfa&lgn=' . $a['login'] . '">' . $a['login'] . '</a></td></tr>';
                    while ($b = $qq->fetch_assoc()) {
                        echo '<tr><td><a href="infa.php?mod=uzinfa&lgn=' . $b['login'] . '">' . $b['login'] . '</a></td></tr>';
                    }
                    echo '</table><br/>';
                    echo '</div>';
                }
            }
        }
        if ($count == 0) msg2('Совпадений IP не найдено', 1);
        fin();
        break;

    case 'sovpsoftall':
        if ($f['admin'] < 1) msg2('Совпадения SOFT недоступны для вас', 1);
        if (!empty($_SESSION['ip']) && $_SESSION['ip'] > $t) msg2('Нельзя использовать данную функцию чаще, чем раз в минуту, слишком большая нагрузка на сервер!', 1);
        msg2('Совпадение по SOFT (архив):');
        $_SESSION['ip'] = 60 + $t;
        $q = $db->query("SELECT `soft`, `login` FROM `ipsoft` WHERE `soft` <> '' AND `login` <> '' GROUP BY `soft` HAVING COUNT(`login`) > 1;");
        $count = 0;
        if ($q) {
            while ($a = $q->fetch_assoc()) {
                $qq = $db->query("SELECT `login` FROM `ipsoft` WHERE `soft` = '{$a['soft']}' AND `login` <> '' AND `login` <> '{$a['login']}' GROUP BY `login`;");
                if ($qq && $qq->num_rows > 0) {
                    $count++;
                    echo '<div class="board" style="text-align:left">';
                    echo '<table border=1>';
                    echo '<tr><td width="240" height="16">' . $a['soft'] . '</td></tr>';
                    echo '<tr><td><a href="infa.php?mod=uzinfa&lgn=' . $a['login'] . '">' . $a['login'] . '</a></td></tr>';
                    while ($b = $qq->fetch_assoc()) {
                        echo '<tr><td><a href="infa.php?mod=uzinfa&lgn=' . $b['login'] . '">' . $b['login'] . '</a></td></tr>';
                    }
                    echo '</table><br/>';
                    echo '</div>';
                }
            }
        }
        if ($count == 0) msg2('Совпадений SOFT не найдено', 1);
        fin();
        break;

    case 'sovpall':
        if ($f['admin'] < 1) msg2('Совпадения IP/SOFT недоступны для вас', 1);
        if (!empty($_SESSION['ip']) && $_SESSION['ip'] > $t) msg2('Нельзя использовать данную функцию чаще, чем раз в минуту, слишком большая нагрузка на сервер!', 1);
        msg2('Совпадение по IP/SOFT (архив):');
        $_SESSION['ip'] = 60 + $t;
        $q = $db->query("SELECT `ip`, `soft`, `login` FROM `ipsoft` WHERE `ip` <> '' AND `soft` <> '' AND `login` <> '' GROUP BY `ip`, `soft` HAVING COUNT(`login`) > 1;");
        $count = 0;
        if ($q) {
            while ($a = $q->fetch_assoc()) {
                $qq = $db->query("SELECT `login` FROM `ipsoft` WHERE `ip` = '{$a['ip']}' AND `soft` = '{$a['soft']}' AND `login` <> '' AND `login` <> '{$a['login']}' GROUP BY `login`;");
                if ($qq && $qq->num_rows > 0) {
                    $count++;
                    echo '<div class="board" style="text-align:left">';
                    echo '<table border=1>';
                    echo '<tr><td width="240" height="16">' . $a['ip'] . '</td></tr>';
                    echo '<tr><td width="240" height="16">' . $a['soft'] . '</td></tr>';
                    echo '<tr><td><a href="infa.php?mod=uzinfa&lgn=' . $a['login'] . '">' . $a['login'] . '</a></td></tr>';
                    while ($b = $qq->fetch_assoc()) {
                        echo '<tr><td><a href="infa.php?mod=uzinfa&lgn=' . $b['login'] . '">' . $b['login'] . '</a></td></tr>';
                    }
                    echo '</table><br/>';
                    echo '</div>';
                }
            }
        }
        if ($count == 0) msg2('Совпадений IP/SOFT не найдено', 1);
        fin();
        break;

    case 'admin':
        if ($f['admin'] < 2) msg('Вы не можете управлять званиями', 1);
        if (empty($go)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=admin&go=1" method="POST">
            Введите логин:<br/>
            <input type="text" name="lgn" value="' . $lgn . '"/><br/>
            <select name="num">
            <option value="0">Игрок</option>
            <option value="1">Модер</option>
            <option value="2">Супермодер</option>';
            if ($f['admin'] >= 3) echo '<option value="3">Админ</option>';
            if ($f['admin'] >= 4) echo '<option value="4">Суперадмин</option>';
            echo '</select>
            <br/>
            <input type="submit" value="Далее" />
            </form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        if ($num != 0 && $num != 1 && $num != 2 && $num != 3 && $num != 4) msg2('Неверное значение', 1);
        // ИСПРАВЛЕНО: было два раза if ($num == 3)
        if ($num == 3 && $f['admin'] < 3) msg2('Админа может назначать Админ или Суперадмин', 1);
        if ($num == 4 && $f['admin'] < 4) msg2('Суперадмина может назначать только Суперадмин', 1);
        $TestLGN = get_login($lgn);
        $lgn = $TestLGN['login'];
        if ($TestLGN['admin'] >= 4) {
            msg2('Нельзя забрать права суперадмина!', 1);
        }
        $db->query("UPDATE `users` SET `admin` = {$num} WHERE `login` = '" . $db->real_escape_string($lgn) . "' LIMIT 1;");
        if ($num == 0) $num = 'игрока';
        elseif ($num == 1) $num = 'модера';
        elseif ($num == 2) $num = 'супермодера';
        elseif ($num == 3) $num = 'админа';
        elseif ($num == 4) $num = 'суперадмина';
        $mess = 'Админка: персонаж ' . $f['login'] . ' сменил статус персонажу ' . $lgn . '.';
        require_once __DIR__ . '/inc/i.php';
        $db->query("INSERT INTO `letters` VALUES (0, 0, '{$t}', '" . $db->real_escape_string($admin) . "', '" . $db->real_escape_string($settings['bot']) . "', '" . $db->real_escape_string($mess) . "', 0, 0);");
        msg2('Персонажу ' . $lgn . ' присвоен статус ' . $num . '!');
        knopka('adm.php', 'В админку', 1);
        fin();
        break;

    case 'mail':
        if (empty($go)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=mail&go=1" method="POST">';
            echo 'Отправка почты<br/>';
            echo 'Email: <input type="text" name="lgn"/><br/>';
            echo 'Text: <br/>';
            echo '<textarea name="lgn2" maxlength=5000 rows="10" style="width:80%;"></textarea><br/>';
            echo '<input type="submit" value="Далее"/></form></div>';
            knopka('adm.php', 'В админку', 1);
            fin();
        }
        if (empty($lgn)) msg2('Не ввели Email', 1);
        if (empty($lgn2)) msg2('Не ввели текст сообщения', 1);
        $subj = 'Отправка из админки';
        $mess = $lgn2;
        $from = 'noreply@hmr.su';
        if (function_exists('mail_utf8') && mail_utf8($lgn, $subj, $mess, $from)) msg2('Отправлено успешно', 1);
        else msg2('Ошибка отправки Email!', 1);
        break;

    case 'locedit':
        if ($f['admin'] < 3) msg2('Недоступно для вас', 1);
        $q = $db->query("SELECT * FROM `loc` WHERE `id` = '" . (int)$f['loc'] . "' LIMIT 1;");
        if (!$q || $q->num_rows == 0) msg('Нет такой локации', 1);
        $a = $q->fetch_assoc();
        if (empty($go)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="adm.php?mod=locedit&go=1" method="POST">
            Имя локации:<br/>
            <input type="text" name="titl" value="' . $a['name'] . '" style="width:80%;"/><br/>
            Описание локации:<br/>
            <textarea name="news" maxlength=5000 style="width:80%; height:200px;">' . $a['info'] . '</textarea>';
            echo 'Мирная:<br/><select name="mir"><option ';
            if ($a['peace'] == 1) echo 'selected ';
            echo 'value="1">Да</option><option ';
            if ($a['peace'] != 1) echo 'selected ';
            echo 'value="2">Нет</option></select><br/>';
            echo '<input class="btn" type="submit" value="Далее" />
            </form></div>';
            knopka('adm.php', 'В админку');
            knopka('loc.php', 'В локацию');
            fin();
        }
        $titl = isset($_REQUEST['titl']) ? ekr($_REQUEST['titl']) : '';
        $news = isset($_REQUEST['news']) ? ekr($_REQUEST['news']) : '';
        $mir = isset($_REQUEST['mir']) ? ekr($_REQUEST['mir']) : '';
        if (empty($titl)) msg('Вы не ввели название!', 1);
        if (empty($news)) msg('Вы не ввели описание!', 1);
        if (empty($mir)) msg('Вы не ввели враждебность!', 1);
        $p = ($mir == 1) ? 1 : 0;
        $db->query("UPDATE `loc` SET `name` = '" . $db->real_escape_string($titl) . "', `info` = '" . $db->real_escape_string($news) . "', `peace` = '{$p}' WHERE `id` = '" . (int)$f['loc'] . "' LIMIT 1;");
        msg2('Вы успешно отредактировали локацию.');
        knopka('adm.php', 'В админку');
        knopka('loc.php', 'В игру');
        fin();
        break;
}
