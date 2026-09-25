<?php
/**
 * Обучение новичка.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/inc/hpstring.php';

if ($f['autoreg'] != 2) {
    header('Location: loc.php');
    fin();
}

$go = isset($_REQUEST['go']) ? (int)$_REQUEST['go'] : 0;

if (empty($f['kvest_step'])) {
    if (empty($go)) {
        msg('Добро пожаловать в волшебный мир удивительных приключений, Путник! Вы можете пройти обучение, или отказаться от него.');
        knopka('newbie.php?go=1', 'Пройти обучение', 1);
        knopka('newbie.php?go=2', 'Отказаться от обучения', 1);
        fin();
    } elseif ($go == 1) {
        $db->query("UPDATE `users` SET `kvest_step` = 1 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        header('Location: newbie.php');
        fin();
    } else {
        $db->query("UPDATE `users` SET `autoreg` = 1 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg('Вы отказались от обучения', 1);
    }
}
