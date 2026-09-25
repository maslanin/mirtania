<?php
/**
 * Капча.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: imagefilledrectangle($im, 0, 0, 20, 45, $black) — координаты перепутаны.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$im = imagecreatetruecolor(45, 20);
$black = imagecolorallocate($im, 0, 0, 0);
// ИСПРАВЛЕНО: было (0, 0, 20, 45) — x2=20, y2=45 (высота 20)
imagefilledrectangle($im, 0, 0, 45, 20, $black);
$white = imagecolorallocate($im, 255, 255, 255);

$c1 = mt_rand(1, 9);
$c2 = mt_rand(1, 9);
$c3 = mt_rand(1, 9);
$code = $c1 . $c2 . $c3;
$_SESSION['bez'] = $code;

$codeimg = $c1 . ' ' . $c2 . ' ' . $c3;
imagestring($im, 4, 2, 2, $codeimg, $white);
imagepng($im);
imagedestroy($im);
exit;
