<?php
/**
 * Генерация мини-карты локации (PNG).
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/class/DBC.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = isset($_COOKIE['id']) ? (int)$_COOKIE['id'] : 0;
if ($id <= 0) {
    header('HTTP/1.1 403 Forbidden');
    exit('403 Forbidden');
}

$q = $db->query("SELECT `loc`, `sex` FROM `users` WHERE `id` = {$id} LIMIT 1;");
$user = $q ? $q->fetch_assoc() : null;
if (!$user) {
    header('HTTP/1.1 403 Forbidden');
    exit('403 Forbidden');
}

$q = $db->query("SELECT `map_id`, `X`, `Y` FROM `loc` WHERE `id` = " . (int)$user['loc'] . " LIMIT 1;");
$loc = $q ? $q->fetch_assoc() : null;
if (!$loc) {
    header('HTTP/1.1 404 Not Found');
    exit('404 Not Found');
}

$q = $db->query("SELECT `X`, `Y` FROM `map` WHERE `id` = " . (int)$loc['map_id'] . " LIMIT 1;");
$map = $q ? $q->fetch_assoc() : null;
if (!$map) {
    header('HTTP/1.1 404 Not Found');
    exit('404 Not Found');
}

$pox = (int)$loc['X'];
$poy = (int)$loc['Y'];
if ($poy > $map['Y'] - 2) $poy = $map['Y'] - 2;
if ($pox > $map['X'] - 2) $pox = $map['X'] - 2;
if ($poy < 3) $poy = 3;
if ($pox < 3) $pox = 3;

$pox1 = $pox - 2;
$pox2 = $pox + 2;
$poy1 = $poy - 2;
$poy2 = $poy + 2;

header('Content-Type: image/png');
$im = imagecreatetruecolor(120, 120);
$color = imagecolorallocate($im, 0, 0, 0);
imagecolortransparent($im, $color);
imagefilledrectangle($im, 0, 0, 119, 119, $color);

$q = $db->query("SELECT `id`, `N`, `E`, `W`, `S`, `X`, `Y` FROM `loc` WHERE `map_id` = " . (int)$loc['map_id'] . " AND `X` >= {$pox1} AND `X` <= {$pox2} AND `Y` >= {$poy1} AND `Y` <= {$poy2};");
if ($q) {
    while ($c = $q->fetch_assoc()) {
        $name = '.png';
        if (!empty($c['W'])) $name = 'W' . $name;
        if (!empty($c['E'])) $name = 'E' . $name;
        if (!empty($c['S'])) $name = 'S' . $name;
        if (!empty($c['N'])) $name = 'N' . $name;
        $path = __DIR__ . '/pic/' . $name;
        if (!file_exists($path)) continue;
        $img = @imagecreatefrompng($path);
        if (!$img) continue;
        $rx = (int)$c['X'] - $pox1;
        $ry = (int)$c['Y'] - $poy1;
        imagecopy($im, $img, 24 * $rx, (-1) * ((24 * $ry) - 96), 0, 0, 24, 24);
        imagedestroy($img);
    }
}

$white = imagecolorallocate($im, 255, 255, 255);
$polx = (int)$loc['X'] - $pox;
$poly = (int)$loc['Y'] - $poy;
$razmer = 24;
$path = __DIR__ . '/pic/p.png';
$p = file_exists($path) ? @imagecreatefrompng($path) : false;

$x1 = (60 - (int)($razmer * 0.5)) + $polx * 24;
$y1 = (60 - (int)($razmer * 0.5)) - $poly * 24;

$positX = 6;
if (!empty($_SESSION['lasthod'])) {
    if ($_SESSION['lasthod'] == 'sever')  $positX = 0;
    if ($_SESSION['lasthod'] == 'jug')    $positX = 6;
    if ($_SESSION['lasthod'] == 'vostok') $positX = 3;
    if ($_SESSION['lasthod'] == 'zapad')  $positX = 9;
}
$positY = ($user['sex'] == 1) ? 0 : 2;

if ($p) {
    imagecopy($im, $p, $x1, $y1, $positX * 24, $positY * 24, 24, 24);
    imagedestroy($p);
}

imagepng($im);
imagedestroy($im);
exit;
