<?php
/**
 * Базовые статы ботов по уровням игрока и создание бота.
 * PHP 8.2-совместимая версия.
 */

// Таблица базовых статов: [zdor, sila, lovka, inta]
$botBase = [
    1  => [3,   3,   15,  15],
    2  => [9,   11,  30,  30],
    3  => [18,  22,  50,  50],
    4  => [30,  37,  65,  65],
    5  => [45,  56,  85,  85],
    6  => [60,  75,  100, 100],
    7  => [84,  105, 125, 125],
    8  => [108, 135, 145, 145],
    9  => [135, 168, 165, 165],
    10 => [165, 206, 190, 190],
    11 => [198, 247, 210, 210],
    12 => [234, 292, 235, 235],
    13 => [273, 341, 255, 255],
    14 => [315, 393, 280, 280],
    15 => [360, 450, 305, 305],
    16 => [408, 510, 330, 330],
    17 => [459, 573, 355, 355],
    18 => [513, 641, 380, 380],
    19 => [570, 712, 410, 410],
    20 => [630, 787, 435, 435],
    21 => [693, 866, 465, 465],
    22 => [759, 948, 495, 495],
    23 => [828, 1035, 520, 520],
    24 => [900, 1125, 555, 555],
];

$lvl = (int)($f['lvl'] ?? 1);
$base = $botBase[$lvl] ?? [975, 1218, 585, 585];
$basezdor   = $base[0];
$basesila   = $base[1];
$baselovka  = $base[2];
$baseinta   = $base[3];

function getHP($s = 1)
{
    global $basezdor;
    return mt_rand((int)($basezdor * $s * 0.7), (int)($basezdor * $s * 0.95)) * 10;
}

function getSila($s = 1)
{
    global $basesila;
    return mt_rand((int)($basesila * $s * 0.7), (int)($basesila * $s * 0.95));
}

function getLovka($s = 1)
{
    global $baselovka;
    return mt_rand((int)($baselovka * $s * 0.7), (int)($baselovka * $s * 0.95));
}

function getInta($s = 1)
{
    global $baseinta;
    return mt_rand((int)($baseinta * $s * 0.7), (int)($baseinta * $s * 0.95));
}

function addBot($name, $lvl)
{
    $db = DBC::instance();
    global $boi_id;
    if (!isset($boi_id)) $boi_id = 0;
    $boi_id = (int)$boi_id;
    $name_esc = $db->real_escape_string($name);

    require __DIR__ . '/bot_base.php';

    $sila  = (int)$sila;
    $inta  = (int)$inta;
    $lovka = (int)$lovka;
    $hp    = (int)$hp;
    $lvl   = (int)$lvl;
    $now   = time();

    $db->query("INSERT INTO `combat` VALUES (0, '{$name_esc}', {$sila}, {$inta}, {$lovka}, 1, 0, 0, 0, {$lvl}, {$hp}, {$hp}, 0, 0, {$boi_id}, 0, 1, 0, '{$now}', '{$now}', 0);");
    return 0;
}
