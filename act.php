<?php
/**
 * Старт боя по локации.
 * PHP 8.2-совместимая версия.
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';
require_once __DIR__ . '/inc/boi.php';
require_once __DIR__ . '/inc/bot.php';

// Проверки состояния
if ($f['status'] == 1) {
    knopka('battle.php', 'Вы в бою!', 1);
    fin();
}
if ($f['status'] == 2) {
    knopka('arena.php', 'У вас заявка на арене!', 1);
    fin();
}
if ($f['hpnow'] <= 0) {
    knopka('loc.php', 'Восстановите здоровье', 1);
    fin();
}
if ($f['rabota'] > $t) {
    knopka('loc.php', 'Вы работаете!', 1);
    fin();
}

/**
 * Присоединиться к бою с боссом.
 */
function joinBossFight($db, $f, $t, $bot_name, $max_players, $start_hour, $start_minute)
{
    $hour = (int)date('H');
    $minute = (int)date('i');
    if ($hour != $start_hour || $minute > $start_minute) {
        msg2('Зайти в бой могут только ' . $max_players . ' человек, и только с ' . sprintf('%02d:00', $start_hour) . ' и до ' . sprintf('%02d:%02d', $start_hour, $start_minute), 1);
    }
    $bot_esc = $db->real_escape_string($bot_name);
    $num = 0;
    $q = $db->query("SELECT `boi_id` FROM `combat` WHERE `login` = '{$bot_esc}' LIMIT 1;");
    $bz = $q ? $q->fetch_assoc() : null;
    $boi_id = $bz ? (int)$bz['boi_id'] : 0;

    if ($boi_id == 0 && $bz) {
        $db->query("DELETE FROM `combat` WHERE `login` = '{$bot_esc}' LIMIT 1;");
    }

    if (empty($boi_id)) {
        $db->query("UPDATE `users` SET `doping` = 0, `doping_time` = 0 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        $f = calcparam($f);
        $boi_id = addBoi(1);
        addBot($bot_name, 1);
        toBoi($f, 2);
    } else {
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `users` WHERE `boi_id` = {$boi_id};");
        $num = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($num < $max_players) {
            $db->query("UPDATE `users` SET `doping` = 0, `doping_time` = 0 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
            $f = calcparam($f);
            toBoi($f, 2);
        } else {
            msg2('Босса окружили уже ' . $num . ' бойцов, вы не можете к нему протиснуться!', 1);
        }
    }
}

// Локация → боты
switch ((int)$f['loc']) {

    case 1:
        if ($f['lvl'] > 3) msg2('Вы уже опытный воин, тренеры вам не нужны', 1);
        $boi_id = addBoi(0);
        if ($f['lvl'] == 1)      addBot('Младший тренер', $f['lvl']);
        elseif ($f['lvl'] == 2)  addBot('Тренер', $f['lvl']);
        else                     addBot('Старший тренер', $f['lvl']);
        toBoi($f, 2);
        break;

    case 5:
        if ($f['lvl'] < 4) { knopka('loc.php', 'Доступно с 4 уровня', 1); fin(); }
        $boi_id = addBoi(0);
        addBot('Падальщик', $f['lvl'] + 1);
        addBot('Молодой падальщик', $f['lvl']);
        toBoi($f, 2);
        break;

    case 8:
        if ($f['lvl'] < 4) { knopka('loc.php', 'Доступно с 4 уровня', 1); fin(); }
        $boi_id = addBoi(0);
        addBot('Кротокрыс', $f['lvl'] + 1);
        addBot('Молодой кротокрыс', $f['lvl']);
        toBoi($f, 2);
        break;

    case 9:
        $boi_id = addBoi(0);
        addBot('Мясной жук', $f['lvl']);
        toBoi($f, 2);
        break;

    case 11:
        if ($f['lvl'] < 4) { knopka('loc.php', 'Доступно с 4 уровня', 1); fin(); }
        $boi_id = addBoi(0);
        addBot('Стервятник', $f['lvl'] + 1);
        addBot('Молодой стервятник', $f['lvl']);
        toBoi($f, 2);
        break;

    case 13:
        if ($f['lvl'] < 4) { knopka('loc.php', 'Доступно с 4 уровня', 1); fin(); }
        $boi_id = addBoi(1);
        addBot('Кровосос', $f['lvl'] + 1);
        addBot('Шершень', $f['lvl']);
        toBoi($f, 2);
        break;

    case 14:
        if ($f['lvl'] < 4) { knopka('loc.php', 'Доступно с 4 уровня', 1); fin(); }
        $boi_id = addBoi(0);
        addBot('Волк', $f['lvl'] + 1);
        addBot('Волчица', $f['lvl']);
        toBoi($f, 2);
        break;

    case 20:
        if ($f['lvl'] < 3) { knopka('loc.php', 'Доступно с 3 уровня', 1); fin(); }
        $boi_id = addBoi(1);
        addBot('Остер', $f['lvl'] + 1);
        toBoi($f, 2);
        break;

    case 21:
        if ($f['lvl'] < 4) { knopka('loc.php', 'Доступно с 4 уровня', 1); fin(); }
        $boi_id = addBoi(1);
        addBot('Черный гоблин', $f['lvl'] + 1);
        addBot('Гоблин', $f['lvl']);
        toBoi($f, 2);
        break;

    case 22:
        if ($f['lvl'] < 10) { knopka('loc.php', 'Доступно с 10 уровня', 1); fin(); }
        joinBossFight($db, $f, $t, 'Тролль', 25, 19, 10);
        break;

    case 27:
        if ($f['lvl'] < 4) { knopka('loc.php', 'Доступно с 4 уровня', 1); fin(); }
        $boi_id = addBoi(0);
        addBot('Глорх', $f['lvl'] + 1);
        addBot('Кусач', $f['lvl']);
        toBoi($f, 2);
        break;

    case 39:
        if ($f['lvl'] < 3) { knopka('loc.php', 'Доступно с 3 уровня', 1); fin(); }
        $boi_id = addBoi(1);
        addBot('Шмыг', $f['lvl']);
        toBoi($f, 2);
        break;

    case 41:
        if ($f['lvl'] < 4) { knopka('loc.php', 'Доступно с 4 уровня', 1); fin(); }
        $boi_id = addBoi(1);
        addBot('Оборотень', $f['lvl'] + 1);
        addBot('Упырь', $f['lvl']);
        toBoi($f, 2);
        break;

    case 43:
        $kvest = !empty($f['kvest']) ? @unserialize($f['kvest']) : [];
        if (!is_array($kvest)) $kvest = [];
        $kv = $kvest['loc56ks'] ?? [];
        if (($kv['nagrada'] ?? 0) == 1) { knopka('loc.php', 'Ошибка локации', 1); fin(); }
        if (($kv['lg'] ?? 0) == 1) msg2('Сердце ледяного голема уже у вас!', 1);
        $boi_id = addBoi(1);
        addBot('Ледяной голем', $f['lvl'] + 5);
        toBoi($f, 2);
        break;

    case 49:
        if ($f['lvl'] < 4) { knopka('loc.php', 'Доступно с 4 уровня', 1); fin(); }
        $boi_id = addBoi(0);
        addBot('Орочий маг', $f['lvl'] + 1);
        addBot('Орочий шаман', $f['lvl']);
        toBoi($f, 2);
        break;

    case 55:
        $kvest = !empty($f['kvest']) ? @unserialize($f['kvest']) : [];
        if (!is_array($kvest)) $kvest = [];
        $kv = $kvest['loc56ks'] ?? [];
        if (($kv['nagrada'] ?? 0) == 1) { knopka('loc.php', 'Ошибка локации', 1); fin(); }
        if (($kv['og'] ?? 0) == 1) msg2('Сердце огненного голема уже у вас!', 1);
        $boi_id = addBoi(1);
        addBot('Огненный голем', $f['lvl'] + 5);
        toBoi($f, 2);
        break;

    case 62:
        $kvest = !empty($f['kvest']) ? @unserialize($f['kvest']) : [];
        if (!is_array($kvest)) $kvest = [];
        $kv = $kvest['loc56ks'] ?? [];
        if (($kv['nagrada'] ?? 0) == 1) { knopka('loc.php', 'Ошибка локации', 1); fin(); }
        if (($kv['kg'] ?? 0) == 1) msg2('Сердце каменного голема уже у вас!', 1);
        $boi_id = addBoi(1);
        addBot('Каменный голем', $f['lvl'] + 5);
        toBoi($f, 2);
        break;

    case 67:
        $boi_id = addBoi(0);
        addBot('Гарпия', $f['lvl']);
        toBoi($f, 2);
        break;

    case 73:
        if ($f['lvl'] < 4) { knopka('loc.php', 'Доступно с 4 уровня', 1); fin(); }
        $boi_id = addBoi(0);
        addBot('Орочий воин', $f['lvl'] + 1);
        addBot('Орк', $f['lvl']);
        toBoi($f, 2);
        break;

    case 89:
        if ($f['lvl'] < 3) { knopka('loc.php', 'Доступно с 3 уровня', 1); fin(); }
        $boi_id = addBoi(1);
        addBot('Болотожор', $f['lvl']);
        toBoi($f, 2);
        break;

    case 92:
        if ($f['lvl'] < 6) { knopka('loc.php', 'Доступно с 6 уровня', 1); fin(); }
        $boi_id = addBoi(2);
        addBot('Огненная ящерица', $f['lvl'] + 2);
        addBot('Огненный варан', $f['lvl'] + 1);
        toBoi($f, 2);
        break;

    case 95:
        if ($f['lvl'] < 3) { knopka('loc.php', 'Доступно с 3 уровня', 1); fin(); }
        $boi_id = addBoi(1);
        if (mt_rand(1, 100) <= 50) addBot('Скелет', $f['lvl'] + 1);
        else                        addBot('Зомби', $f['lvl'] + 1);
        toBoi($f, 2);
        break;

    case 96:
        if ($f['lvl'] < 10) { knopka('loc.php', 'Доступно с 10 уровня', 1); fin(); }
        joinBossFight($db, $f, $t, 'Дракон', 20, 22, 10);
        break;

    case 100:
        $boi_id = addBoi(0);
        addBot('Ползун', $f['lvl']);
        toBoi($f, 2);
        break;

    case 103:
        if ($f['lvl'] < 6) { knopka('loc.php', 'Доступно с 6 уровня', 1); fin(); }
        $boi_id = addBoi(0);
        addBot('Шелкопряд', $f['lvl']);
        toBoi($f, 2);
        break;

    default:
        knopka('loc.php', 'Ошибка локации', 1);
        fin();
}

header('Location: battle.php');
fin();
