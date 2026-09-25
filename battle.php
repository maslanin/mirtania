<?php
/**
 * Движок боя.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';

$mod   = isset($_REQUEST['mod'])   ? $_REQUEST['mod']   : '';
$magic = isset($_REQUEST['magic']) ? (int)$_REQUEST['magic'] : 0;
$flag_boi = 1;

// Праздники
$prazdn = 0;
$date = date('d.m');
$gd = getdate();
if (in_array($date, ['29.12','30.12','31.12','01.01','02.01','14.01','14.02','23.02','08.03','01.04','01.05','02.05','03.05','09.05','01.06','12.06','12.12'], true)) $prazdn = 1;
if ($gd['yday'] == 255) $prazdn = 1;

if ($f['status'] == 2) {
    knopka('arena.php', 'У вас заявка на арене', 1);
    fin();
}

/**
 * Подготовка бойца: полные статы с экипировкой/допингом.
 */
function prepareFighter(array $fighter, ?array $userData): array
{
    if (!empty($fighter['flag_bot'])) {
        $fighter['krit']   = (int)$fighter['inta'] * 10;
        $fighter['uvorot'] = (int)$fighter['lovka'] * 10;
        $fighter['uron']   = (int)($fighter['sila'] * 0.9);
        $fighter['bron']   = (int)$fighter['sila'];
        $fighter['ref']    = 0;
        $fighter['vip']    = 0;
        $fighter['intel']  = 0;
        $fighter['klan']   = '';
        $fighter['sopr']   = 0;
        return $fighter;
    }
    if ($userData) {
        $fighter['intel']         = (int)$userData['intel'];
        $fighter['zdor']          = (int)$userData['zdor'];
        $fighter['doping']        = (int)$userData['doping'];
        $fighter['doping_time']   = (int)$userData['doping_time'];
        $fighter['altar']         = (int)$userData['altar'];
        $fighter['altar_time']    = (int)$userData['altar_time'];
        $fighter = calcparam($fighter);
        $fighter['ref']           = (int)$userData['ref'];
        $fighter['vip']           = (int)$userData['vip'];
        $fighter['klan']          = $userData['klan'];
    }
    return $fighter;
}

// Проверим, есть ли бой
$q = $db->query("SELECT * FROM `battle` WHERE `id` = " . (int)$f['boi_id'] . " LIMIT 1;");
if ($q === false || $q->num_rows == 0) $flag_boi = 0;
$boi = $q ? $q->fetch_assoc() : null;
$bid = $boi ? (int)$boi['id'] : 0;
if ($boi && $boi['flag_boi'] == 0) $flag_boi = 0;

if ($boi && empty($boi['login'])) {
    $db->query("UPDATE `battle` SET `login` = '" . $db->real_escape_string($f['login']) . "' WHERE `id` = {$bid} LIMIT 1;");
}

if (empty($flag_boi)) {
    require_once __DIR__ . '/inc/hpstring.php';
    knopka('loc.php', 'Бой завершен', 1);
    $db->query("UPDATE `users` SET `status` = 0, `boi_id` = 0, `lastdate` = '{$t}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    if ($bid > 0) {
        $db->query("DELETE FROM `combat` WHERE `boi_id` = " . (int)$bid . ";");
        $db->query("UPDATE `battle` SET `flag_boi` = 0 WHERE `id` = " . (int)$bid . " LIMIT 1;");
    } else {
        $db->query("DELETE FROM `combat` WHERE `login` = '" . $db->real_escape_string($f['login']) . "';");
    }
    if ($bid > 0) {
        $q = $db->query("SELECT `log` FROM `battlelog` WHERE `boi_id` = '{$bid}' ORDER BY `id` DESC LIMIT 3;");
        echo '<div class="board" style="text-align:left">';
        if ($q) {
            while ($stlog = $q->fetch_assoc()) echo $stlog['log'];
        }
        echo '</div>';
    }
    fin();
}

$curtime = time();
$curdate = date('H:i:s');
$final_log = '';
$komanda1 = '';
$komanda2 = '';
$kom1 = [];
$kom2 = [];
$kom1sum = 0;
$kom2sum = 0;
$logboi = '';
$me = [];
$uz = [];
$hodtime = 120;
$pkstr_display = '';
$ost = 20 - ($curtime - (int)$boi['sbrospar']);
if ($ost < 0) $ost = 0;

// Данные обо мне из combat
$q = $db->query("SELECT * FROM `combat` WHERE `boi_id` = {$bid} AND `login` = '" . $db->real_escape_string($f['login']) . "' LIMIT 1;");
$me = $q ? $q->fetch_assoc() : null;
if (!$me) {
    $db->query("UPDATE `users` SET `status` = 0, `boi_id` = 0 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    $db->query("DELETE FROM `combat` WHERE `login` = '" . $db->real_escape_string($f['login']) . "';");
    require_once __DIR__ . '/inc/hpstring.php';
    msg2('Вы не в бою.');
    knopka('loc.php', 'В игру', 1);
    if ($bid > 0) {
        $q = $db->query("SELECT `log` FROM `battlelog` WHERE `boi_id` = '{$bid}' ORDER BY `id` DESC LIMIT 3;");
        if ($q && $q->num_rows > 0) {
            echo '<div class="board" style="text-align:left">';
            while ($stlog = $q->fetch_assoc()) echo $stlog['log'];
            echo '</div>';
        }
    }
    fin();
}

// Загружаем полные статы для $me
$q = $db->query("SELECT `intel`, `doping`, `doping_time`, `altar`, `altar_time`, `zdor`, `sila`, `lovka`, `inta`, `ref`, `vip`, `klan` FROM `users` WHERE `login` = '" . $db->real_escape_string($f['login']) . "' LIMIT 1;");
$me_user = $q ? $q->fetch_assoc() : null;
$me = prepareFighter($me, $me_user);

// Загружаем полные статы для $uz (если есть соперник)
if (!empty($me['sopernik'])) {
    $q = $db->query("SELECT * FROM `combat` WHERE `id` = " . (int)$me['sopernik'] . " LIMIT 1;");
    $uz = $q ? $q->fetch_assoc() : null;
    if ($uz) {
        if (empty($uz['flag_bot'])) {
            $q = $db->query("SELECT `intel`, `doping`, `doping_time`, `altar`, `altar_time`, `zdor`, `sila`, `lovka`, `inta`, `ref`, `vip`, `klan` FROM `users` WHERE `login` = '" . $db->real_escape_string($uz['login']) . "' LIMIT 1;");
            $uz_user = $q ? $q->fetch_assoc() : null;
            $uz = prepareFighter($uz, $uz_user);
        } else {
            $uz = prepareFighter($uz, null);
        }
    }
}

// Считаем % для отображения (до удара)
if ($uz) {
    $p_hit = (int)(($me['krit'] / max(1, $uz['uvorot'])) * 40);
    if ($p_hit > 95) $p_hit = 95;
    if ($p_hit < 5) $p_hit = 5;
    $u_hit = (int)(($uz['krit'] / max(1, $me['uvorot'])) * 40);
    if ($u_hit > 95) $u_hit = 95;
    if ($u_hit < 5) $u_hit = 5;
    $u_dodge = 100 - $u_hit;
    $pkstr_display = '<small>П: <b>' . $p_hit . '%</b> · У: <b>' . $u_dodge . '%</b></small>';
}

// Если запрос удара есть — записываем
if (!empty($_REQUEST['ud']) && !empty($_REQUEST['bl']) && (empty($me['kuda_udar']) || empty($me['kuda_blok']))) {
    $me['kuda_udar'] = (int)$_REQUEST['ud'];
    $me['kuda_blok'] = (int)$_REQUEST['bl'];
    $db->query("UPDATE `combat` SET `kuda_udar` = " . (int)$me['kuda_udar'] . ", `kuda_blok` = " . (int)$me['kuda_blok'] . " WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
}

// Удар
if (!empty($me['kuda_udar']) && $me['hpnow'] > 0 && !empty($me['sopernik'])) {
    if (!$uz) {
        $db->query("UPDATE `combat` SET `sopernik` = 0 WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
        msg2('Противник не найден!');
        knopka('battle.php', 'Обновить', 1);
        fin();
    }
    if ($uz['hpnow'] <= 0) {
        $db->query("UPDATE `combat` SET `sopernik` = 0 WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
        msg2('Ваш противник уже убит!');
        knopka('battle.php', 'Обновить', 1);
        fin();
    }

    if (!empty($uz['kuda_udar']) && !empty($uz['kuda_blok'])) {
        $uron_zaudar   = mt_rand((int)$me['uron'], (int)($me['uron'] * 2));
        $s_uron_zaudar = mt_rand((int)$uz['uron'], (int)($uz['uron'] * 2));

        if ($me['krit'] < 1) $me['krit'] = 1;
        if ($me['uvorot'] < 1) $me['uvorot'] = 1;
        if ($uz['krit'] < 1) $uz['krit'] = 1;
        if ($uz['uvorot'] < 1) $uz['uvorot'] = 1;
        if (!isset($me['intel']) || $me['intel'] < 1) $me['intel'] = 1;
        if (!isset($uz['intel']) || $uz['intel'] < 1) $uz['intel'] = 1;

        $me['sopr'] = (int)($me['bron'] * 0.1 + $me['intel']);
        if ($me['sopr'] > 99) $me['sopr'] = 99;
        $uz['sopr'] = (int)($uz['bron'] * 0.1 + $uz['intel']);
        if ($uz['sopr'] > 99) $uz['sopr'] = 99;

        if (empty($me['kuda_udar'])) $me['kuda_udar'] = 1;
        if (empty($me['kuda_blok'])) $me['kuda_blok'] = 1;
        if (empty($uz['kuda_udar'])) $uz['kuda_udar'] = 1;
        if (empty($uz['kuda_blok'])) $uz['kuda_blok'] = 1;
        if ($me['kuda_udar'] < 1 || $me['kuda_udar'] > 3) $me['kuda_udar'] = 1;
        if ($me['kuda_blok'] < 1 || $me['kuda_blok'] > 3) $me['kuda_blok'] = 1;
        if ($uz['flag_bot'] == 1) {
            if ($me['kuda_udar'] == 1 && $uz['kuda_blok'] == 1) $uz['kuda_blok'] = 2;
            if ($me['kuda_udar'] == 2 && $uz['kuda_blok'] == 2) $uz['kuda_blok'] = 3;
            if ($me['kuda_udar'] == 3 && $uz['kuda_blok'] == 3) $uz['kuda_blok'] = 1;
        }
        $ud_str  = ['голову','грудь','ноги'][$me['kuda_udar'] - 1];
        $ud_str2 = ['голову','грудь','ноги'][$uz['kuda_udar'] - 1];

        $kubik = mt_rand(-6, 6);
        $raznica = (int)($me['krit'] / $uz['uvorot'] * 10);
        if ($raznica < 3) $raznica = 3;
        $raznica += $kubik;
        if ($raznica <= 4) $kakoy_udar = 0;
        elseif ($raznica < 16) $kakoy_udar = 1;
        elseif ($raznica < 25) $kakoy_udar = 2;
        else $kakoy_udar = 3;

        $kubik = mt_rand(-6, 6);
        $raznica = (int)($uz['krit'] / $me['uvorot'] * 10);
        if ($raznica < 3) $raznica = 3;
        $raznica += $kubik;
        if ($raznica <= 4) $s_kakoy_udar = 0;
        elseif ($raznica < 16) $s_kakoy_udar = 1;
        elseif ($raznica < 25) $s_kakoy_udar = 2;
        else $s_kakoy_udar = 3;

        // Удар игрока
        if ($kakoy_udar == 0) {
            $uron_zaudar = 0;
            if ($me['kuda_udar'] != $uz['kuda_blok'] || $uz['flag_bot'] == 1) $udar_log = $uz['login'] . ' уходит от удара в ' . $ud_str . '<br/>';
            else $udar_log = $me['login'] . ' бьет в ' . $ud_str . ' ' . $uz['login'] . ', но попадает в блок<br/>';
        } elseif ($kakoy_udar == 1) {
            if ($me['kuda_udar'] != $uz['kuda_blok'] || $uz['flag_bot'] == 1) {
                $uron_zaudar = (int)($uron_zaudar - $uz['bron'] * 0.5);
                if ($uron_zaudar < 1) $uron_zaudar = 1;
                $udar_log = $me['login'] . ' бьет в ' . $ud_str . ' и наносит ' . $uz['login'] . ' урон ' . $uron_zaudar . '<br/>';
            } else {
                $uron_zaudar = 0;
                $udar_log = $me['login'] . ' бьет в ' . $ud_str . ' ' . $uz['login'] . ', но попадает в блок<br/>';
            }
        } elseif ($kakoy_udar == 2) {
            if ($me['kuda_udar'] != $uz['kuda_blok'] || $uz['flag_bot'] == 1) {
                $uron_zaudar = (int)($uron_zaudar * 1.8 - $uz['bron'] * 0.33);
                if ($uron_zaudar < 1) $uron_zaudar = 1;
                $udar_log = $me['login'] . ' бьет резким ударом в ' . $ud_str . ' и наносит ' . $uz['login'] . ' урон <b>' . $uron_zaudar . '</b><br/>';
            } else {
                $uron_zaudar = 0;
                $udar_log = $me['login'] . ' бьет резким ударом в ' . $ud_str . ' ' . $uz['login'] . ', но попадает в блок<br/>';
            }
        } else {
            if ($me['kuda_udar'] != $uz['kuda_blok'] || $uz['flag_bot'] == 1) {
                $uron_zaudar = (int)($uron_zaudar * 2.2 - $uz['bron'] * 0.33);
                if ($uron_zaudar < 1) $uron_zaudar = 1;
                $udar_log = $me['login'] . ' бьет критическим ударом в ' . $ud_str . ' и наносит ' . $uz['login'] . ' урон <b><span style="color:red">' . $uron_zaudar . '</span></b><br/>';
            } else {
                $uron_zaudar = 0;
                $udar_log = $me['login'] . ' бьет критическим ударом в ' . $ud_str . ' ' . $uz['login'] . ', но попадает в блок<br/>';
            }
        }

        // Удар противника
        if ($s_kakoy_udar == 0) {
            $s_uron_zaudar = 0;
            if ($uz['kuda_udar'] != $me['kuda_blok'] || $uz['flag_bot'] == 1) $s_udar_log = $me['login'] . ' уходит от удара в ' . $ud_str2 . '<br/>';
            else $s_udar_log = $uz['login'] . ' бьет в ' . $ud_str2 . ' ' . $me['login'] . ', но попадает в блок<br/>';
        } elseif ($s_kakoy_udar == 1) {
            if ($uz['kuda_udar'] != $me['kuda_blok'] || $uz['flag_bot'] == 1) {
                $s_uron_zaudar = (int)($s_uron_zaudar - $me['bron'] * 0.5);
                if ($s_uron_zaudar < 1) $s_uron_zaudar = 1;
                $s_udar_log = $uz['login'] . ' бьет в ' . $ud_str2 . ' и наносит ' . $me['login'] . ' урон ' . $s_uron_zaudar . '<br/>';
            } else {
                $s_uron_zaudar = 0;
                $s_udar_log = $uz['login'] . ' бьет в ' . $ud_str2 . ' ' . $me['login'] . ', но попадает в блок<br/>';
            }
        } elseif ($s_kakoy_udar == 2) {
            if ($uz['kuda_udar'] != $me['kuda_blok'] || $uz['flag_bot'] == 1) {
                $s_uron_zaudar = (int)($s_uron_zaudar * 1.8 - $me['bron'] * 0.33);
                if ($s_uron_zaudar < 1) $s_uron_zaudar = 1;
                $s_udar_log = $uz['login'] . ' бьет резким ударом в ' . $ud_str2 . ' и наносит ' . $me['login'] . ' урон <b>' . $s_uron_zaudar . '</b><br/>';
            } else {
                $s_uron_zaudar = 0;
                $s_udar_log = $uz['login'] . ' бьет резким ударом в ' . $ud_str2 . ' ' . $me['login'] . ', но попадает в блок<br/>';
            }
        } else {
            if ($uz['kuda_udar'] != $me['kuda_blok'] || $uz['flag_bot'] == 1) {
                $s_uron_zaudar = (int)($s_uron_zaudar * 2.2 - $me['bron'] * 0.33);
                if ($s_uron_zaudar < 1) $s_uron_zaudar = 1;
                $s_udar_log = $uz['login'] . ' бьет критическим ударом в ' . $ud_str2 . ' и наносит ' . $me['login'] . ' урон <b><span style="color:red">' . $s_uron_zaudar . '</span></b><br/>';
            } else {
                if (mt_rand(1, 100) <= 75) $s_uron_zaudar = 0;
                $s_udar_log = $uz['login'] . ' бьет критическим ударом в ' . $ud_str2 . ' ' . $me['login'] . ', но попадает в блок<br/>';
            }
        }

        $boi['round'] = (int)$boi['round'] + 1;
        require __DIR__ . '/inc/art.php';

        $me['hpnow'] -= $s_uron_zaudar;
        $uz['hpnow'] -= $uron_zaudar;
        $me['uron_boi'] = (int)$me['uron_boi'] + $uron_zaudar;
        $uz['uron_boi'] = (int)$uz['uron_boi'] + $s_uron_zaudar;
        $me['kuda_udar'] = 0;
        $me['kuda_blok'] = 0;
        $me['time_udar'] = $curtime;

        $db->query("UPDATE `battle` SET `round` = " . (int)$boi['round'] . " WHERE `id` = {$bid} LIMIT 1;");
        $db->query("UPDATE `combat` SET `uron_boi` = " . (int)$me['uron_boi'] . ", `hpnow` = " . (int)$me['hpnow'] . ", `time_udar` = '{$t}', `kuda_udar` = 0, `kuda_blok` = 0, `boi_round` = `boi_round` + 1 WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
        $db->query("UPDATE `combat` SET `uron_boi` = " . (int)$uz['uron_boi'] . ", `hpnow` = " . (int)$uz['hpnow'] . ", `time_udar` = '{$t}', `kuda_udar` = 0, `kuda_blok` = 0, `boi_round` = `boi_round` + 1 WHERE `id` = " . (int)$uz['id'] . " LIMIT 1;");
        if (empty($me['flag_bot'])) $db->query("UPDATE `users` SET `hpnow` = " . (int)$me['hpnow'] . " WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        if (empty($uz['flag_bot'])) $db->query("UPDATE `users` SET `hpnow` = " . (int)$uz['hpnow'] . " WHERE `login` = '" . $db->real_escape_string($uz['login']) . "' LIMIT 1;");

        $points = 0;
        $kill_log = '';

        if ($me['hpnow'] <= 0 && $uz['hpnow'] > 0) {
            $moneyfor = mt_rand((int)$me['lvl'] - 1, (int)$me['lvl'] * 2 + 1);
            if ($moneyfor < 1) $moneyfor = 1;
            if ($moneyfor > $uz['lvl'] * 2) $moneyfor = $uz['lvl'] * 2;
            $nalog = (int)ceil($moneyfor * 0.1);
            if ($nalog < 1) $nalog = 1;
            if ($uz['vip'] > $curtime) $moneyfor = (int)ceil(2 * $moneyfor);
            if ($prazdn == 1) $moneyfor *= 2;
            if (!empty($uz['ref'])) $db->query("UPDATE `users` SET `money` = `money` + " . (int)ceil($moneyfor * 0.05) . " WHERE `id` = " . (int)$uz['ref'] . " LIMIT 1;");
            if (!empty($uz['klan'])) klan_points($uz['klan'], 2);
            if (!empty($me['klan'])) klan_points($me['klan'], -1);
            if ($uz['flag_bot'] == 0) {
                $kill_log = '<span style="color:' . $notice . '">' . $me['login'] . ' погибает. ' . $uz['login'] . ' получает ' . $moneyfor . ' монет</span><br/>' . $kill_log;
                if (!empty($uz['klan']) && !empty($me['klan']) && $me['klan'] != $uz['klan'] && $uz['lvl'] - $me['lvl'] < 2 && $boi['krov'] == 3) {
                    $points = mt_rand((int)($me['lvl'] / 2), (int)$me['lvl'] + 2);
                    if ($points < 1) $points = 1;
                    $kill_log = '<span style="color:yellow;">' . $uz['login'] . ' получает ' . $points . ' очков чести</span><br/>' . $kill_log;
                }
                $db->query("UPDATE `users` SET `money` = `money` + '{$moneyfor}', `nalog` = `nalog` + '{$nalog}', `chest` = `chest` + '{$points}' WHERE `login` = '" . $db->real_escape_string($uz['login']) . "' LIMIT 1;");
            } else {
                $kill_log = '<span style="color:' . $notice . '">' . $me['login'] . ' погибает</span><br/>' . $kill_log;
            }
            $db->query("UPDATE `combat` SET `sopernik` = 0 WHERE `id` = " . (int)$uz['id'] . " OR `id` = " . (int)$me['id'] . " LIMIT 2;");
        }

        $points = 0;
        $nalog = 0;
        if ($uz['hpnow'] <= 0 && $me['hpnow'] > 0) {
            $moneyfor = mt_rand((int)$uz['lvl'] - 1, (int)$uz['lvl'] * 2 + 1);
            if ($moneyfor < 1) $moneyfor = 1;
            if ($moneyfor > $me['lvl'] * 2) $moneyfor = $me['lvl'] * 2;
            $nalog = (int)ceil($moneyfor * 0.1);
            if ($nalog < 1) $nalog = 1;
            if ($me['vip'] > $curtime) $moneyfor = (int)ceil(2 * $moneyfor);
            if ($prazdn == 1) $moneyfor *= 2;
            if (!empty($me['ref'])) $db->query("UPDATE `users` SET `money` = `money` + " . (int)ceil($moneyfor * 0.05) . " WHERE `id` = " . (int)$me['ref'] . " LIMIT 1;");
            if (!empty($me['klan'])) klan_points($me['klan'], 2);
            if (!empty($uz['klan'])) klan_points($uz['klan'], -1);
            $kill_log = '<span style="color:' . $notice . '">' . $uz['login'] . ' погибает. ' . $me['login'] . ' получает ' . $moneyfor . ' монет</span><br/>' . $kill_log;
            if (!empty($uz['klan']) && !empty($me['klan']) && $me['klan'] != $uz['klan'] && $me['lvl'] - $uz['lvl'] < 2 && $boi['krov'] == 3) {
                $points = mt_rand((int)($uz['lvl'] / 2), (int)$uz['lvl'] + 2);
                if ($points < 1) $points = 1;
                $kill_log = '<span style="color:yellow;">' . $me['login'] . ' получает ' . $points . ' очков чести</span><br/>' . $kill_log;
            }
            $db->query("UPDATE `users` SET `money` = `money` + '{$moneyfor}', `nalog` = `nalog` + '{$nalog}', `chest` = `chest` + '{$points}' WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
            require __DIR__ . '/inc/drop.php';
            $db->query("UPDATE `combat` SET `sopernik` = 0 WHERE `id` = " . (int)$uz['id'] . " OR `id` = " . (int)$me['id'] . " LIMIT 2;");
        }

        if ($uz['hpnow'] <= 0 && $me['hpnow'] <= 0) {
            if (!empty($uz['klan'])) klan_points($uz['klan'], 1);
            if (!empty($me['klan'])) klan_points($me['klan'], 1);
            if ($uz['login'] == 'Тролль' || $uz['login'] == 'Дракон') require __DIR__ . '/inc/drop.php';
            $kill_log = '<span style="color:' . $notice . '">' . $uz['login'] . ' погибает. ' . $me['login'] . ' погибает.</span><br/>' . $kill_log;
        }

        $hp_string = '<span style="color:' . $male . '">' . $curdate . ' (' . $boi['round'] . '): <b>' . $me['login'] . '</b> [' . $me['lvl'] . '] (' . $me['hpnow'] . '/' . $me['hpmax'] . ')';
        if (!empty($art_uron)) $hp_string .= ', арт: ' . $art_uron;
        if (!empty($art_hp))   $hp_string .= ', леч: ' . $art_hp;
        $hp_string .= ' VS <b>' . $uz['login'] . '</b> [' . $uz['lvl'] . '] (' . $uz['hpnow'] . '/' . $uz['hpmax'] . ')';
        if (!empty($s_art_uron)) $hp_string .= ', арт: ' . $s_art_uron;
        if (!empty($s_art_hp))   $hp_string .= ', леч: ' . $s_art_hp;
        $hp_string .= '</span><br/>';

        $logboi_new = $hp_string . $kill_log . $s_udar_log . $udar_log . '<br/>';
        $db->query("INSERT INTO `battlelog` VALUES (0, '{$bid}', '{$t}', '" . $db->real_escape_string($logboi_new) . "');");
    } else {
        if ($uz['time_udar'] + $hodtime > $curtime && $uz['flag_bot'] == 0) {
            knopka('battle.php', 'Ожидание хода противника', 1);
            $db->query("UPDATE `combat` SET `time_udar` = '{$t}' WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
        }
    }
}

// mod
switch ($mod) {
    case 'sumka':
        if ($me['hpnow'] > 0) require __DIR__ . '/inc/sumka.php';
        break;
    case 'magic':
        if ($me['hpnow'] > 0) require __DIR__ . '/inc/magic.php';
        break;
    case 'long':
        require_once __DIR__ . '/inc/hpstring.php';
        knopka('battle.php', 'Вернуться', 1);
        $q = $db->query("SELECT `log` FROM `battlelog` WHERE `boi_id` = '{$bid}' ORDER BY `id` DESC;");
        if ($q) {
            while ($stlog = $q->fetch_assoc()) {
                echo '<div class="board" style="text-align:left">' . $stlog['log'] . '</div>';
            }
        }
        fin();
        break;
    case 'sbrospar':
        if ($boi['sbrospar'] < $curtime - 20 && $me['hpnow'] > 0) {
            $db->query("UPDATE `combat` SET `sopernik` = 0 WHERE `boi_id` = '{$bid}';");
            $db->query("UPDATE `battle` SET `sbrospar` = '{$t}' WHERE `id` = '{$bid}' LIMIT 1;");
            msg2('Вы сбросили пары');
        }
        break;
    case 'whowhere':
        msg2('Кто в бою:');
        $q = $db->query("SELECT `login`, `lvl`, `hpnow`, `hpmax`, `komanda`, `flag_bot`, `uron_boi` FROM `combat` WHERE `boi_id` = {$bid} ORDER BY `komanda`, `lvl` DESC;");
        if ($q && $q->num_rows > 0) {
            while ($b = $q->fetch_assoc()) {
                $team = ($b['komanda'] == 1) ? 'Команда 1' : 'Команда 2';
                $hp_color = ($b['hpnow'] > 0) ? 'green' : 'red';
                echo '<div class="board2" style="text-align:left">';
                if (empty($b['flag_bot'])) {
                    echo '<a href="infa.php?mod=uzinfa&lgn=' . urlencode($b['login']) . '"><b>' . htmlspecialchars($b['login'], ENT_QUOTES, 'UTF-8') . '</b></a>';
                } else {
                    echo '<b>' . htmlspecialchars($b['login'], ENT_QUOTES, 'UTF-8') . '</b> <span style="color:#888;">[бот]</span>';
                }
                echo ' [' . $b['lvl'] . '] — ';
                echo '<span style="color:' . $hp_color . '">' . $b['hpnow'] . '/' . $b['hpmax'] . '</span>';
                echo ' — ' . $team;
                echo ' — урон: ' . $b['uron_boi'];
                echo '</div>';
            }
        } else {
            echo '<div class="board2">В бою никого нет.</div>';
        }
        knopka('battle.php', 'Вернуться в бой', 1);
        fin();
        break;
}

// 3 последних записи лога
$q = $db->query("SELECT `log` FROM `battlelog` WHERE `boi_id` = '{$bid}' ORDER BY `id` DESC LIMIT 3;");
if ($q) {
    while ($stlog = $q->fetch_assoc()) {
        $logboi .= $stlog['log'];
    }
}

if (empty($logboi)) {
    $logboi = $me['login'] . ' начинает бой в ' . date('H:i:s', (int)$boi['boistart']) . '<br/>';
    $db->query("INSERT INTO `battlelog` VALUES (0, '{$bid}', '{$t}', '" . $db->real_escape_string($logboi) . "');");
}

// Разбор бойцов
$q = $db->query("SELECT * FROM `combat` WHERE `boi_id` = '{$bid}';");
if ($q) {
    while ($bz = $q->fetch_assoc()) {
        if ($bz['time_udar'] < $curtime - 1800 && $bz['hpnow'] > 0) {
            $bz['hpnow'] = 0;
            $bz['mananow'] = 0;
            if (empty($bz['flag_bot'])) $db->query("UPDATE `users` SET `hpnow` = 0, `mananow` = 0 WHERE `login` = '" . $db->real_escape_string($bz['login']) . "' LIMIT 1;");
            $db->query("UPDATE `combat` SET `hpnow` = 0, `mananow` = 0 WHERE `boi_id` = {$bid} AND `id` = " . (int)$bz['id'] . " LIMIT 1;");
        }
        if ($bz['time_udar'] < $curtime - $hodtime && $bz['hpnow'] > 0) {
            $db->query("UPDATE `combat` SET `kuda_udar` = 2, `kuda_blok` = 2 WHERE `boi_id` = {$bid} AND `id` = " . (int)$bz['id'] . " LIMIT 1;");
        }
        if ($bz['flag_bot'] == 1 && $bz['hpnow'] > 0 && (empty($bz['kuda_udar']) || empty($bz['kuda_blok']))) {
            $bz['kuda_udar'] = mt_rand(1, 3);
            $bz['kuda_blok'] = mt_rand(1, 3);
            $db->query("UPDATE `combat` SET `kuda_udar` = " . (int)$bz['kuda_udar'] . ", `kuda_blok` = " . (int)$bz['kuda_blok'] . " WHERE `boi_id` = {$bid} AND `id` = " . (int)$bz['id'] . " LIMIT 1;");
        }
        if ($bz['hpnow'] > 0) {
            if ($bz['komanda'] == 1) {
                $kom1sum++;
                if (empty($bz['sopernik'])) $kom1[] = (int)$bz['id'];
                if (empty($bz['flag_bot'])) $komanda1 .= '<a href="infa.php?mod=uzinfa&lgn=' . $bz['login'] . '"><span style="color:' . $notice . '">' . $bz['login'] . '</span></a> [' . $bz['lvl'] . '] (' . $bz['hpnow'] . '/' . $bz['hpmax'] . ') урон: ' . $bz['uron_boi'] . '<br/>';
                else $komanda1 .= '<span style="color:' . $notice . '">' . $bz['login'] . '</span> [' . $bz['lvl'] . '] (' . $bz['hpnow'] . '/' . $bz['hpmax'] . ') урон: ' . $bz['uron_boi'] . '<br/>';
            } else {
                $kom2sum++;
                if (empty($bz['sopernik'])) $kom2[] = (int)$bz['id'];
                if (empty($bz['flag_bot'])) $komanda2 .= '<a href="infa.php?mod=uzinfa&lgn=' . $bz['login'] . '"><span style="color:' . $male . '">' . $bz['login'] . '</span></a> [' . $bz['lvl'] . '] (' . $bz['hpnow'] . '/' . $bz['hpmax'] . ') урон: ' . $bz['uron_boi'] . '<br/>';
                else $komanda2 .= '<span style="color:' . $male . '">' . $bz['login'] . '</span> [' . $bz['lvl'] . '] (' . $bz['hpnow'] . '/' . $bz['hpmax'] . ') урон: ' . $bz['uron_boi'] . '<br/>';
            }
        }
    }
}

// Разбить на пары
while (count($kom1) > 0 && count($kom2) > 0) {
    shuffle($kom1);
    shuffle($kom2);
    $rand1 = mt_rand(0, count($kom1) - 1);
    $rand2 = mt_rand(0, count($kom2) - 1);
    $boeckm1 = $kom1[$rand1];
    $boeckm2 = $kom2[$rand2];
    unset($kom1[$rand1]);
    unset($kom2[$rand2]);
    $kom1 = array_values($kom1);
    $kom2 = array_values($kom2);
    $db->query("UPDATE `combat` SET `sopernik` = '{$boeckm1}' WHERE `id` = '{$boeckm2}' LIMIT 1;");
    $db->query("UPDATE `combat` SET `sopernik` = '{$boeckm2}' WHERE `id` = '{$boeckm1}' LIMIT 1;");
    if ($boeckm1 == $me['id']) $me['sopernik'] = $boeckm2;
    if ($boeckm2 == $me['id']) $me['sopernik'] = $boeckm1;
}

// Финиш
if (empty($komanda1) || empty($komanda2)) {
    $winkom = 0;
    $koef = 0.33;
    if ($boi['krov'] == 2) $koef *= 3;
    if (in_array((int)$boi['krov'], [3, 4, 5], true)) $koef = 0.01;
    if ($prazdn == 1) $koef *= 2;

    $q = $db->query("SELECT SUM(`lvl`) AS `s` FROM `combat` WHERE `boi_id` = {$bid} AND `komanda` = 1;");
    $lvl1 = $q ? (int)$q->fetch_assoc()['s'] : 0;
    $q = $db->query("SELECT SUM(`lvl`) AS `s` FROM `combat` WHERE `boi_id` = {$bid} AND `komanda` = 2;");
    $lvl2 = $q ? (int)$q->fetch_assoc()['s'] : 0;
    if ($lvl1 < 1) $lvl1 = 1;
    if ($lvl2 < 1) $lvl2 = 1;

    if ($kom1sum > 0 && $kom2sum <= 0) {
        $winkom = 1;
        $koef = round($lvl2 / $lvl1 * $koef, 2);
        $db->query("UPDATE `combat`, `users` SET `users`.`win` = `users`.`win` + 1 WHERE (`combat`.`flag_bot` = 0 AND `combat`.`komanda` = 1 AND `combat`.`boi_id` = '{$bid}' AND `users`.`login` = `combat`.`login`);");
        $db->query("UPDATE `combat`, `users` SET `users`.`lost` = `users`.`lost` + 1, `users`.`doping` = 0, `users`.`doping_time` = 0, `users`.`rabota` = 0, `users`.`loc` = 1, `users`.`kvest_now` = 0, `users`.`kvest_step` = 0 WHERE (`combat`.`flag_bot` = 0 AND `combat`.`komanda` = 2 AND `combat`.`boi_id` = '{$bid}' AND `users`.`login` = `combat`.`login`);");
    }
    if ($kom1sum <= 0 && $kom2sum > 0) {
        $winkom = 2;
        $koef = round($lvl1 / $lvl2 * $koef, 2);
        $db->query("UPDATE `combat`, `users` SET `users`.`win` = `users`.`win` + 1 WHERE (`combat`.`flag_bot` = 0 AND `combat`.`komanda` = 2 AND `combat`.`boi_id` = '{$bid}' AND `users`.`login` = `combat`.`login`);");
        $db->query("UPDATE `combat`, `users` SET `users`.`lost` = `users`.`lost` + 1, `users`.`doping` = 0, `users`.`doping_time` = 0, `users`.`rabota` = 0, `users`.`loc` = 1, `users`.`kvest_now` = 0, `users`.`kvest_step` = 0 WHERE (`combat`.`flag_bot` = 0 AND `combat`.`komanda` = 1 AND `combat`.`boi_id` = '{$bid}' AND `users`.`login` = `combat`.`login`);");
    } elseif ($kom1sum <= 0 && $kom2sum <= 0) {
        $final_log = '<span style="color:' . $male . '">НИЧЬЯ</span><br/>' . $final_log;
    }

    if ($prazdn == 1) $final_log = '<span style="color:yellow">Праздничное увеличение опыт х2, монеты х2</span><br/>' . $final_log;

    $q = $db->query("SELECT * FROM `combat` WHERE `boi_id` = {$bid} AND `komanda` = 1 ORDER BY `uron_boi` DESC;");
    if ($q) {
        while ($kom1 = $q->fetch_assoc()) {
            $str = $kom1['login'] . ' (' . $kom1['hpnow'] . '/' . $kom1['hpmax'] . ') (урон: ' . $kom1['uron_boi'];
            if ($winkom == 1) {
                if (empty($kom1['flag_bot'])) {
                    $qq = $db->query("SELECT `id`, `vip`, `ref` FROM `users` WHERE `login` = '" . $db->real_escape_string($kom1['login']) . "' LIMIT 1;");
                    $slog = $qq ? $qq->fetch_assoc() : null;
                    $exp1 = (int)($koef * $kom1['uron_boi']);
                    if ($slog && $slog['vip'] > $curtime) $exp1 = (int)(2 * $exp1);
                    if ($slog && !empty($slog['ref'])) addexp($slog['ref'], (int)ceil($exp1 * 0.05));
                    if ($slog) addexp($slog['id'], $exp1);
                } else {
                    $exp1 = (int)($koef * $kom1['uron_boi']);
                }
                $str .= ', опыт: ' . $exp1;
            }
            $str .= ')<br/>';
            $final_log = $str . $final_log;
        }
    }
    $final_log = '<small>VS.</small><br/>' . $final_log . '<br/>';

    $q = $db->query("SELECT * FROM `combat` WHERE `boi_id` = {$bid} AND `komanda` = 2 ORDER BY `uron_boi` DESC;");
    if ($q) {
        while ($kom2 = $q->fetch_assoc()) {
            $str = $kom2['login'] . ' (' . $kom2['hpnow'] . '/' . $kom2['hpmax'] . ') (урон: ' . $kom2['uron_boi'];
            if ($winkom == 2) {
                if (empty($kom2['flag_bot'])) {
                    $qq = $db->query("SELECT `id`, `vip`, `ref` FROM `users` WHERE `login` = '" . $db->real_escape_string($kom2['login']) . "' LIMIT 1;");
                    $slog = $qq ? $qq->fetch_assoc() : null;
                    $exp2 = (int)($koef * $kom2['uron_boi']);
                    if ($slog && $slog['vip'] > $curtime) $exp2 = (int)(2 * $exp2);
                    if ($slog && !empty($slog['ref'])) addexp($slog['ref'], (int)ceil($exp2 * 0.05));
                    if ($slog) addexp($slog['id'], $exp2);
                } else {
                    $exp2 = (int)($koef * $kom2['uron_boi']);
                }
                $str .= ', опыт: ' . $exp2;
            }
            $str .= ')<br/>';
            $final_log = $str . $final_log;
        }
    }
    if (!empty($winkom)) $final_log = 'Коэффициент опыта: ' . $koef . '<br/>' . $final_log;

    $db->query("INSERT INTO `battlelog` VALUES (0, '{$bid}', '{$t}', '" . $db->real_escape_string($final_log) . "');");
    $db->query("DELETE FROM `combat` WHERE `boi_id` = 0 OR `boi_id` = {$bid};");
    $db->query("UPDATE `battle` SET `flag_boi` = 0 WHERE `id` = {$bid} LIMIT 1;");
    $db->query("UPDATE `users` SET `status` = 0, `boi_id` = 0, `lastdate` = '{$t}', `hptime` = '{$t}', `manatime` = '{$t}' WHERE `boi_id` = {$bid};");

    require_once __DIR__ . '/inc/hpstring.php';
    knopka('loc.php', 'Бой завершен', 1);
    unset($_SESSION['pkstr']);
    $logboi = '';
    $q = $db->query("SELECT * FROM `battlelog` WHERE `boi_id` = {$bid} ORDER BY `id` DESC LIMIT 3;");
    echo '<div class="board" style="text-align:left">';
    if ($q) {
        while ($stlog = $q->fetch_assoc()) echo $stlog['log'];
    }
    echo '</div>';
    fin();
}

require_once __DIR__ . '/inc/hpstring.php';

if (($kom1sum > 1 && $me['komanda'] == 2) || ($kom2sum > 1 && $me['komanda'] == 1) || empty($me['sopernik']) || $me['hpnow'] < 0) {
    echo '<div class="board" style="text-align:left;">' . $komanda1 . 'VS<br/>' . $komanda2 . '</div>';
}

$q = $db->query("SELECT `invent`.`id` FROM `invent`, `item` WHERE `invent`.`login` = '" . $db->real_escape_string($me['login']) . "' AND `invent`.`flag_equip` = 1 AND `item`.`equip` = 'sumka' AND `invent`.`ido` = `item`.`id`;");
$sum = ($q && $q->num_rows > 0) ? 1 : 0;

if (!empty($me['sopernik'])) {
    if ($me['komanda'] == 1) { $col1 = $notice; $col2 = $male; }
    else { $col1 = $male; $col2 = $notice; }

    $q = $db->query("SELECT * FROM `combat` WHERE `id` = " . (int)$me['sopernik'] . " LIMIT 1;");
    $uz_view = $q ? $q->fetch_assoc() : null;

    if ($uz_view) {
        echo '<div class="board" style="text-align:left;">';
        echo '<span style="color:' . $col1 . '"><b>' . $me['login'] . '</b></span> [' . $me['lvl'] . '] (' . $me['hpnow'] . '/' . $me['hpmax'] . ') урон: ' . $me['uron_boi'] . '<br/><small>VS.</small><br/>';
        if ($uz_view['flag_bot'] == 1) {
            echo '<span style="color:' . $col2 . '"><b>' . $uz_view['login'] . '</b></span> [' . $uz_view['lvl'] . '] (' . $uz_view['hpnow'] . '/' . $uz_view['hpmax'] . ') урон: ' . $uz_view['uron_boi'];
        } else {
            $tajm = $curtime - (int)$uz_view['time_udar'];
            $tajm = $hodtime - $tajm;
            if ($tajm < 0) $tajm = 0;
            echo '<span style="color:' . $col2 . '"><b><a href="infa.php?mod=uzinfa&lgn=' . $uz_view['login'] . '">' . $uz_view['login'] . '</a></b></span> [' . $uz_view['lvl'] . '] (' . $uz_view['hpnow'] . '/' . $uz_view['hpmax'] . ') урон: ' . $uz_view['uron_boi'] . ', тайм: ' . $tajm;
        }
        echo '</div>';

        if ((empty($me['kuda_udar']) || empty($me['kuda_blok'])) && $me['hpnow'] > 0) {
            echo '<div class="board" style="text-align:left;">';
            if ($uz_view['flag_bot'] == 1) {
                echo '<a class="navig" href="battle.php?ud=' . mt_rand(1, 3) . '&bl=' . mt_rand(1, 3) . '&r=' . mt_rand(1111, 9999) . '"><b>Ударить</b></a>';
            } else {
                echo '<form action="battle.php?r=' . mt_rand(1111, 9999) . '" method="POST">';
                $rndblok = mt_rand(1, 3);
                $catbl = [1 => '', 2 => '', 3 => ''];
                $catbl[$rndblok] = 'selected';
                echo '<select name="bl">';
                echo '<option value="2" ' . $catbl[2] . '>блок груди</option>';
                echo '<option value="1" ' . $catbl[1] . '>блок головы</option>';
                echo '<option value="3" ' . $catbl[3] . '>блок ног</option>';
                echo '</select>';
                $rndud = mt_rand(1, 3);
                $catud = [1 => '', 2 => '', 3 => ''];
                $catud[$rndud] = 'selected';
                echo '<select name="ud">';
                echo '<option value="2" ' . $catud[2] . '>удар в грудь</option>';
                echo '<option value="1" ' . $catud[1] . '>удар в голову</option>';
                echo '<option value="3" ' . $catud[3] . '>удар по ногам</option>';
                echo '</select>';
                echo '<input type="submit" value="Удар"></form>';
            }
            echo '</div>';
        }
    }
}

$tajm = $curtime - (int)$me['time_udar'];
$tajm = $hodtime - $tajm;
if ($tajm < 0) $tajm = 0;

echo '<div class="board3" style="text-align:left;">';
echo '<a href="battle.php?r=' . mt_rand(1, 999) . '">Обновить (<b>' . $tajm . '</b>)</a> ';
if ($ost == 0) echo '- <a href="battle.php?mod=sbrospar">Сброс</a> ';
if ($sum > 0 && $me['hpnow'] > 0) echo '- <a href="battle.php?mod=sumka">Сумка</a> ';
if (!empty($pkstr_display)) {
    echo ' <span style="margin-left:8px;">' . $pkstr_display . '</span>';
}
echo '</div>';
echo '<div class="board" style="text-align:left;">' . $logboi . '</div>';
echo '<div class="menu">';
echo '<a href="battle.php?mod=long">Длинный лог боя</a> ';
echo '- <a href="battle.php?mod=whowhere">Кто в бою</a> ';
echo '</div>';
fin();
