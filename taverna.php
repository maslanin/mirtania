<?php
/**
 * Таверна: покупка эликсиров.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';

if ($f['status'] == 1) {
    knopka('battle.php', 'Вы в бою!', 1);
    fin();
}
if ($f['hpnow'] <= 0) {
    knopka('loc.php', 'Восстановите здоровье', 1);
    fin();
}
if ($f['loc'] != 38) {
    knopka('loc.php', 'Ошибка локации', 1);
    fin();
}

$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';
$iid = isset($_REQUEST['iid']) ? (int)$_REQUEST['iid'] : 1;

// Товары: id => [item_id, цена, название]
$goods = [
    1  => [620, 100, 'Эликсир ловкости (уворот +100)'],
    2  => [621, 100, 'Эликсир реакции (крит +100)'],
    3  => [622, 100, 'Эликсир жизненной силы (макс. ХП +100)'],
    4  => [623, 100, 'Эликсир магической силы (макс. МП +100)'],
    5  => [624, 100, 'Эликсир вышибалы (урон +50)'],
    6  => [153, 10,  'Эссенция исцеления (HP +50)'],
    7  => [154, 30,  'Вытяжка исцеления (HP +100)'],
    8  => [155, 100, 'Целебный элексир (HP +150)'],
    9  => [156, 200, 'Напиток лечения (HP +250)'],
    10 => [191, 10,  'Эссенция мудрости (MP +50)'],
    11 => [192, 30,  'Вытяжка мудрости (MP +100)'],
    12 => [193, 100, 'Элексир мудрости (MP +150)'],
    13 => [194, 200, 'Напиток мудрости (MP +250)'],
    14 => [121, 10,  'Свиток нападения'],
    15 => [122, 200, 'Свиток развоплощения'],
];

if (!empty($_SESSION['auth'])) require_once __DIR__ . '/inc/hpstring.php';

if (empty($mod)) {
    echo '<div class="board">';
    echo 'Монеты: ' . $f['money'] . '<hr/>';
    echo 'Вы зашли в здание таверны. Здесь можно приобрести различные элексиры, влияющие на ваше состояние.<hr/></div>';
    foreach ($goods as $id => $g) {
        knopka('taverna.php?mod=kup&iid=' . $id, $g[2] . ' (' . $g[1] . ' монет)', 1);
    }
    fin();
}

if ($mod == 'kup') {
    if (!isset($goods[$iid])) {
        header('Location: taverna.php');
        exit;
    }
    [$item_id, $cena] = $goods[$iid];
    if ($f['money'] < $cena) msg('Вам нечем заплатить.', 1);
    $db->query("UPDATE `users` SET `money` = `money` - {$cena} WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
    $items->add_item($f['login'], $item_id, 1);
    $item = $items->base_shmot($item_id);
    msg('Вы купили ' . ($item['name'] ?? 'товар') . '.', 1);
    fin();
}

fin();
