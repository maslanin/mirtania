<?php
/**
 * Лотерея, свитки опыта, точильный камень.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: пересечение диапазонов 85 (было 76-85 и 85-91).
 */

$login_esc = $db->real_escape_string($f['login']);
$item_id   = (int)$item['id'];

switch ((int)$item['ido']) {

    case 123: // Лотерейный билет
        if (empty($ok)) {
            msg2('Вы хотите разыграть Лотерейный билет.');
            knopka('inv.php?mod=useitem&iid=' . $iid . '&ok=1', 'Продолжить', 1);
            knopka('inv.php', 'Вернуться', 1);
            fin();
        }
        $rnd = mt_rand(1, 100);

        if ($rnd >= 1 && $rnd <= 50) {
            $summ = 5000;
            $f['money'] += $summ;
            $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
            $items->del_item($f['login'], $item_id, 1);
            msg2('Вы выиграли в лотерею ' . $summ . ' монет.', 1);
        } elseif ($rnd >= 51 && $rnd <= 60) {
            $summ = 25000;
            $f['money'] += $summ;
            $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
            $items->del_item($f['login'], $item_id, 1);
            msg2('Вы выиграли в лотерею ' . $summ . ' монет.', 1);
        } elseif ($rnd >= 61 && $rnd <= 70) {
            $items->add_item($f['login'], (int)$item['ido'], 1);
            msg2('Вы выиграли в лотерею еще один лотерейный билет. Старый билет остался при вас.', 1);
        } elseif ($rnd >= 71 && $rnd <= 75) {
            $items->del_item($f['login'], $item_id, 1);
            $items->add_item($f['login'], 127, 1);
            msg2('Вы выиграли в лотерею Точильный камень.', 1);
        } elseif ($rnd >= 76 && $rnd <= 84) {
            $items->del_item($f['login'], $item_id, 1);
            $items->add_item($f['login'], 124, 1);
            msg2('Вы выиграли в лотерею Свиток опыта 1 ступени.', 1);
        } elseif ($rnd >= 85 && $rnd <= 91) {
            $items->del_item($f['login'], $item_id, 1);
            $items->add_item($f['login'], 125, 1);
            msg2('Вы выиграли в лотерею Свиток опыта 2 ступени.', 1);
        } elseif ($rnd >= 92 && $rnd <= 95) {
            $items->del_item($f['login'], $item_id, 1);
            $items->add_item($f['login'], 126, 1);
            msg2('Вы выиграли в лотерею Свиток опыта 3 ступени.', 1);
        } elseif ($rnd >= 96 && $rnd <= 100) {
            $items->del_item($f['login'], $item_id, 1);
            $int = (int)$f['lvl'] + 127;
            $ido = $items->add_item($f['login'], $int, 1);
            if (date('d.m') == '30.12' || date('d.m') == '31.12') {
                $db->query("UPDATE `invent` SET `up` = 25 WHERE `id` = " . (int)$ido . " LIMIT 1;");
            }
            $db->query("UPDATE `invent` SET `name` = 'Именной браслет " . $login_esc . "', `info` = 'Выдано персонажу " . $login_esc . " за выигрыш в лотерею' WHERE `id` = " . (int)$ido . " LIMIT 1;");
            msg2('Вы выиграли лотерейный браслет на ' . $f['lvl'] . ' уровень.', 1);
        } else {
            msg2('Произошла какая-то ошибка.', 1);
        }
        break;

    case 124: // Свиток опыта 1
        $exxp = 7500 * (int)$f['lvl'];
        if (empty($ok)) {
            msg2('Вы хотите получить ' . $exxp . ' опыта.');
            knopka('inv.php?mod=useitem&iid=' . $iid . '&ok=1', 'Продолжить', 1);
            knopka('inv.php', 'Вернуться', 1);
            fin();
        }
        addexp($f['id'], $exxp);
        $items->del_item($f['login'], $item_id, 1);
        msg2('Вы получили ' . $exxp . ' опыта.', 1);
        break;

    case 125: // Свиток опыта 2
        $exxp = 12500 * (int)$f['lvl'];
        if (empty($ok)) {
            msg2('Вы хотите получить ' . $exxp . ' опыта.');
            knopka('inv.php?mod=useitem&iid=' . $iid . '&ok=1', 'Продолжить', 1);
            knopka('inv.php', 'Вернуться', 1);
            fin();
        }
        addexp($f['id'], $exxp);
        $items->del_item($f['login'], $item_id, 1);
        msg2('Вы получили ' . $exxp . ' опыта.', 1);
        break;

    case 126: // Свиток опыта 3
        $exxp = 20000 * (int)$f['lvl'];
        if (empty($ok)) {
            msg2('Вы хотите получить ' . $exxp . ' опыта.');
            knopka('inv.php?mod=useitem&iid=' . $iid . '&ok=1', 'Продолжить', 1);
            knopka('inv.php', 'Вернуться', 1);
            fin();
        }
        addexp($f['id'], $exxp);
        $items->del_item($f['login'], $item_id, 1);
        msg2('Вы получили ' . $exxp . ' опыта.', 1);
        break;

    case 127: // Точильный камень
        $look = isset($_REQUEST['look']) ? (int)$_REQUEST['look'] : 0;
        if (empty($look)) {
            $q = $db->query("SELECT `id` FROM `invent` WHERE `up` <> 0 AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 AND `login` = '{$login_esc}';");
            if ($q === false || $q->num_rows === 0) {
                msg('У вас нет вещей, пригодных для снятия модификации', 1);
            }
            msg('Выберите нужную вещь:');
            while ($a = $q->fetch_assoc()) {
                $item2 = $items->shmot((int)$a['id']);
                if ($item2 === null) continue;
                knopka('inv.php?mod=useitem&iid=' . $iid . '&look=' . $item2['id'], $item2['name']);
            }
            fin();
        }
        if ($look <= 0) msg('Вещь не найдена в вашем рюкзаке!', 1);
        $q = $db->query("SELECT `id` FROM `invent` WHERE `id` = {$look} AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 LIMIT 1;");
        if ($q === false || $q->num_rows === 0) msg('Вещь не найдена в вашем рюкзаке!', 1);
        $a = $q->fetch_assoc();
        $item2 = $items->shmot((int)$a['id']);
        if ($item2 === null) msg('Вещь не найдена', 1);
        if (empty($ok)) {
            msg('Вы уверены, что хотите снять модификацию с ' . $item2['name'] . '?');
            knopka('inv.php?mod=useitem&iid=' . $iid . '&look=' . $look . '&ok=1', 'Снять модификацию');
            knopka('inv.php', 'Инвентарь');
            fin();
        }
        $db->query("UPDATE `invent` SET `up` = 0 WHERE `id` = {$look} AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 LIMIT 1;");
        $items->del_item($f['login'], $iid, 1);
        msg2('Модификация с ' . $item2['name'] . ' успешно снята.', 1);
        break;
}
