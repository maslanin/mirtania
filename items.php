<?php
/**
 * Выдача вещей всем игрокам (утилита).
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/class/DBC.php';
require_once __DIR__ . '/inc/func.php';
require_once __DIR__ . '/class/items.php';

$q = $db->query("SELECT `login` FROM `users`;");
$count = 0;
if ($q) {
    while ($a = $q->fetch_assoc()) {
        $items->add_item($a['login'], 636, 1);
        $count++;
    }
}
echo 'Игрокам передано ' . $count . ' вещей';
