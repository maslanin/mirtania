<?php
/**
 * Управление кланом.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО:
 *  - в mod=del было WHERE klan='...' (нет такого поля в klans) → name
 *  - get_login() возвращает false → проверки
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';

require_once __DIR__ . '/inc/hpstring.php';

// Права: 0 - нет, 1 - зам, 2 - наместник, 3 - глава
if (empty($f['klan']) || $f['klan_status'] == 0) msg2('Вы не можете управлять кланом!', 1);

$mod   = isset($_REQUEST['mod'])   ? $_REQUEST['mod'] : '';
$num   = isset($_REQUEST['num'])   ? (int)$_REQUEST['num'] : 0;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$go    = isset($_REQUEST['go'])    ? (int)$_REQUEST['go'] : 0;
$iid   = isset($_REQUEST['iid'])   ? (int)$_REQUEST['iid'] : 0;
$lgn   = isset($_REQUEST['lgn'])   ? $_REQUEST['lgn'] : '';
$ok    = isset($_REQUEST['ok'])    ? $_REQUEST['ok'] : '';

$klan_esc = $db->real_escape_string($f['klan']);
$q = $db->query("SELECT * FROM `klans` WHERE `name` = '{$klan_esc}' LIMIT 1;");
$a = $q ? $q->fetch_assoc() : null;
if (!$a) msg2('Клан не найден!', 1);

$q = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `klan` = '{$klan_esc}';");
$c = $q ? (int)$q->fetch_assoc()['c'] : 0;

if (empty($mod)) {
    msg2('Уровень клана: ' . $a['lvl'] . ' | Баллы: ' . $a['points'] . ' | Люди: ' . $c . '/' . ($a['lvl'] * 12) . ' | Казна: ' . $a['kazna'] . ' монет');
    if (2 <= $f['klan_status']) knopka('klan.php?mod=priem', 'Принять в клан', 1);
    if (1 <= $f['klan_status']) knopka('klan.php?mod=spam', 'Рассылка', 1);
    if (1 <= $f['klan_status']) knopka('klan.php?mod=sostav', 'Состав клана', 1);
    if (2 <= $f['klan_status']) knopka('klan.php?mod=rekrut', 'Рекруты', 1);
    if (2 <= $f['klan_status']) knopka('klan.php?mod=status', 'Статусы', 1);
    if (2 <= $f['klan_status'] && 7 <= GetDay((int)$a['nalog_time'])) {
        knopka('klan.php?mod=nalog', 'Собрать налог', 1);
    } else {
        msg('Налог будет доступен для сбора ' . date('d.m.Y', (int)$a['nalog_time'] + 86400 * 7));
    }
    if ($f['loc'] != 1 && $f['loc'] != 37 && $f['loc'] != 91 && $a['loc'] == 0 && $a['point'] == 0) {
        $q = $db->query("SELECT `name`, `loc`, `point` FROM `klans` WHERE `loc` = " . (int)$f['loc'] . " OR `point` = " . (int)$f['loc'] . " LIMIT 1;");
        if (!$q || $q->num_rows == 0) {
            knopka('klan.php?mod=mesto', 'Отметить это место для постройки замка', 1);
        }
    }
    fin();
}

if ($mod == 'priem') {
    if ($f['klan_status'] < 2) msg2('Прием в клан доступен только наместникам и главам.', 1);
    if ($a['lvl'] * 12 <= $c) msg2('В клане нет мест, необходимо повысить уровень клана!', 1);
    if (empty($lgn)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="klan.php?mod=priem" method="POST">';
        echo 'Введите ник:<br/>';
        echo '<input type="text" name="lgn" style="width:80%"/><br/>';
        echo '<input type="submit" value="Далее"/></form></div>';
        knopka('klan.php', 'Вернуться', 1);
        fin();
    }
    $TestLGN = get_login($lgn);
    $lgn = $TestLGN['login'];
    if ($TestLGN['lvl'] < 4) msg2('Вступать в клан можно с 4 уровня.', 1);
    if ($TestLGN['status'] == 1) msg2('Персонаж ' . $lgn . ' в бою.', 1);
    if (!empty($TestLGN['klan'])) msg2('Персонаж ' . $lgn . ' состоит в клане <b>' . $TestLGN['klan'] . '</b>.', 1);
    if (!empty($TestLGN['klan_invite'])) {
        if ($TestLGN['klan_invite'] == $f['klan']) msg2('У персонажа ' . $lgn . ' уже есть приглашение в ваш клан.', 1);
        else msg2('У персонажа ' . $lgn . ' уже есть приглашение в другой клан.', 1);
    }
    if (empty($ok)) {
        msg2('Вы хотите пригласить в клан персонажа ' . $lgn . '. Продолжить?');
        knopka('klan.php?mod=priem&lgn=' . $lgn . '&ok=1', 'Продолжить', 1);
        knopka('klan.php', 'Вернуться', 1);
        fin();
    }
    $db->query("UPDATE `users` SET `klan_invite` = '{$klan_esc}' WHERE `login` = '" . $db->real_escape_string($lgn) . "' LIMIT 1;");
    msg2('Персонаж ' . $lgn . ' успешно приглашен в клан.');
    knopka('klan.php', 'Далее', 1);
    fin();
}

if ($mod == 'status') {
    if (empty($ok)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="klan.php?mod=status&ok=1" method="POST">
        Введите логин:<br/>
        <input type="text" name="lgn" /><br/>
        <select name="num">
        <option value="0">Без статуса</option>
        <option value="1">Зам. Главы</option>';
        if (3 <= $f['klan_status']) echo '<option value="2">Наместник</option><option value="3">Глава клана</option>';
        echo '</select>
        <br/>
        <input type="submit" value="Далее" />
        </form></div>';
        knopka('klan.php', 'Вернуться', 1);
        fin();
    }
    if ($num != 0 && $num != 1 && $num != 2 && $num != 3) msg2('Неверный выбор статуса персонажа', 1);
    if ($num == 3 && $f['klan_status'] < 3) msg2('Главу клана может назначить только Глава клана, передав полномочия.', 1);
    if ($num == 2 && $f['klan_status'] < 3) msg2('Наместника может назначить только Глава клана.', 1);
    $TestLGN = get_login($lgn);
    if ($TestLGN['klan'] != $f['klan']) msg2('Этот персонаж не в вашем клане.', 1);
    $lgn = $TestLGN['login'];
    if ($lgn == $f['login']) msg2('Не стоит менять собственный статус', 1);
    if ($TestLGN['klan_status'] == 3) msg2('Нельзя сменить статус у главы клана.', 1);
    if ($TestLGN['klan_status'] == 2 && $f['klan_status'] < 3) msg2('Недостаточно прав для смены статуса.', 1);
    $db->query("UPDATE `users` SET `klan_status` = {$num} WHERE `login` = '" . $db->real_escape_string($lgn) . "' LIMIT 1;");
    if ($num == 3) $db->query("UPDATE `users` SET `klan_status` = 2 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    msg2('Персонажу ' . $lgn . ' сменен статус!');
    knopka('klan.php', 'Вернуться', 1);
    fin();
}

if ($mod == 'sostav') {
    if ($f['klan_status'] < 2) msg2('Вы не можете работать с составом клана', 1);
    $count = 0;
    $q = $db->query("SELECT `login`, `lvl`, `sex`, `exp`, `klan_status`, `lastdate`, `nalog` FROM `users` WHERE `klan` = '{$klan_esc}' ORDER BY `lvl` DESC, `exp` DESC, `login`;");
    echo '<div class="board" style="text-align:left">';
    echo '<table border="1">';
    echo '<tr><td>#</td><td align="center">НИК</td><td align="center">СТАТУС</td><td align="center">ЗАХОД</td><td align="center">НАЛОГ</td><td align="center">---</td></tr>';
    if ($q) {
        while ($sostav = $q->fetch_assoc()) {
            echo '<tr>';
            $color_login = ($sostav['sex'] == 1) ? $male : $female;
            $count++;
            echo '<td>' . $count . '</td>';
            echo '<td><a href="infa.php?mod=uzinfa&lgn=' . $sostav['login'] . '"><span style="color:' . $color_login . '">' . $sostav['login'] . ' [' . $sostav['lvl'] . ']</span></a></td>';
            echo '<td>';
            if ($sostav['klan_status'] == 3) echo '<b>Глава</b>';
            elseif ($sostav['klan_status'] == 2) echo '<b>Наместник</b>';
            elseif ($sostav['klan_status'] == 1) echo '<b>Зам. Главы</b>';
            echo '</td>';
            $raznica = time() - (int)$sostav['lastdate'];
            if ($raznica <= 300) $onl = '<span style="color:' . $notice . '">В игре</span>';
            elseif ($raznica <= 3600) $onl = ceil($raznica / 60) . ' мин. назад';
            elseif ($raznica <= 86400) $onl = ceil($raznica / 3600) . ' чс. назад';
            else $onl = ceil($raznica / 86400) . ' дн. назад';
            echo '<td>' . $onl . '</td>';
            echo '<td>' . $sostav['nalog'] . '</td>';
            echo '<td>';
            if (3 <= $f['klan_status'] && $sostav['login'] != $f['login']) echo '<a href="klan.php?mod=del&lgn=' . $sostav['login'] . '">Исключить</a>';
            echo '</td>';
            echo '</tr>';
        }
    }
    echo '</table></div>';
    knopka('klan.php', 'Вернуться', 1);
    fin();
}

if ($mod == 'rekrut') {
    if ($f['klan_status'] < 2) msg2('Вы не можете работать с рекрутами', 1);
    if ($go == 1) {
        $TestLGN = get_login($lgn);
        $lgn = $TestLGN['login'];
        if ($TestLGN['klan_invite'] != $f['klan']) msg2('Персонаж ' . $lgn . ' не состоит в рекрутах вашего клана!', 1);
        $db->query("UPDATE `users` SET `klan_invite` = '' WHERE `id` = " . (int)$TestLGN['id'] . ";");
        msg2('Персонаж ' . $lgn . ' успешно удален из рекрутов вашего клана.');
    }
    $count = 0;
    $q = $db->query("SELECT `login`, `lvl`, `sex`, `lastdate` FROM `users` WHERE `klan_invite` = '{$klan_esc}' ORDER BY `login`;");
    if (!$q || $q->num_rows == 0) msg('Список рекрутов пуст.', 1);
    echo '<div class="board" style="text-align:left">';
    echo '<table border="1">';
    echo '<tr><td>#</td><td align="center">НИК</td><td align="center">ЗАХОД</td><td align="center">ДЕЙСТВИЕ</td></tr>';
    if ($q) {
        while ($sostav = $q->fetch_assoc()) {
            echo '<tr>';
            $color_login = ($sostav['sex'] == 1) ? $male : $female;
            $count++;
            echo '<td>' . $count . '</td>';
            echo '<td><a href="infa.php?mod=uzinfa&lgn=' . $sostav['login'] . '"><span style="color:' . $color_login . '">' . $sostav['login'] . ' [' . $sostav['lvl'] . ']</span></a></td>';
            $raznica = time() - (int)$sostav['lastdate'];
            if ($raznica <= 300) $onl = '<span style="color:' . $notice . '">В игре</span>';
            elseif ($raznica <= 3600) $onl = ceil($raznica / 60) . ' мин. назад';
            elseif ($raznica <= 86400) $onl = ceil($raznica / 3600) . ' чс. назад';
            else $onl = ceil($raznica / 86400) . ' дн. назад';
            echo '<td>' . $onl . '</td>';
            echo '<td><a href="klan.php?mod=rekrut&go=1&lgn=' . $sostav['login'] . '">Убрать</a></td>';
            echo '</tr>';
        }
    }
    echo '</table></div>';
    knopka('klan.php', 'Вернуться', 1);
    fin();
}

if ($mod == 'del') {
    if ($f['klan_status'] < 3) msg('Вы не можете исключать игроков из клана', 1);
    $TestLGN = get_login($lgn);
    $lgn = $TestLGN['login'];
    if (empty($ok)) {
        msg2('Вы действительно хотите исключить ' . $lgn . ' из клана?');
        knopka('klan.php?mod=del&lgn=' . $lgn . '&ok=1', 'Исключить', 1);
        knopka('klan.php?mod=sostav', 'Вернуться', 1);
        fin();
    }
    if ($f['login'] == $TestLGN['login']) msg2('Нельзя исключить себя из клана', 1);
    if ($TestLGN['klan_status'] == 3) msg2('Нельзя исключить главу из клана', 1);
    if ($TestLGN['klan'] != $f['klan']) msg2('Этот персонаж не состоит в вашем клане.', 1);
    if ($TestLGN['status'] > 0) msg2('Персонаж в бою.', 1);
    $db->query("UPDATE `users` SET `klan` = '', `klan_status` = 0, `klan_time` = '{$t}' WHERE `login` = '" . $db->real_escape_string($lgn) . "' LIMIT 1;");
    // ИСПРАВЛЕНО: было WHERE klan=... — в таблице klans нет поля klan, есть name
    $db->query("UPDATE `klans` SET `kazna` = `kazna` + " . (int)$TestLGN['nalog'] . " WHERE `name` = '{$klan_esc}' LIMIT 1;");
    msg2('Персонаж ' . $lgn . ' успешно исключен из вашего клана.');
    knopka('klan.php', 'Вернуться', 1);
    fin();
}

if ($mod == 'spam') {
    if ($f['klan_status'] < 1) msg2('Вы не можете давать массовую рассылку по клану.', 1);
    if (empty($lgn)) {
        echo '<div class="board" style="text-align:left">';
        echo '<form action="klan.php?mod=spam" method="POST">';
        echo 'Это сообщение получат все, кто не в блоке и были в игре в ближайшие 30 дней:<br/>';
        echo '<input type="text" name="lgn" style="width:80%"/><br/>';
        echo '<input type="submit" value="Далее"/></form></div>';
        knopka('klan.php', 'Вернуться', 1);
        fin();
    }
    $srok = 86400 * 30;
    $timer = $t - $srok;
    $count = 0;
    $mess = '<b>Кланспам</b>: ' . $lgn;
    $mess_esc = $db->real_escape_string($mess);
    $login_esc = $db->real_escape_string($f['login']);
    $q = $db->query("SELECT * FROM `users` WHERE `lastdate` > '{$timer}' AND `flag_blok` = 0 AND `klan` = '{$klan_esc}' AND `login` <> '{$login_esc}';");
    if ($q) {
        while ($b = $q->fetch_assoc()) {
            $count++;
            $db->query("INSERT INTO `letters` VALUES (0, 0, '{$t}', '" . $db->real_escape_string($b['login']) . "', '{$login_esc}', '{$mess_esc}', 0, 0);");
        }
    }
    msg2('Отправлено ' . $count . ' сообщений.');
    knopka('klan.php', 'Вернуться', 1);
    fin();
}

if ($mod == 'nalog') {
    if ($f['klan_status'] < 2) msg2('Вы не можете собирать налог.', 1);
    if (GetDay((int)$a['nalog_time']) < 7) msg2('Нельзя собирать налог чаще, чем раз в 7 дней. Будет доступно ' . date('d.m.Y', (int)$a['nalog_time'] + 86400 * 7), 1);
    $q = $db->query("SELECT SUM(`nalog`) AS `s` FROM `users` WHERE `klan` = '{$klan_esc}';");
    $nalog = $q ? (int)$q->fetch_assoc()['s'] : 0;
    $log = $f['login'] . ' [' . $f['lvl'] . '] собирает налог в размере ' . $nalog . ' монет.';
    $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '{$klan_esc}', '{$t}');");
    $db->query("UPDATE `klans` SET `kazna` = `kazna` + {$nalog}, `nalog_time` = '{$t}' WHERE `name` = '{$klan_esc}' LIMIT 1;");
    $db->query("UPDATE `users` SET `nalog` = 0 WHERE `klan` = '{$klan_esc}';");
    msg2('Собран налог в размере ' . $nalog . ' монет');
    knopka('klan.php', 'Вернуться', 1);
    fin();
}

if ($mod == 'mesto') {
    if ($f['loc'] == 1 || $f['loc'] == 37 || $f['loc'] == 91) msg2('Нельзя построить замок в этом месте!');
    $q = $db->query("SELECT `name`, `loc`, `point` FROM `klans` WHERE `loc` = " . (int)$f['loc'] . " OR `point` = " . (int)$f['loc'] . " LIMIT 1;");
    $a2 = $q ? $q->fetch_assoc() : null;
    if (!$a2) {
        if (empty($ok)) {
            msg2('Вы уверены, что хотите построить клановый замок здесь?');
            knopka('klan.php?mod=mesto&ok=1', 'Отметить место для постройки', 1);
            knopka('loc.php', 'В игру', 1);
            fin();
        }
        $log = $f['login'] . ' [' . $f['lvl'] . '] отмечает на карте место для постройки кланового замка.';
        $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '{$klan_esc}', '{$t}');");
        $db->query("UPDATE `klans` SET `point` = " . (int)$f['loc'] . " WHERE `name` = '{$klan_esc}' LIMIT 1;");
        msg2("Вы отметили место для постройки кланового замка. Теперь вам нужно добывать камни для постройки.");
        knopka('loc.php', 'В игру', 1);
        fin();
    } else {
        msg2('Это место уже занято кланом ' . $a2['name'], 1);
    }
}

fin();
