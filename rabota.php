<?php
/**
 * Работа на шахтах.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: не было fin() после неверной капчи — работа засчитывалась.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';

if ($f['status'] == 1) {
    knopka('battle.php', 'Вы в бою!', 1);
    fin();
}

$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : '';
$keystring = isset($_REQUEST['keystring']) ? $_REQUEST['keystring'] : '';

$db->query("UPDATE `users` SET `lastdate` = '{$t}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");

require_once __DIR__ . '/inc/hpstring.php';

/**
 * Проверка капчи.
 */
function rabota_check_captcha($keystring)
{
    if (!isset($_SESSION['bez']) || $_SESSION['bez'] != $keystring) {
        msg('Вы ввели неверный код с картинки!', 1);
    }
}

// Локация 19: Старая шахта
if ($f['loc'] == 19) {
    echo '<div class="board">';
    $nagrada = (int)$f['lvl'] * 6;
    if ($nagrada < 6) $nagrada = 6;
    if ($f['vip'] > time()) {
        $nagrada = (int)$f['lvl'] * 10;
    }
    if ($f['rabota'] > 0) {
        if ($f['rabota'] > time()) {
            $ost = $f['rabota'] - time();
            msg('Вам осталось отработать ' . ceil($ost / 60) . ' мин.', 1);
        } else {
            $f['money'] += $nagrada;
            echo 'Получено монет: ' . $nagrada . '<br/>';
            $nalog = (int)ceil($nagrada * 0.1);
            $f['nalog'] += $nalog;
            $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . ", `nalog` = " . (int)$f['nalog'] . ", `rabota` = 0 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
            msg('Вы отработали свои 30 минут.');
            echo '</div>';
            knopka('rabota.php', 'Работать еще', 1);
            fin();
        }
    } else {
        if (empty($ok)) {
            echo '<form action="rabota.php?ok=1" method="POST">';
            echo 'Вы видите, как несколько измученных рудокопов молотят кирками по рудной жиле. Ваша зарплата составит ' . $nagrada . ' монет. Вы решаете:<br/>';
            echo '<img src="bez.php?r=' . mt_rand(11111, 99999) . '"><br/><input type="text" name="keystring"><br/>';
            echo '<input type="submit" value="Присоединиться" /></form>';
            fin();
        }
        rabota_check_captcha($keystring);
        $rabota = time() + 1800;
        $db->query("UPDATE `users` SET `rabota` = {$rabota} WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        echo '</div>';
        msg('Вы присоединяетесь к рудокопам, один из них отдает вам кирку и идет отдыхать. Работать вам придется 30 минут, в это время перемещаться по миру нельзя, а браузер можно закрыть.', 1);
    }
}
// Локация 44: Свободная шахта
elseif ($f['loc'] == 44) {
    echo '<div class="board">';
    if ($f['lvl'] < 10) msg2('Вы недостаточно сильны, приходите когда будете хотябы 10 уровня', 1);
    $nagrada = (int)$f['lvl'] * 10;
    if ($nagrada < 10) $nagrada = 10;
    if ($f['vip'] > time()) {
        $nagrada = (int)$f['lvl'] * 15;
    }
    if ($f['rabota'] > 0) {
        if ($f['rabota'] > time()) {
            $ost = $f['rabota'] - time();
            msg('Вам осталось отработать ' . ceil($ost / 60) . ' мин.', 1);
        } else {
            $f['money'] += $nagrada;
            $nalog = (int)ceil($nagrada * 0.1);
            $f['nalog'] += $nalog;
            $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . ", `nalog` = " . (int)$f['nalog'] . ", `rabota` = 0 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
            msg('Получено монет: ' . $nagrada);
            echo '</div>';
            knopka('rabota.php', 'Работать еще', 1);
            fin();
        }
    } else {
        if (empty($ok)) {
            echo '<form action="rabota.php?ok=1" method="POST">';
            echo 'Вы хотите наняться охранять шахту. Зарплата на ваш уровень составит ' . $nagrada . ' монет:<br/>';
            echo '<img src="bez.php?r=' . mt_rand(11111, 99999) . '"><br/><input type="text" name="keystring"><br/>';
            echo '<input type="submit" value="Присоединиться" /></form>';
            fin();
        }
        rabota_check_captcha($keystring);
        $rabota = time() + 1800;
        $db->query("UPDATE `users` SET `rabota` = '{$rabota}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        echo '</div>';
        msg('Вы присоединились к надзирателям, в вашу задачу входит обеспечивать порядок на шахте в течении 30 минут. Браузер можно закрыть, перемещаться по миру нельзя.', 1);
    }
}
// Локация 8: заброшенная шахта (для клана)
elseif ($f['loc'] == 8 && !empty($f['klan'])) {
    echo '<div class="board">';
    $nagrada = mt_rand((int)ceil($f['lvl'] / 2), (int)$f['lvl']);
    if ($f['rabota'] > 0) {
        if ($f['rabota'] > time()) {
            $ost = $f['rabota'] - time();
            msg('Вам осталось отработать ' . ceil($ost / 60) . ' мин.', 1);
        } else {
            $money = $nagrada * mt_rand(10, 15);
            echo 'Вы добыли ' . $nagrada . ' камней для своего клана<br/>Казна получает ' . $money . ' монет<br/>';
            $log = $f['login'] . ' [' . $f['lvl'] . '] добывает для клана ' . $nagrada . ' камней и ' . $money . ' монет.';
            $klan_esc = $db->real_escape_string($f['klan']);
            $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '{$klan_esc}', '{$t}');");
            $db->query("UPDATE `klans` SET `kamni` = `kamni` + '{$nagrada}', `kazna` = `kazna` + '{$money}' WHERE `name` = '{$klan_esc}' LIMIT 1;");
            $db->query("UPDATE `users` SET `rabota` = 0 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
            echo '</div>';
            knopka('rabota.php', 'Добывать еще', 1);
            fin();
        }
    } else {
        if (empty($ok)) {
            echo '<form action="rabota.php?ok=1" method="POST">';
            echo 'В этой заброшенной шахте до сих пор попадаются камни для строительства<br/>';
            echo '<img src="bez.php?r=' . mt_rand(11111, 99999) . '"><br/><input type="text" name="keystring"><br/>';
            echo '<input type="submit" value="Добывать камни" /></form>';
            fin();
        }
        rabota_check_captcha($keystring);
        $rabota = time() + 600;
        $db->query("UPDATE `users` SET `rabota` = {$rabota} WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        echo '</div>';
        msg('Добывать камни вам придется 10 минут, в это время перемещаться по миру нельзя, а браузер можно закрыть.', 1);
    }
} else {
    knopka('loc.php', 'Ошибка локации!', 1);
    fin();
}
