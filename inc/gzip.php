<?php
/**
 * Сжатие вывода.
 * PHP 8.2-совместимая версия.
 *
 * ВНИМАНИЕ: на современных веб-серверах (nginx/Apache с mod_deflate)
 * этот файл не нужен — сервер сжимает сам. Оставлен для совместимости.
 */

function compress_output_gzip($output)
{
    return gzencode($output, 2);
}

function compress_output_deflate($output)
{
    return gzdeflate($output, 2);
}

$PREFER_DEFLATE    = false;
$FORCE_COMPRESSION = false;

$AE = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? ($_SERVER['HTTP_TE'] ?? '');

$support_gzip    = (strpos($AE, 'gzip')    !== false) || $FORCE_COMPRESSION;
$support_deflate = (strpos($AE, 'deflate') !== false) || $FORCE_COMPRESSION;

do {
    if ($support_gzip) {
        if (!$support_deflate) {
            break;
        } else {
            $support_deflate = $PREFER_DEFLATE;
        }
    }
    if ($support_deflate) {
        header('Content-Encoding: deflate');
        ob_start('compress_output_deflate');
    }
} while (0);

if ($support_gzip) {
    header('Content-Encoding: gzip');
    ob_start('compress_output_gzip');
} else {
    ob_start();
}
