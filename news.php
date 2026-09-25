<?php
/**
 * Новости.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/head.php';

if (!empty($_SESSION['auth'])) {
    require_once __DIR__ . '/inc/hpstring.php';
}

$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;

$q = $db->query("SELECT COUNT(*) AS `c` FROM `news`;");
$numb = $q ? (int)$q->fetch_assoc()['c'] : 0;

if (!empty($_SESSION['auth']) && isset($f['id'])) {
    $db->query("UPDATE `users` SET `newsdate` = 0 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
}

if ($numb <= 0) msg2('Новостей нет', 1);

$col = 5;
if ($start > (int)($numb / $col)) $start = (int)($numb / $col);
if ($start < 0) $start = 0;
$limit = $start * $col;
$count = $limit;

$q = $db->query("SELECT * FROM `news` ORDER BY `id` DESC LIMIT {$limit}, {$col};");
if ($q) {
    while ($news = $q->fetch_assoc()) {
        echo '<div class="board">Тема: <b><span style="color:green">' . $news['title'] . '</span></b><br/>';
        echo 'Добавил: <b>' . $news['login'] . '</b> (' . date('d.m.Y H:i', (int)$news['datenews']) . ')</div></div>';
        echo '<div class="board2" style="text-align:left">';
        echo nl2br(link_it($news['text']));
        echo '</div>';
        $count++;
    }
}

if ($numb > $col) {
    echo '<div class="board">';
    if ($start > 0) echo '<a href="news.php?start=' . ($start - 1) . '" class="navig">Назад</a>';
    else echo '<a href="#" class="navig"> Назад</a>';
    echo ' | ';
    if ($limit + $col < $numb) echo '<a href="news.php?start=' . ($start + 1) . '" class="navig">Вперед</a>';
    else echo ' <a href="#" class="navig"> Вперед</a>';
    echo '</div>';
}

fin();
