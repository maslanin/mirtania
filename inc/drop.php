<?php
/**
 * Дроп с ботов.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕН БАГ: Тролль и Дракон выдавали предмет $me, а не $winner.
 */

if (empty($uz['flag_bot'])) {
    return;
}

$uz_login = $uz['login'];
$me_login = $me['login'];
$me_login_esc = $db->real_escape_string($me_login);

// Обычный дроп: [имя бота => [item_id, шанс]]
$dropTable = [
    'Гарпия'         => [168, 20],
    'Ползун'         => [169, 20],
    'Остер'          => [162, 20],
    'Черный гоблин'  => [161, 20],
    'Падальщик'      => [160, 20],
    'Кротокрыс'      => [159, 20],
    'Орочий маг'     => [158, 20],
    'Шелкопряд'      => [164, 40],
];

if (isset($dropTable[$uz_login])) {
    [$itemId, $chance] = $dropTable[$uz_login];
    if (mt_rand(1, 100) <= $chance) {
        $item = $items->base_shmot($itemId);
        if ($item !== null) {
            if (!empty($me['klan'])) klan_points($me['klan'], 1);
            $udar_log = '<span style="color:' . $female . '">' . $me_login . ' выбивает ' . $item['name'] . '</span> <br/> ' . $udar_log;
            $items->add_item($me_login, $item['id'], 1);
        }
    }
}

// Големы — прогресс квеста
$golems = [
    'Ледяной голем'  => 'lg',
    'Огненный голем' => 'og',
    'Каменный голем' => 'kg',
];
if (isset($golems[$uz_login])) {
    $key = $golems[$uz_login];
    $kvest = !empty($f['kvest']) ? @unserialize($f['kvest']) : [];
    if (!is_array($kvest)) $kvest = [];
    if (!isset($kvest['loc56ks']) || !is_array($kvest['loc56ks'])) $kvest['loc56ks'] = [];
    $kvest['loc56ks'][$key] = 1;
    $f['kvest'] = serialize($kvest);
    $kv_esc = $db->real_escape_string($f['kvest']);
    $udar_log = '<span style="color:' . $female . '">' . $me_login . ' выбивает Сердце ' . mb_strtolower(str_replace(' голем', '', $uz_login), 'UTF-8') . ' голема</span> <br/> ' . $udar_log;
    $db->query("UPDATE `users` SET `kvest` = '{$kv_esc}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
}

// Тролль и Дракон — предмет получает winner
if ($uz_login === 'Тролль' || $uz_login === 'Дракон') {
    $winner = null;
    $q = $db->query("SELECT `login` FROM `combat` WHERE `boi_id` = " . (int)$me['boi_id'] . " AND `uron_boi` > 999 AND `flag_bot` = 0 AND `komanda` = 2;");
    if ($q && $q->num_rows > 0) {
        $l = [];
        while ($logins = $q->fetch_assoc()) {
            $l[] = $logins['login'];
        }
        if (!empty($l)) {
            shuffle($l);
            $winner = $l[0];
        }
    }
    if ($winner !== null) {
        $itemId = ($uz_login === 'Тролль') ? 127 : 157;
        $item = $items->base_shmot($itemId);
        if ($item !== null) {
            $winner_esc = $db->real_escape_string($winner);
            if ($uz_login === 'Тролль') {
                $udar_log = '<span style="color:' . $female . '">' . $winner . ' подбирает с распростертого тролля ' . $item['name'] . '</span> <br/> ' . $udar_log;
            } else {
                $udar_log = '<span style="color:' . $female . '">' . $winner . ' подбирает с убитого монстра ' . $item['name'] . '</span> <br/> ' . $udar_log;
            }
            // ИСПРАВЛЕНО: было $me_login, стало $winner
            $items->add_item($winner_esc, $item['id'], 1);
        }
    }
}

// Случайный дроп свитков
if (mt_rand(1, 100) <= 1) {
    $item = $items->base_shmot(121); // свиток нападения
    if ($item !== null) {
        if (!empty($me['klan'])) klan_points($me['klan'], 1);
        $udar_log = '<span style="color:' . $female . '">' . $me_login . ' выбивает ' . $item['name'] . '</span> <br/> ' . $udar_log;
        $items->add_item($me_login, $item['id'], 1);
    }
}
if (mt_rand(1, 100) <= 1) {
    $item = $items->base_shmot(122); // свиток развоплощения
    if ($item !== null) {
        if (!empty($me['klan'])) klan_points($me['klan'], 1);
        $udar_log = '<span style="color:' . $female . '">' . $me_login . ' выбивает ' . $item['name'] . '</span> <br/> ' . $udar_log;
        $items->add_item($me_login, $item['id'], 1);
    }
}
