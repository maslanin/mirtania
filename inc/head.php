<?php
/**
 * Шапка страницы.
 * PHP 8.2-совместимая версия.
 */

// Текущий файл (без пути)
$self = basename($_SERVER['PHP_SELF'] ?? '');

// Начало шапки — flex-контейнер
echo '<div class="head" style="display: flex; align-items: center; justify-content: space-between;">';

// === ЛЕВАЯ КОЛОНКА ===
echo '<div style="flex: 1; text-align: left;">';
if (!empty($_SESSION['auth'])) {
    require_once __DIR__ . '/check.php';

    // Профиль (не показываем на anketa.php)
    if ($self !== 'anketa.php') {
        echo '<a href="anketa.php" style="text-decoration: none; color: #fff;">Профиль</a>';
    }

    // Уведомление о непрочитанных письмах
    if (!empty($count_pm) && $self !== 'pm.php') {
        echo ' <a href="pm.php" style="text-decoration: none; color: #ff9900;">Почта (' . $count_pm . ')</a>';
    }

    // Индикатор боя
    if ($f['status'] == 1 && $self !== 'battle.php') {
        echo ' <a href="battle.php" style="text-decoration: none; color: #ff3333;">Бой!</a>';
    }
}
echo '</div>';

// === ЦЕНТР — ЧАСЫ ===
echo '<div style="flex: 0 0 auto; text-align: center; font-weight: bold; white-space: nowrap;">';
echo date('H:i:s');
echo '</div>';

// === ПРАВАЯ КОЛОНКА ===
echo '<div style="flex: 1; text-align: right;">';
if (!empty($_SESSION['auth'])) {
    $links = [];

    // Обновить (не из кэша)
    $links[] = '<a href="' . htmlspecialchars($self, ENT_QUOTES, 'UTF-8') . '?r=' . mt_rand(1111, 9999) . '" style="text-decoration: none; color: #fff;">Обновить</a>';

    // Меню (не показываем на menu.php)
    if ($self !== 'menu.php') {
        $links[] = '<a href="menu.php" style="text-decoration: none; color: #fff;">Меню</a>';
    }

    // В игру (не показываем на loc.php)
    if ($self !== 'loc.php') {
        $links[] = '<a href="loc.php" style="text-decoration: none; color: #fff;">В игру</a>';
    }

    // Разделитель — точка с отступами
    $sep = ' <span style="color: #888; margin: 0 4px;">·</span> ';
    echo implode($sep, $links);
} else {
    if ($self !== 'index.php') {
        echo '<a href="index.php" style="text-decoration: none; color: #fff;">[Главная]</a>';
    }
}
echo '</div>';

echo '</div>';

if (!isset($f['login'])) $f['login'] = '';
if (!isset($f['admin'])) $f['admin'] = 0;

if (!empty($settings['mess']) && !empty($f['login']) && ($admin ?? '') !== $f['login']) {
    msg2($settings['mess'], 1);
}
