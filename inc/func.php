<?php
/**
 * Ядро функций.
 * PHP 8.2-совместимая версия.
 */

// Настройки (читаем один раз)
$q = $db->query("SELECT * FROM `settings` WHERE `id` = 1 LIMIT 1;");
$settings = $q ? $q->fetch_assoc() : ['name' => 'Миртания', 'bot' => 'system', 'admin' => 'maslanin', 'reg' => 1, 'mess' => ''];

/**
 * Фильтрация ввода: trim + htmlspecialchars + real_escape_string.
 * ВНИМАНИЕ: применяется дважды к одним и тем же данным — потенциальное
 * двойное экранирование. В новой версии — только htmlspecialchars для вывода
 * и prepared statements для SQL.
 */
function ekr($a)
{
    if ($a === null) {
        return '';
    }
    $db = DBC::instance();
    $a = trim((string)$a);
    $a = htmlspecialchars($a, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $a = $db->real_escape_string($a);
    return $a;
}

/**
 * Пересчёт параметров персонажа (статы, экипировка, алтарь, допинг).
 */
function calcparam($s)
{
    if (!is_array($s) || empty($s['login'])) {
        return $s;
    }
    $db = DBC::instance();
    $items = items::instance();

    $s['krit']   = ($s['inta']  ?? 0) * 10;
    $s['uvorot'] = ($s['lovka'] ?? 0) * 10;
    $s['uron']   = (int)(($s['sila'] ?? 0) * 1.2);
    $s['bron']   = ($s['sila'] ?? 0);
    $s['hpmax']  = ($s['zdor']  ?? 0) * 10;
    $s['manamax']= ($s['intel'] ?? 0) * 10;
    if ($s['hpmax'] < 10)   $s['hpmax'] = 10;
    if ($s['manamax'] < 10) $s['manamax'] = 10;

    if (!isset($s['art']) || !is_array($s['art'])) {
        $s['art'] = [];
    }

    $login_esc = $db->real_escape_string($s['login']);
    $q = $db->query("SELECT `invent`.`id` FROM `invent`, `item` WHERE ((`invent`.`login` = '{$login_esc}' AND `invent`.`flag_arenda` = 0) OR (`invent`.`arenda_login` = '{$login_esc}' AND `invent`.`flag_arenda` = 1)) AND `invent`.`flag_rinok` = 0 AND `invent`.`flag_equip` = 1 AND (`item`.`equip` <> '' AND `item`.`equip` <> 'sumka') AND `invent`.`ido` = `item`.`id`;");
    if ($q !== false) {
        while ($a = $q->fetch_assoc()) {
            $item = $items->shmot((int)$a['id']);
            if ($item === null) continue;
            $up = (int)($item['up'] ?? 0);
            $s['krit']   += (int)ceil($item['krit']   + ($up * $item['krit']   / 100));
            $s['uvorot'] += (int)ceil($item['uvorot'] + ($up * $item['uvorot'] / 100));
            $s['uron']   += (int)ceil($item['uron']   + ($up * $item['uron']   / 100));
            $s['bron']   += (int)ceil($item['bron']   + ($up * $item['bron']   / 100));
            $s['hpmax']  += (int)ceil($item['hp']     + ($up * $item['hp']     / 100));
            if (!empty($item['art'])) {
                if (!isset($s['art'][$item['art']])) $s['art'][$item['art']] = 0;
                $s['art'][$item['art']]++;
            }
        }
    }

    if (!empty($s['altar']) && $s['altar'] > 0) {
        if (!empty($s['altar_time']) && $s['altar_time'] < time()) {
            $s['altar'] = 0;
            $s['altar_time'] = 0;
            if (!empty($s['id'])) {
                $db->query("UPDATE `users` SET `altar` = 0, `altar_time` = 0 WHERE `id` = " . (int)$s['id'] . " LIMIT 1;");
            }
        }
    }
    if (!empty($s['altar']) && $s['altar'] > 0) {
        $mnozh = $s['altar'] * 0.01;
        $s['krit']   += (int)ceil($s['krit']   * $mnozh);
        $s['uvorot'] += (int)ceil($s['uvorot'] * $mnozh);
        $s['uron']   += (int)ceil($s['uron']   * $mnozh);
        $s['bron']   += (int)ceil($s['bron']   * $mnozh);
    }

    if (!empty($s['doping']) && $s['doping'] > 0) {
        if (!empty($s['doping_time']) && $s['doping_time'] < time()) {
            $s['doping'] = 0;
            $s['doping_time'] = 0;
            if (!empty($s['id'])) {
                $db->query("UPDATE `users` SET `doping` = 0, `doping_time` = 0 WHERE `id` = " . (int)$s['id'] . " LIMIT 1;");
            }
        }
    }
    if (!empty($s['doping']) && $s['doping'] > 0) {
        require __DIR__ . '/doping.php';
    }

    if (!empty($s['id'])) {
        $db->query("UPDATE `users` SET `krit` = " . (int)$s['krit'] . ", `uvorot` = " . (int)$s['uvorot'] . ", `bron` = " . (int)$s['bron'] . ", `uron` = " . (int)$s['uron'] . ", `hpmax` = " . (int)$s['hpmax'] . ", `manamax` = " . (int)$s['manamax'] . " WHERE `id` = " . (int)$s['id'] . " LIMIT 1;");
    }
    return $s;
}

/**
 * Смайлы.
 * TODO: свернуть в массив.
 */
function smile($s)
{
    $map = [
        '.афтар.'    => 'aftar.gif',
        '.бан.'      => 'ban.gif',
        '.банан.'    => 'banan.gif',
        '.банан1.'   => 'banan1.gif',
        '.бомж.'     => 'bomj.gif',
        '.браво.'    => 'bravo.gif',
        '.чмак.'     => 'chmak.gif',
        '.дедмороз.' => 'dedmoroz.gif',
        '.дети.'     => 'deti.gif',
        '.днюха.'    => 'denrojd.gif',
        '.добрый.'   => 'dobrij.gif',
        '.достали.'  => 'dostali.gif',
        '.драка.'    => 'draka.gif',
        '.дум.'      => 'dum.gif',
        '.душ.'      => 'dush.gif',
        '.дятел.'    => 'djatel.gif',
        '.елка.'     => 'elka.gif',
        '.ёлка.'     => 'elka.gif',
        '.фан.'      => 'fan.gif',
        '.фанаты.'   => 'fans.gif',
        '.фигасе.'   => 'figase.gif',
        '.флаг.'     => 'flag.gif',
        '.флаг1.'    => 'flag1.gif',
        '.флуд.'     => 'flud.gif',
        '.говнецо.'  => 'govneco.gif',
        '.грабли.'   => 'grabli.gif',
        '.грамота.'  => 'gramota.gif',
        '.сердце.'   => 'heart.gif',
        '.хор.'      => 'hor.gif',
        '.истерика.' => 'isterika.gif',
        '.яд.'       => 'jad.gif',
        '.карты.'    => 'karty.gif',
        '.каток.'    => 'katok.gif',
        '.король.'   => 'king.gif',
        '.конфета.'  => 'konfeta.gif',
        '.кофе.'     => 'kofe.gif',
        '.комп.'     => 'komp.gif',
        '.конфетти.' => 'konfetti.gif',
        '.конь.'     => 'konj.gif',
        '.курю.'     => 'kurju.gif',
        '.ладно.'    => 'ladno.gif',
        '.ляля.'     => 'ljalja.gif',
        '.медик.'    => 'medic.gif',
        '.молоток.'  => 'molotok.gif',
        '.нефлуди.'  => 'nefludi.gif',
        '.новыйгод.' => 'newyear.gif',
        '.небань.'   => 'noban.gif',
        '.номер.'    => 'nomer.gif',
        '.ох.'       => 'oh.gif',
        '.пасиба.'   => 'pasiba.gif',
        '.песочница.'=> 'pesochnica.gif',
        '.пионер.'   => 'pioner.gif',
        '.письмо.'   => 'pismo.gif',
        '.пифпаф.'   => 'pifpaf.gif',
        '.пиво.'     => 'pivo.gif',
        '.плак.'     => 'plac.gif',
        '.плохо.'    => 'ploho.gif',
        '.плюсодин.' => 'plusodin.gif',
        '.побили.'   => 'pobili.gif',
        '.подарок.'  => 'podarok.gif',
        '.пока.'     => 'poka.gif',
        '.попа.'     => 'popa.gif',
        '.превед.'   => 'preved.gif',
        '.привет.'   => 'privet.gif',
        '.прыг.'     => 'pryg.gif',
        '.репка.'    => 'repka.gif',
        '.ромашка.'  => 'romashka.gif',
        '.роза.'     => 'roza.gif',
        '.русский.'  => 'russkij.gif',
        '.русский1.' => 'russkij1.gif',
        '.ржу.'      => 'rzhu.gif',
        '.секас.'    => 'sekas.gif',
        '.семья.'    => 'semja.gif',
        '.сиськи.'   => 'siski.gif',
        '.смех.'     => 'smeh.gif',
        '.сигарета.' => 'smoke.gif',
        '.солнце.'   => 'solnce.gif',
        '.спам.'     => 'spam.gif',
        '.стих.'     => 'stih.gif',
        '.сцуко.'    => 'scuko.gif',
        '.свадьба.'  => 'svadba.gif',
        '.свист.'    => 'svist.gif',
        '.согласен.' => 'soglasen.gif',
        '.танцы.'    => 'tancy.gif',
        '.тема.'     => 'tema.gif',
        '.тормоз.'   => 'tormoz.gif',
        '.туса.'     => 'tusa.gif',
        '.утро.'     => 'utro.gif',
        '.велик.'    => 'velik.gif',
        '.велком.'   => 'wellcome.gif',
        '.вестерн.'  => 'vestern.gif',
        '.винсент.'  => 'vinsent.gif',
        '.язык.'     => 'yazik.gif',
        '.зяфк.'     => 'zjafk.gif',
    ];
    foreach ($map as $code => $file) {
        $s = str_replace($code, '<img src="smile/' . $file . '"/>', $s);
    }
    return $s;
}

/**
 * Отправка UTF-8 почты.
 */
function mail_utf8($to, $subject = '(No subject)', $message = '', $from = 'noreply@localhost')
{
    $header = "MIME-Version: 1.0\n"
        . "Content-type: text/plain; charset=UTF-8\n"
        . "From: " . $from . "\n";
    return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, $header, '-f ' . $from);
}

/**
 * Автолинковка URL и email.
 */
function link_it($s)
{
    $s = preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"out.php?url=$3\">$3</a>", $s);
    $s = preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"out.php?url=http://$3\">$3</a>", $s);
    $s = preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\ .]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $s);
    return $s;
}

/**
 * Строка с датой последней новости.
 */
function DateNews()
{
    $db = DBC::instance();
    $q = $db->query("SELECT `datenews` FROM `news` ORDER BY `id` DESC LIMIT 1;");
    if ($q === false || $q->num_rows === 0) {
        return 'Новостей нет';
    }
    $news = $q->fetch_assoc();
    return 'Новости (' . date('d.m.Y H:i', (int)$news['datenews']) . ')';
}

/**
 * Получить персонажа по логину.
 * Завершает скрипт, если логин не найден (как в старом коде).
 */
function get_login($lgn = '')
{
    $db = DBC::instance();
    if ($lgn === '' || $lgn === null) {
        msg('Неверно набран логин!', 1);
    }
    if (!preg_match('/^[a-zA-Z0-9_а-яА-ЯёЁ]+$/u', $lgn)) {
        msg('Неверно набран логин!', 1);
    }
    $lgn_esc = $db->real_escape_string($lgn);
    $q = $db->query("SELECT * FROM `users` WHERE `login` = '{$lgn_esc}' LIMIT 1;");
    if ($q === false || $q->num_rows === 0) {
        msg('Логин не найден!', 1);
    }
    return $q->fetch_assoc();
}

/**
 * Разница в днях между датой и текущей.
 */
function GetDay($s)
{
    $another = mktime(0, 0, 0, (int)date('m', $s), (int)date('d', $s), (int)date('Y', $s));
    $now     = mktime(0, 0, 0, (int)date('m'), (int)date('d'), (int)date('Y'));
    $diff = (int)(($now - $another) / 86400);
    if ($diff < 0) $diff *= -1;
    return $diff;
}

/**
 * Очки клана.
 */
function klan_points($name, $i)
{
    $db = DBC::instance();
    $name_esc = $db->real_escape_string($name);
    $q = $db->query("SELECT * FROM `klans` WHERE `name` = '{$name_esc}' LIMIT 1;");
    if ($q === false || $q->num_rows === 0) {
        return false;
    }
    $a = $q->fetch_assoc();
    if ($a['points'] >= $a['lvl'] * 1000) {
        $a['points'] = 0;
        $a['lvl'] += 1;
    }
    if ($a['points'] + $i < 0) {
        $a['points'] = 0;
    } else {
        $a['points'] += $i;
    }
    $db->query("UPDATE `klans` SET `points` = " . (int)$a['points'] . ", `lvl` = " . (int)$a['lvl'] . " WHERE `name` = '{$name_esc}' LIMIT 1;");
    return true;
}

/**
 * Добавить опыт.
 */
function addexp($id, $num)
{
    $db = DBC::instance();
    $id = (int)$id;
    $num = (int)$num;
    $db->query("UPDATE `users` SET `exp` = `exp` + {$num} WHERE `id` = {$id} LIMIT 1;");
    return true;
}

/**
 * Вывод сообщения в board2.
 */
function msg($s = '', $stop = 0)
{
    echo '<div class="board2">' . $s . '</div>';
    echo '<div style="width:100%;height:4px;border:0;position:relative;background-color:#8b7e66;margin:0;text-align:center;"></div>';
    if (!empty($stop)) {
        if (!empty($_SESSION['auth'])) {
            knopka('javascript:history.go(-1)', 'Вернуться');
            knopka('loc.php', 'В игру');
        }
        fin();
    }
    return true;
}

/**
 * Вывод сообщения в board + board2.
 */
function msg2($s = '', $stop = 0)
{
    echo '<div class="board"><div class="board2">' . $s . '</div></div>';
    echo '<div style="width:100%;height:4px;border:0;position:relative;background-color:#8b7e66;margin:0;text-align:center;"></div>';
    if (!empty($stop)) {
        if (!empty($_SESSION['auth'])) {
            knopka('javascript:history.go(-1)', 'Вернуться');
            knopka('loc.php', 'В игру');
        }
        fin();
    }
    return true;
}

/**
 * Вывод сообщения в board.
 */
function msg3($s = '', $stop = 0)
{
    echo '<div class="board">' . $s . '</div>';
    echo '<div style="width:100%;height:4px;border:0;position:relative;background-color:#8b7e66;margin:0;text-align:center;"></div>';
    if (!empty($stop)) {
        if (!empty($_SESSION['auth'])) {
            knopka('javascript:history.go(-1)', 'Вернуться');
            knopka('loc.php', 'В игру');
        }
        fin();
    }
    return true;
}

/**
 * Кнопка (menu_j).
 */
function knopka($url, $name = '', $kart = 0)
{
    if (empty($url))  $url  = 'http://' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
    if (empty($name)) $name = $url;
    echo '<div class="menu_j"><a href="' . $url . '" class="top_menu_j">';
    if (!empty($kart)) echo '<img src="pic/k.png" alt=""/> ';
    echo $name . '</a></div>';
    return true;
}

/**
 * Кнопка (main-knopki).
 */
function knopka2($url, $name)
{
    if (empty($url))  $url  = 'http://' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
    if (empty($name)) $name = $url;
    echo '<a class="main-knopki" href="' . $url . '">' . $name . '</a>';
    return true;
}

/**
 * Финализация: закрыть body, вывести время, exit.
 */
function fin($s = '')
{
    global $time_start;
    if (!empty($s)) echo $s . '<br/>';
    echo '</div>';
    $time_end = microtime(true);
    $alltime = round($time_end - $time_start, 5);
    echo '<div class="head" align="center">';
    echo $alltime . ' сек';
    echo '</div>';
    echo '<center><small>';
    echo '<font color="white">Игра на реконструкции';
    echo '</small>';
    // ИСПРАВЛЕНО: не меняем $_SERVER['REQUEST_TIME'] (суперглобал!)
    if (empty($_SESSION['mobtop']) || $_SESSION['mobtop'] < time()) {
        // Внешний счётчик отключён, чтобы не тормозить загрузку
        $_SESSION['mobtop'] = time() + mt_rand(200, 600);
    }
    echo '</center></body></html>';
    exit();
}
