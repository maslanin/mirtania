<?php
/**
 * Перемещение по локациям.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/inc/hpstring.php';

$_SESSION['lasthod'] = '';

if (empty($f['loc'])) {
    $f['loc'] = 1;
    $db->query("UPDATE `users` SET `loc` = 1 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
}
if ($f['status'] == 1) {
    knopka('battle.php', 'Вы в бою!', 1);
    fin();
}
if ($f['status'] == 2) {
    knopka('arena.php', 'У вас заявка на арене!', 1);
    fin();
}

$mod  = isset($_REQUEST['mod'])  ? $_REQUEST['mod']  : '';
$go   = isset($_REQUEST['go'])   ? (int)$_REQUEST['go']   : 0;
$num  = isset($_REQUEST['num'])  ? (int)$_REQUEST['num']  : 1;
$hint = isset($_REQUEST['hint']) ? (string)$_REQUEST['hint'] : '';

if (!isset($_SESSION['hint'])) {
    $_SESSION['hint'] = $hint;
}

if ($f['rabota'] > $t) msg2('Вы работаете еще ' . ceil(($f['rabota'] - $t) / 60) . ' мин.');
if ($f['rabota'] > 0 && $f['rabota'] < $t) knopka('rabota.php', 'Вы отработали свое время', 1);

$q = $db->query("SELECT * FROM `loc` WHERE `id` = " . (int)$f['loc'] . " LIMIT 1;");
$loc = $q ? $q->fetch_assoc() : null;
if (!$loc) {
    msg2('Локация не найдена', 1);
}
$x = (int)$loc['X'];
$y = (int)$loc['Y'];

if (!empty($_REQUEST['sever']) && !empty($loc['N']) && $hint === $_SESSION['hint']) { $y++; $_SESSION['lasthod'] = 'sever'; }
if (!empty($_REQUEST['jug'])   && !empty($loc['S']) && $hint === $_SESSION['hint']) { $y--; $_SESSION['lasthod'] = 'jug'; }
if (!empty($_REQUEST['zapad']) && !empty($loc['W']) && $hint === $_SESSION['hint']) { $x--; $_SESSION['lasthod'] = 'zapad'; }
if (!empty($_REQUEST['vostok'])&& !empty($loc['E']) && $hint === $_SESSION['hint']) { $x++; $_SESSION['lasthod'] = 'vostok'; }

if ($y != $loc['Y'] || $x != $loc['X']) {
    $q = $db->query("SELECT * FROM `loc` WHERE `X` = {$x} AND `Y` = {$y} AND `map_id` = " . (int)$loc['map_id'] . " LIMIT 1;");
    $loc = $q ? $q->fetch_assoc() : null;
    if ($loc) {
        $f['loc'] = (int)$loc['id'];
        $db->query("UPDATE `users` SET `loc` = {$f['loc']}, `rabota` = 0, `kvest_step` = 0 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        $f['kvest_step'] = 0;
    }
}

if (!empty($f['kvest_step'])) knopka('kvest.php?qv_id=' . $f['kvest_now'], 'Продолжить задание', 1);
if (!empty($stats_free)) knopka('anketa.php?mod=stats', 'Вам необходимо <span style="color:red">распределить статы</span>');

// Портал
if ($mod == 'get' || !empty($_REQUEST['get'])) {
    msg('Выберите, куда открыть портал:');
    if ($f['loc'] != $f['lastportal'] && !empty($f['lastportal'])) knopka('loc.php?mod=portal&num=4', 'Последний портал', 1);
    if ($f['loc'] != 1)  knopka('loc.php?mod=portal&num=1', 'Старый лагерь', 1);
    if ($f['loc'] != 37) knopka('loc.php?mod=portal&num=2', 'Новый лагерь', 1);
    if ($f['loc'] != 91) knopka('loc.php?mod=portal&num=3', 'Болотный лагерь', 1);
    if (!empty($f['klan'])) {
        $q = $db->query("SELECT `loc`, `point` FROM `klans` WHERE `name` = '" . $db->real_escape_string($f['klan']) . "' LIMIT 1;");
        if ($q && $q->num_rows == 1) knopka('loc.php?mod=portal&num=5', 'Клановый замок', 1);
    }
    fin();
}

if ($mod == 'portal') {
    $lastportal = (int)$f['loc'];
    if ($f['mananow'] < 1) msg2('Недостаточно маны!', 1);

    $target = 0;
    $target_name = '';
    if ($num == 1 && $f['loc'] != 1)  { $target = 1;  $target_name = 'Старый лагерь'; }
    elseif ($num == 2 && $f['loc'] != 37) { $target = 37; $target_name = 'Новый лагерь'; }
    elseif ($num == 3 && $f['loc'] != 91) { $target = 91; $target_name = 'Болотный лагерь'; }
    elseif ($num == 4 && $f['loc'] != $f['lastportal'] && !empty($f['lastportal'])) { $target = (int)$f['lastportal']; $target_name = 'прошлое место'; }
    elseif ($num == 5) {
        if (empty($f['klan'])) msg2('Вы не в клане', 1);
        $q = $db->query("SELECT `loc`, `point` FROM `klans` WHERE `name` = '" . $db->real_escape_string($f['klan']) . "' LIMIT 1;");
        if ($q && $q->num_rows == 1) {
            $a = $q->fetch_assoc();
            if (empty($a['loc']) && empty($a['point'])) msg2('У вашего клана нет замка!', 1);
            $target = !empty($a['loc']) ? (int)$a['loc'] : (int)$a['point'];
            $target_name = 'замок вашего клана';
        }
    }

    if ($target > 0) {
        $q = $db->query("SELECT `id` FROM `loc` WHERE `id` = {$target} LIMIT 1;");
        if ($q === false || $q->num_rows == 0) msg2('Перемещение в это место невозможно!', 1);
        $mananow = (int)$f['mananow'] - 1;
        $db->query("UPDATE `users` SET `loc` = {$target}, `mananow` = {$mananow}, `manatime` = '{$t}', `lastportal` = {$lastportal}, `rabota` = 0, `kvest_step` = 0 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2('Вы переместились в ' . $target_name . '. Это стоило вам 1 маны.');
        // Обновим данные
        $f['loc'] = $target;
        $f['mananow'] = $mananow;
        $q = $db->query("SELECT * FROM `loc` WHERE `id` = {$target} LIMIT 1;");
        $loc = $q ? $q->fetch_assoc() : null;
        $x = (int)$loc['X'];
        $y = (int)$loc['Y'];
    }
}

echo '<div class="board">';

$rd = mt_rand(999, 99999);
$hint2 = md5((string)$rd);
if ($f['grafika'] == 1 || $f['grafika'] == 3) {
    echo '<img src="locimg.php?r=' . mt_rand(1000000, 99999999) . '" width="120" height="120" style="border: 1px outset black;"/><br/>';
}

if (!empty($loc['info'])) echo '<small>' . $loc['info'] . '</small><br/><br/>';

echo '<form action="loc.php" method="post">';
echo '<input type="hidden" name="hint" value="' . $hint2 . '">';
$_SESSION['hint'] = $hint2;

if ($f['strelki'] == 1) {
    if (!empty($loc['N'])) echo '<input type="submit" value="Север" name="sever" style="width:50%"><br/>';
    if (!empty($loc['S'])) echo '<input type="submit" value="Юг" name="jug" style="width:50%"><br/>';
    if (!empty($loc['W'])) echo '<input type="submit" value="Запад" name="zapad" style="width:50%"><br/>';
    if (!empty($loc['E'])) echo '<input type="submit" value="Восток" name="vostok" style="width:50%"><br/>';
} elseif ($f['strelki'] == 2) {
    echo '<input type="submit" value="Север" name="sever"' . (empty($loc['N']) ? ' disabled="disabled" style="color:gray"' : '') . '/><br/>';
    echo '<input type="submit" value="Запад" name="zapad"' . (empty($loc['W']) ? ' disabled="disabled" style="color:gray"' : '') . '/>';
    echo '<input type="submit" value="Восток" name="vostok"' . (empty($loc['E']) ? ' disabled="disabled" style="color:gray"' : '') . '/><br/>';
    echo '<input type="submit" value=" Юг " name="jug"' . (empty($loc['S']) ? ' disabled="disabled" style="color:gray"' : '') . '/>';
} else {
    echo '<input type="submit" value="&#8593;" name="sever"' . (empty($loc['N']) ? ' disabled="disabled" style="color:gray"' : '') . '/><br/>';
    echo '<input type="submit" value="&#8592;" name="zapad"' . (empty($loc['W']) ? ' disabled="disabled" style="color:gray"' : '') . '/>';
    echo '<input type="submit" style="background:#c3a86b;" value="Т" name="get"/>';
    echo '<input type="submit" value="&#8594;" name="vostok"' . (empty($loc['E']) ? ' disabled="disabled" style="color:gray"' : '') . '/><br/>';
    echo '<input type="submit" value="&#8595;" name="jug"' . (empty($loc['S']) ? ' disabled="disabled" style="color:gray"' : '') . '/>';
}
echo '</form><br/>';

require_once __DIR__ . '/inc/locs.php';

echo '</div>';

if ($f['strelki'] == 1 || $f['strelki'] == 2) knopka('loc.php?mod=get', 'Портал', 1);

$timer1 = $t - 900;
$q = $db->query("SELECT `login`, `lvl`, `sex`, `status`, `klan` FROM `users` WHERE `loc` = " . (int)$f['loc'] . " AND `login` <> '" . $db->real_escape_string($f['login']) . "' AND `lastdate` > '{$timer1}' ORDER BY `lvl`;");
if (!$q || $q->num_rows == 0) {
    msg2('Рядом никого нет');
} else {
    msg2('Рядом с вами:');
    while ($array_onl = $q->fetch_assoc()) {
        $color_login = ($array_onl['sex'] == 1) ? $male : $female;
        $str = '<span style="color:' . $color_login . '">' . $array_onl['login'] . ' [' . $array_onl['lvl'] . ']</span>';
        if (!empty($array_onl['klan'])) $str .= ' (' . $array_onl['klan'] . ')';
        if ($array_onl['status'] == 1) $str .= ' [Б]';
        knopka('infa.php?mod=uzinfa&lgn=' . $array_onl['login'], $str);
    }
}

if ($f['admin'] >= 3) msg('loc: ' . $f['loc']);

fin();
