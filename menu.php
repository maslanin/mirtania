<?php
/**
 * Меню навигации.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';

if (!empty($_SESSION['auth'])) {
    require_once __DIR__ . '/inc/hpstring.php';
}

knopka('news.php', '<b>' . DateNews() . '</b>', 1);

// Форум
$q = $db->query("SELECT COUNT(*) AS `c` FROM `forum_topic`;");
$a = $q ? (int)$q->fetch_assoc()['c'] : 0;
$q = $db->query("SELECT COUNT(*) AS `c` FROM `forum_comm`;");
$b = $q ? (int)$q->fetch_assoc()['c'] : 0;
knopka('forum.php', 'Форум <b>(' . $a . '/' . $b . ')</b>', 1);

// Чат
$timer = $t - 300;
$q = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `chatdate` > '{$timer}';");
$a = $q ? (int)$q->fetch_assoc()['c'] : 0;

// Онлайн
$timer1 = $t - 900;
$timer2 = $t - 7200;
$q = $db->query("SELECT COUNT(`id`) AS `c` FROM `users` WHERE (`lastdate` > '{$timer1}' OR (`status` = 1 AND `lastdate` > '{$timer2}'));");
$b = $q ? (int)$q->fetch_assoc()['c'] : 0;

knopka('chat.php', 'Чат <b>(' . $a . ')</b>', 1);

$count_pm = $count_pm ?? 0;
knopka('pm.php', 'Почта <b>(' . $count_pm . ')</b>', 1);
knopka('infa.php?mod=onl', 'Онлайн <b>(' . $b . ')</b>', 1);

if (!empty($f['klan']) && $f['klan_status'] > 0) {
    knopka('klan.php', 'Управление кланом', 1);
}

knopka('infa.php?mod=klans', 'Кланы', 1);
knopka('infa.php?mod=rate', 'Зал славы', 1);
knopka('infa.php', 'Поиск игроков', 1);
knopka('lib.php', 'Библиотека', 1);
knopka('inv.php', 'Рюкзак', 1);
knopka('inv.php?mod=equip', 'Экипировка', 1);
knopka('infa.php?mod=boi', 'Текущие бои', 1);

if (1 <= $f['admin']) knopka('adm.php', 'Админка', 1);
if (3 <= $f['admin']) knopka('adm.php?mod=res', 'Восстановление', 1);

fin();
