<?php
/**
 * Ежедневный бонус.
 * PHP 8.2-совместимая версия.
 */

$day = 60 * 60 * 20; // раз в 20 часов

$bonus_time = (int)($f['bonus_time'] ?? 0);
$bonus_day  = (int)($f['bonus_day']  ?? 0);
$autoreg    = (int)($f['autoreg']    ?? 0);

if ($bonus_time + $day < time() && $autoreg == 0) {
    if (($bonus_day > 0 && $bonus_time + $day * 2 < time()) || $bonus_day >= 10) {
        $bonus_day = 0;
    }
    $bonus_day++;
    $rand = 5 * (int)$f['lvl'] * $bonus_day;
    $nagr = 0;
    if ($bonus_day == 5)  $nagr = mt_rand(1, 3);
    if ($bonus_day == 10) $nagr = mt_rand(3, 5);

    $str = '<img src="pic/bonus.png"> Вам начислен ежедневный бонус: ' . $rand . ' монет';
    if (!empty($nagr)) $str .= ' и ' . $nagr . ' руды';
    $str .= ' за ' . $bonus_day . '-й день</div>';
    msg2($str);

    $money = (int)$f['money'] + $rand;
    $ruda  = (int)$f['ruda']  + $nagr;
    $now   = time();
    $db->query("UPDATE `users` SET `money` = {$money}, `ruda` = {$ruda}, `bonus_time` = {$now}, `bonus_day` = {$bonus_day} WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    $f['money']      = $money;
    $f['ruda']       = $ruda;
    $f['bonus_time'] = $now;
    $f['bonus_day']  = $bonus_day;
}
