<?php
/**
 * Анкеты, рейтинги, онлайн.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО:
 *  - приоритет and/or в mod=iteminfa
 *  - незакрытый <div> в mod=uzinfa при блоке
 *  - @unserialize с проверкой
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';

$mod   = isset($_REQUEST['mod'])   ? $_REQUEST['mod'] : '';
$lgn   = isset($_REQUEST['lgn'])   ? $_REQUEST['lgn'] : '';
$iid   = isset($_REQUEST['iid'])   ? (int)$_REQUEST['iid'] : 0;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;

if (empty($mod)) {
    echo '<div class="board2">';
    echo '<form action="infa.php?mod=uzinfa" method="GET">
    <input type="hidden" name="mod" value="uzinfa" />
    Введите логин:<br/>
    <input type="text" class="ptext" name="lgn" />
    <input type="submit" class="button" value="Поиск"/>
    </form></div>';
    knopka('infa.php?mod=rate', 'Рейтинг игроков', 1);
    knopka('infa.php?mod=blok', 'Заблокированные', 1);
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    fin();
}

if ($mod == 'onl') {
    $timer = $t - 86400;
    $q = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `lastdate` > '{$timer}';");
    $count_sutki = $q ? (int)$q->fetch_assoc()['c'] : 0;

    $timer1 = $t - 900;
    $timer2 = $t - 7200;
    $q = $db->query("SELECT `login`, `lvl`, `sex`, `status`, `rabota`, `klan` FROM `users` WHERE (`lastdate` > '{$timer1}' OR (`status` = 1 AND `lastdate` > '{$timer2}')) ORDER BY `login`;");
    $count_onl = $q ? $q->num_rows : 0;

    echo '<div class="board2" style="text-align:left">';
    echo 'Онлайн: ' . $count_onl . ', за сутки: ' . $count_sutki . '</div>';

    $count = 0;
    if ($q) {
        while ($array_onl = $q->fetch_assoc()) {
            $color_login = ($array_onl['sex'] == 1) ? $male : $female;
            $count++;
            $str = $count . '. <span style="color:' . $color_login . '">' . $array_onl['login'] . ' [' . $array_onl['lvl'] . ']</span>';
            if (!empty($array_onl['klan'])) $str .= ' (' . $array_onl['klan'] . ')';
            if ($array_onl['status'] == 1) $str .= ' [Б]';
            if ($array_onl['rabota'] > time()) $str .= ' <span style="color:' . $female . '">[Р]</span>';
            knopka('infa.php?mod=uzinfa&lgn=' . $array_onl['login'], $str);
        }
    }
    echo '</div>';
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    fin();
}

if ($mod == 'rate') {
    echo '<div class="board2">Топ 10:</div>';
    $count = 0;
    $numb = 10;
    $q = $db->query("SELECT COUNT(`id`) AS `c` FROM `users` WHERE `admin` < 3 AND `flag_blok` = 0;");
    $allg = $q ? (int)$q->fetch_assoc()['c'] : 0;
    if ($start > (int)($allg / $numb)) $start = (int)($allg / $numb);
    if ($start < 0) $start = 0;
    $limit = $start * $numb;
    $q = $db->query("SELECT `login`, `lvl`, `sex`, `exp` FROM `users` WHERE `admin` < 3 AND `flag_blok` = 0 ORDER BY `lvl` DESC, `exp` DESC, `login` LIMIT {$limit}, {$numb};");
    if ($q) {
        while ($array_onl = $q->fetch_assoc()) {
            $color_login = ($array_onl['sex'] == 1) ? $male : $female;
            $count++;
            knopka('infa.php?mod=uzinfa&lgn=' . $array_onl['login'], $count . '. <span style="color:' . $color_login . '">' . $array_onl['login'] . ' [' . $array_onl['lvl'] . ']</span>');
        }
    }
    echo '<div class="board" style="text-align:center">';
    if ($start > 0) echo '<a href="infa.php?mod=rate&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"><-Назад</a>';
    echo ' | ';
    if ($limit + $numb < $allg) echo '<a href="infa.php?mod=rate&start=' . ($start + 1) . '" class="navig">Вперед-></a>'; else echo '<a href="#" class="navig">Вперед-></a>';
    echo '</div>';
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    fin();
}

if ($mod == 'who') {
    $timer1 = $t - 300;
    $q = $db->query("SELECT `login`, `lvl`, `sex`, `status`, `klan` FROM `users` WHERE `loc` = " . (int)$f['loc'] . " AND `login` <> '" . $db->real_escape_string($f['login']) . "' AND `lastdate` > '{$timer1}' ORDER BY `lvl`;");
    $count = 0;
    if (!$q || $q->num_rows == 0) {
        msg2('Рядом никого нет');
    } else {
        msg2('Рядом с вами:');
        while ($array_onl = $q->fetch_assoc()) {
            $color_login = ($array_onl['sex'] == 1) ? $male : $female;
            $count++;
            $str = $count . '. <span style="color:' . $color_login . '">' . $array_onl['login'] . ' [' . $array_onl['lvl'] . ']</span>';
            if (!empty($array_onl['klan'])) $str .= ' (' . $array_onl['klan'] . ')';
            if ($array_onl['status'] == 1) $str .= ' [Б]';
            knopka('infa.php?mod=uzinfa&lgn=' . $array_onl['login'], $str);
        }
    }
    fin();
}

if ($mod == 'uzinfa') {
    $l = get_login($lgn);
    $lgn = $l['login'];

    echo ' <div class="board2">';
    echo '<b>' . $lgn . '</b>';
    if ($l['admin'] > 0) {
        echo '[<span style="color:' . $male . '">';
        if ($l['admin'] == 1) echo 'М';
        if ($l['admin'] == 2) echo 'СМ';
        if ($l['admin'] == 3) echo 'А';
        if ($l['admin'] >= 4) echo 'СА';
        echo '</span>] ';
    }

    $pol = ($l['sex'] == 1) ? ' Был' : ' Была';
    if ($l['lastdate'] < $t - 300) echo '<br/><u>' . $pol . ' ' . date('d.m.Y H:i', (int)$l['lastdate']) . '</u>';
    else echo '<br/><span style="color:' . $notice . '"><b> В игре</b></span>';

    if (!empty($l['brak'])) echo '<br/> В браке с: <a href="infa.php?mod=uzinfa&lgn=' . $l['brak'] . '">' . $l['brak'] . '</a>';

    echo '<br/><a href="infa.php?mod=lookboi&lgn=' . $lgn . '">';
    if ($l['status'] == 1) echo '<span style="color:' . $female . '">В бою</span>';
    else echo '<span style="color:' . $male . '">Последний Бой</span>';
    echo '</a>';
    echo '</div>';

    if ($l['rabota'] > time()) echo '<div class="board">*<small><span style="color:#003333"><b>Персонаж работает</b></span></small></div>';
    if (!empty($l['altar']) && $l['altar_time'] > time()) echo '<div class="board">*<small><span style="color:#003333">Персонаж под защитой алтаря</span></small></div>';
    if ($l['doping'] > 0 && $l['doping_time'] > $t) {
        echo '<div class="board">*<small>Допинг: <span style="color:' . $female . '"><b>';
        $doping_names = [1=>'Брага',2=>'Пиво',3=>'Вино',4=>'Самогон',5=>'Рисовый шнапс',6=>'Ловкость',7=>'Реакция',8=>'Жизненная энергия',9=>'Магическая энергия',10=>'Вышибала'];
        echo $doping_names[(int)$l['doping']] ?? '?';
        echo '</b></span></small></div>';
    }

    if ($l['flag_blok'] == 1) {
        msg('Персонаж заблокирован.<br/>Причина: ' . $l['zachto_blok'] . '<br/>');
        if ($l['ban'] > 0) {
            echo '<div class="board2">Блок до ' . date('d.m.Y H:i', (int)$l['ban']);
            echo '</div>'; // ИСПРАВЛЕНО: раньше </div> был не закрыт
        }
    }

    echo '<div class="board">';
    $pol = ($l['sex'] == 1) ? 'мужской' : 'женский';
    echo '
    Уровень: ' . $l['lvl'] . '<br/>
    Имя: ' . $l['name'] . '<br/>
    Пол: ' . $pol . '<br/>';
    if (!empty($l['klan'])) {
        echo 'Клан: ' . $l['klan'];
        if ($l['klan_status'] > 0) {
            echo ' (<span style="color:' . $male . '">';
            if ($l['klan_status'] == 3) echo 'Глава';
            elseif ($l['klan_status'] == 2) echo 'Наместник';
            elseif ($l['klan_status'] == 1) echo 'Зам. главы';
            echo '</span>)';
        }
        echo '<br/>PvP-Статус: ';
        if ($l['pvp'] == 1) echo '<span style="color:green"><b>On</b></span>';
        else echo '<span style="color:red"><b>Off</b></span>';
    }
    if (empty($l['klan']) && empty($l['klan_invite']) && $f['klan_status'] >= 2 && 4 <= $l['lvl']) {
        knopka2('klan.php?mod=priem&lgn=' . $l['login'], 'Пригласить в клан');
    }

    echo '<br/>Бои: <span style="color:' . $male . '">' . $l['win'] . '</span> |
    <span style="color:' . $female . '">' . $l['lost'] . '</span><br/>';
    echo 'Регистрация: ' . date('d.m.Y H:i', (int)$l['regdate']);
    echo '</div>';

    if ($lgn != $f['login']) {
        knopka('infa.php?mod=oruzh&lgn=' . $lgn, 'Экипировка');
        knopka('pm.php?mod=dialog&lgn=' . $lgn, 'Написать Письмо');
        knopka('inv.php?lgn=' . $lgn, 'Передать Вещь');
        knopka('inv.php?mod=money&lgn=' . $lgn, 'Передать Монеты');
    }
    if (1 <= $f['admin']) {
        knopka('adm.php?lgn=' . $lgn . '', 'Управление Профилем');
        echo '<div class="board">';
        echo 'Монеты: ' . $l['money'] . '<br/>';
        if (!empty($l['bank'])) echo 'Банк: ' . $l['bank'] . '<br/>';
        if (!empty($l['ruda'])) echo 'Руда: ' . $l['ruda'] . '<br/>';
        echo '</div>';
    }
    if (!empty($l['infa'])) msg('<small><b><u>' . $lgn . ' Пишет</u></b>:<br/><br/> ' . $l['infa'] . '</small>');
    fin();
}

if ($mod == 'oruzh') {
    echo '<div class="board2" style="text-align:left">Список Снаряжения и Статы:<br/>';
    $l = get_login($lgn);
    $lgn = $l['login'];
    echo 'HP: ' . $l['hpnow'] . ' / ' . $l['hpmax'] . '<br/>';
    echo 'MP: ' . $l['mananow'] . ' / ' . $l['manamax'] . '<br/>';
    echo 'Сила: ' . $l['sila'] . '<br/>';
    echo 'Ловкость: ' . $l['lovka'] . '<br/>';
    echo 'Интуиция: ' . $l['inta'] . '<br/>';
    echo 'Интеллект: ' . $l['intel'] . '<br/>';
    echo 'Здоровье: ' . $l['zdor'] . '<br/>';
    echo '</div>';

    $login_esc = $db->real_escape_string($lgn);
    $q = $db->query("SELECT `invent`.`id` FROM `invent`, `item` WHERE ((`invent`.`login` = '{$login_esc}' AND `invent`.`flag_arenda` = 0) OR (`invent`.`arenda_login` = '{$login_esc}' AND `invent`.`flag_arenda` = 1)) AND `invent`.`flag_rinok` = 0 AND `invent`.`flag_equip` = 1 AND (`item`.`equip` <> '' AND `item`.`equip` <> 'sumka') AND `invent`.`ido` = `item`.`id`;");
    if (!$q || $q->num_rows == 0) msg2('На ' . $lgn . ' ничего не надето!', 1);

    $count = 0;
    echo '<div class="board">';
    while ($eq = $q->fetch_assoc()) {
        $item = $items->shmot((int)$eq['id']);
        if ($item === null) continue;
        if (!empty($count)) echo '<br/>';
        $slot_names = ['prruka'=>'Правая рука','lruka'=>'Левая рука','dospeh'=>'Доспех','golova'=>'Голова','kolco'=>'Кольцо','amulet'=>'Амулет','nogi'=>'Ноги','plaw'=>'Плащ','braslet'=>'Браслет','pojas'=>'Пояс'];
        echo ($slot_names[$item['equip']] ?? '?') . ': ';
        $count++;
        echo '<b><a href="infa.php?mod=iteminfa&iid=' . $item['id'] . '"><span style="color:#003333">' . $item['name'] . '</span></a></b>';
    }
    echo '</div>';
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    fin();
}

if ($mod == 'lookboi') {
    $l = get_login($lgn);
    $lgn = $l['login'];

    // Ищем последний бой игрока
    $login_esc = $db->real_escape_string($lgn);
    $q = $db->query("SELECT * FROM `battle` WHERE `login` = '{$login_esc}' OR `login2` = '{$login_esc}' ORDER BY `id` DESC LIMIT 1;");
    $boi = $q ? $q->fetch_assoc() : null;

    if (!$boi) {
        msg2('У персонажа ' . $lgn . ' ещё не было боёв.');
        knopka('javascript:history.go(-1)', 'Вернуться', 1);
        fin();
    }

    $bid = (int)$boi['id'];
    $goboi = !empty($boi['flag_boi']) ? 1 : 0;

    echo '<div class="board" style="text-align:left">';
    $km1 = ''; $km2 = ''; $eof1 = ''; $eof2 = ''; $str1 = ''; $str2 = '';

    if ($goboi == 1) {
        $q = $db->query("SELECT * FROM `combat` WHERE `boi_id` = '{$bid}';");
        if ($q) {
            while ($bz = $q->fetch_assoc()) {
                if ($bz['komanda'] == 1) {
                    if ($bz['hpnow'] > 0) $km1 .= '<span style="color:' . $notice . '">' . $bz['login'] . '</span> [' . $bz['lvl'] . '] (' . $bz['hpnow'] . '/' . $bz['hpmax'] . ') урон: ' . $bz['uron_boi'] . '<br/>';
                    $eof1 .= $bz['login'] . ' (' . $bz['hpnow'] . '/' . $bz['hpmax'] . ')<br/>';
                } else {
                    if ($bz['hpnow'] > 0) $km2 .= '<span style="color:' . $male . '">' . $bz['login'] . '</span> [' . $bz['lvl'] . '] (' . $bz['hpnow'] . '/' . $bz['hpmax'] . ') урон: ' . $bz['uron_boi'] . '<br/>';
                    $eof2 .= $bz['login'] . ' (' . $bz['hpnow'] . '/' . $bz['hpmax'] . ')<br/>';
                }
            }
        }
        $q = $db->query("SELECT * FROM `battlelog` WHERE `boi_id` = '{$bid}' ORDER BY `id` DESC;");
        $str1 = $km1 . 'VS<br/>' . $km2 . '<hr/>';
        $str2 = '<hr/>' . $eof1 . 'VS<br/>' . $eof2;
    } else {
        $q = $db->query("SELECT * FROM `battlelog` WHERE `boi_id` = '{$bid}' ORDER BY `id`;");
    }

    $str = '';
    if ($q) {
        while ($log = $q->fetch_assoc()) {
            $str .= $log['log'];
        }
    }
    fin($str1 . $str . $str2 . '<a href="javascript:history.go(-1)">Назад</a>');
}

if ($mod == 'iteminfa') {
    if ($iid <= 0) msg2('Вещь не найдена.', 1);
    $q = $db->query("SELECT `id` FROM `invent` WHERE `id` = '{$iid}' LIMIT 1;");
    $itm = $q ? $q->fetch_assoc() : null;
    if (!$itm) msg2('Вещь не найдена.', 1);
    $item = $items->shmot((int)$itm['id']);
    if ($item === null) msg2('Вещь не найдена.', 1);

    echo '<div class="board2"><b>' . $item['name'] . '</b>';
    if (!empty($item['art'])) echo ' [<font color=green><b>' . $item['art'] . '</b></font>]';
    echo '<br/><small>[id: ' . $item['id'] . ']</small>';
    echo '</div>';
    echo '<div class="board" style="text-align:left;">';
    if ($item['flag_arenda'] == 1) echo 'В аренде у <a href="infa.php?mod=uzinfa&lgn=' . $item['arenda_login'] . '">' . $item['arenda_login'] . '</a><br/>';
    elseif ($item['flag_rinok'] == 1) echo 'Продается на рынке<br/>';
    elseif ($item['flag_sklad'] == 1) echo 'Лежит на складе<br/>';
    else echo 'Принадлежит: <a href="infa.php?mod=uzinfa&lgn=' . $item['login'] . '">' . $item['login'] . '</a><br/>';
    echo 'Уровень: ' . $item['lvl'] . '</b><br/>Цена: ' . $item['price'] . ' <br/>';
    if ($item['hp'] > 0) {
        $hp = (int)ceil($item['hp'] + ($item['up'] * $item['hp'] / 100));
        echo 'Жизни: <font color="red"> +' . $hp . '</font>';
    }
    echo '</div>';
    if (!empty($item['info'])) msg($item['info']);
    else msg('Нет описания');

    if (!empty($item['equip'])) {
        echo '<div class="board" style="text-align:left;">';
        echo '<b>Характеристики</b>:<br/>';
        if ($item['krit'] > 0)   echo 'Крит: '   . (int)ceil($item['krit']   + ($item['up'] * $item['krit']   / 100)) . '<br/>';
        if ($item['uvorot'] > 0) echo 'Уворот: ' . (int)ceil($item['uvorot'] + ($item['up'] * $item['uvorot'] / 100)) . '<br/>';
        if ($item['uron'] > 0)   echo 'Урон: '   . (int)ceil($item['uron']   + ($item['up'] * $item['uron']   / 100)) . '<br/>';
        if ($item['bron'] > 0)   echo 'Броня: '  . (int)ceil($item['bron']   + ($item['up'] * $item['bron']   / 100)) . '<br/>';
        echo '</div>';
        echo '<div class="board" style="text-align:left;">';
        echo '<b>Требования</b>:<br/>';
        if ($item['zdor'] > 0)  echo 'Здоровье: '  . $item['zdor']  . ' <font color=green> [' . $f['zdor']  . ']</font><br/>';
        if ($item['sila'] > 0)  echo 'Сила: '      . $item['sila']  . ' <font color=green> [' . $f['sila']  . ']</font><br/>';
        if ($item['inta'] > 0)  echo 'Интуиция: '  . $item['inta']  . ' <font color=green> [' . $f['inta']  . '] </font> <br/>';
        if ($item['lovka'] > 0) echo 'Ловкость: '  . $item['lovka'] . ' <font color=green> [' . $f['lovka'] . '] </font> <br/>';
        if ($item['intel'] > 0) echo 'Интеллект: ' . $item['intel'] . ' <font color=green> [' . $f['intel'] . '] </font> <br/>';
        if (!empty($item['up'])) knopka2('shop.php?mod=iteminfa&iid=' . $item['ido'], 'Базовые параметры вещи');

        // ИСПРАВЛЕНО: приоритет and/or — раньше было (A and B) or C or D...
        $need_restat = $item['zdor'] > $f['zdor']
            || $item['inta'] > $f['inta']
            || $item['sila'] > $f['sila']
            || $item['lovka'] > $f['lovka']
            || $item['intel'] > $f['intel'];
        $is_owner = (($f['login'] == $item['arenda_login'] && $item['flag_arenda'] == 1) || ($f['login'] == $item['login'] && $item['flag_arenda'] == 0));
        if ($need_restat && $is_owner) {
            knopka2('anketa.php?mod=stats&ok=1&stat_zdor=' . $item['zdor'] . '&stat_sila=' . $item['sila'] . '&stat_inta=' . $item['inta'] . '&stat_lovka=' . $item['lovka'] . '&stat_intel=' . $item['intel'], 'Расставить статы под эту вещь');
        }
        echo '</div>';
    }
    echo '</div>';
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    fin();
}

if ($mod == 'blok') {
    echo '<div class="board2">Список заблокированых:</div>';
    $count = 0;
    $db->query("UPDATE `users` SET `flag_blok` = 0, `ban` = 0 WHERE (`ban` < '{$t}' AND `ban` > 0);");
    $q = $db->query("SELECT `lvl`, `login`, `ban`, `zachto_blok` FROM `users` WHERE `flag_blok` = 1 ORDER BY `lastdate` DESC;");
    if ($q) {
        while ($Arr = $q->fetch_assoc()) {
            $count++;
            $str = $count . '. ' . $Arr['login'] . ' [' . $Arr['lvl'] . '] // ' . $Arr['zachto_blok'];
            if ($Arr['ban'] > time()) $str .= ' (до ' . date('d.m.Y H:i', (int)$Arr['ban']) . ')';
            knopka('infa.php?mod=uzinfa&lgn=' . $Arr['login'], $str);
        }
    }
    echo '</div>';
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    fin();
}

if ($mod == 'klans') {
    echo '<div class="board2">Кланы:</div>';
    $count = 0;
    $q = $db->query("SELECT * FROM `klans` ORDER BY `lvl` DESC, `points` DESC, `name`;");
    if (!$q || $q->num_rows == 0) msg2('Нет ни одного клана', 1);
    while ($sostav = $q->fetch_assoc()) {
        $count++;
        knopka('infa.php?mod=sostav&lgn=' . $sostav['id'], $count . '. ' . $sostav['name'] . ' [' . $sostav['lvl'] . ']');
    }
    echo '</div>';
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    fin();
}

if ($mod == 'boi') {
    $count = 0;
    $q = $db->query("SELECT * FROM `battle` WHERE (`krov` = 3 OR `krov` = 4 OR `krov` = 5) AND `flag_boi` = 1 ORDER BY `id` DESC;");
    if (!$q || $q->num_rows == 0) msg2('Нет ни одного боя', 1);
    while ($b = $q->fetch_assoc()) {
        $str = '';
        if ($b['krov'] == 3) $str = ' (Свиток развоплощения)';
        if ($b['krov'] == 4) $str = ' (Бой на арене)';
        if ($b['krov'] == 5) $str = ' (Свиток нападения)';
        knopka('infa.php?mod=lookboi&lgn=' . $b['login'], date('H:i:s', (int)$b['boistart']) . ' - ' . $b['login'] . ' против ' . $b['login2'] . $str);
    }
    fin();
}

if ($mod == 'sostav') {
    $count = 0;
    $lgn = (int)$lgn;
    if ($lgn < 1) $lgn = 1;
    $q = $db->query("SELECT `name`, `lvl`, `points` FROM `klans` WHERE `id` = '{$lgn}' LIMIT 1;");
    $kkk = $q ? $q->fetch_assoc() : null;
    if (!$kkk) msg2('Клан не найден', 1);
    $klan_esc = $db->real_escape_string($kkk['name']);
    $q = $db->query("SELECT `login`, `lvl`, `sex`, `exp`, `klan_status`, `lastdate` FROM `users` WHERE `klan` = '{$klan_esc}' AND `flag_blok` = 0 ORDER BY `lvl` DESC, `exp` DESC, `login`;");
    echo '</div><div class="board2">';
    echo 'Клан: ' . $kkk['name'];
    echo '<br/> Уровень: ' . $kkk['lvl'];
    echo '<br/>Прогресс:  (' . $kkk['points'] . '/' . ($kkk['lvl'] * 1000) . ')';
    echo '</div>';
    if ($q) {
        while ($sostav = $q->fetch_assoc()) {
            $color_login = ($sostav['sex'] == 1) ? $male : $female;
            $count++;
            echo '<div class="board" style="text-align:left;">';
            echo $count . '. ';
            echo '<a href="infa.php?mod=uzinfa&lgn=' . $sostav['login'] . '"><span style="color:' . $color_login . '">' . $sostav['login'] . ' [' . $sostav['lvl'] . ']</span></a> ';
            if ($sostav['klan_status'] == 3) echo '[<b>Глава</b>] ';
            elseif ($sostav['klan_status'] == 2) echo '[<b>Наместник</b>] ';
            elseif ($sostav['klan_status'] == 1) echo '[<b>Зам. Главы</b>] ';
            $raznica = time() - (int)$sostav['lastdate'];
            if ($raznica <= 300) $onl = '<span style="color:' . $notice . '">В игре</span> ';
            elseif ($raznica <= 3600) $onl = ceil($raznica / 60) . ' мин. назад';
            elseif ($raznica <= 86400) $onl = ceil($raznica / 3600) . ' чс. назад';
            else $onl = ceil($raznica / 86400) . ' дн. назад';
            echo $onl . '<br/></div>';
        }
    }
    echo '</div>';
    knopka('javascript:history.go(-1)', 'Вернуться', 1);
    fin();
}

fin();
