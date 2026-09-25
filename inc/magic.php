<?php
/**
 * Магия в бою.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНЫ баги: в case 3 и 4 обновлялся $me вместо $hz.
 */

if (($me['magic_hod'] ?? 0) == $boi['round']) {
    msg2('Вы уже кастовали заклинание в этом ходу.', 1);
}

$mananow = (int)($me['mananow'] ?? 0);

switch ($magic) {
    default:
        if ($mananow >= 25)  knopka('battle.php?mod=magic&magic=1&r=' . mt_rand(1, 999), 'Лечение (25 MP)', 1);
        if ($mananow >= 50)  knopka('battle.php?mod=magic&magic=2&r=' . mt_rand(1, 999), 'Исцеление (50 MP)', 1);
        if ($mananow >= 70)  knopka('battle.php?mod=magic&magic=3&r=' . mt_rand(1, 999), 'Помощь (70 MP)', 1);
        if ($mananow >= 250) knopka('battle.php?mod=magic&magic=4&r=' . mt_rand(1, 999), 'Воскрешение (250 MP)', 1);
        knopka('battle.php?r=' . mt_rand(1, 999), 'Вернуться', 1);
        fin();
        break;

    case 1:
        if ($mananow < 25) msg2('У вас недостаточно маны', 1);
        if (mt_rand(1, 100) <= 90) {
            $regen = mt_rand(15 * (int)$me['lvl'], 20 * (int)$me['lvl']);
            if ($regen + $me['hpnow'] > $me['hpmax']) $regen = $me['hpmax'] - $me['hpnow'];
            $me['hpnow'] += $regen;
            $log_hp = '<span style="color:' . $notice . '">' . $me['login'] . ' лечит магией ' . $regen . ' hp</span><br/><br/>';
            $db->query("UPDATE `users` SET `hpnow` = " . (int)$me['hpnow'] . ", `magic_hod` = " . (int)$boi['round'] . " WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
            $db->query("UPDATE `combat` SET `hpnow` = " . (int)$me['hpnow'] . " WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
        } else {
            $log_hp = '<span style="color:' . $notice . '">' . $me['login'] . ' делает сложные пассы руками, но ничего не происходит</span><br/><br/>';
            $db->query("UPDATE `users` SET `magic_hod` = " . (int)$boi['round'] . " WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        }
        $db->query("UPDATE `users` SET `mananow` = `mananow` - 25 WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        $db->query("UPDATE `combat` SET `mananow` = `mananow` - 25 WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
        $db->query("INSERT INTO `battlelog` VALUES (0, {$bid}, '{$t}', '{$log_hp}');");
        break;

    case 2:
        if ($mananow < 50) msg2('У вас недостаточно маны', 1);
        if (mt_rand(1, 100) <= 70) {
            $regen = mt_rand(20 * (int)$me['lvl'], 30 * (int)$me['lvl']);
            if ($regen + $me['hpnow'] > $me['hpmax']) $regen = $me['hpmax'] - $me['hpnow'];
            $me['hpnow'] += $regen;
            $log_hp = '<span style="color:' . $notice . '">' . $me['login'] . ' исцеляет магией ' . $regen . ' hp</span><br/><br/>';
            $db->query("UPDATE `users` SET `hpnow` = " . (int)$me['hpnow'] . ", `magic_hod` = " . (int)$boi['round'] . " WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
            $db->query("UPDATE `combat` SET `hpnow` = " . (int)$me['hpnow'] . " WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
        } else {
            $log_hp = '<span style="color:' . $notice . '">' . $me['login'] . ' делает сложные пассы руками, но ничего не происходит</span><br/><br/>';
            $db->query("UPDATE `users` SET `magic_hod` = " . (int)$boi['round'] . " WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        }
        $db->query("UPDATE `users` SET `mananow` = `mananow` - 50 WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        $db->query("UPDATE `combat` SET `mananow` = `mananow` - 50 WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
        $db->query("INSERT INTO `battlelog` VALUES (0, {$bid}, '{$t}', '{$log_hp}');");
        break;

    case 3:
        if ($mananow < 70) msg2('У вас недостаточно маны', 1);
        $lgn = isset($_REQUEST['lgn']) ? (int)$_REQUEST['lgn'] : 0;
        $kom3 = [];
        $q = $db->query("SELECT * FROM `combat` WHERE `boi_id` = {$bid} AND `komanda` = " . (int)$me['komanda'] . " AND `login` <> '" . $db->real_escape_string($me['login']) . "' AND `hpnow` > 0;");
        if ($q) {
            while ($hz = $q->fetch_assoc()) {
                $kom3[(int)$hz['id']] = $hz['login'] . ' (' . $hz['hpnow'] . '/' . $hz['hpmax'] . ')';
            }
        }
        if (empty($lgn)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="battle.php?mod=magic&magic=3" method="POST">';
            echo 'Помочь:<br/>';
            echo '<select name="lgn">';
            foreach ($kom3 as $key => $val) echo '<option value="' . $key . '">' . $val . '</option>';
            echo '</select><br/>';
            echo '<input type="submit" value="Кастовать"></form></div>';
            knopka('battle.php', 'Вернуться', 1);
            fin();
        }
        if ($lgn <= 0) msg2('Не выбран союзник для помощи!', 1);
        $q = $db->query("SELECT * FROM `combat` WHERE `id` = {$lgn} AND `boi_id` = {$bid} AND `hpnow` > 0 AND `komanda` = " . (int)$me['komanda'] . " AND `login` <> '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        $hz = $q ? $q->fetch_assoc() : null;
        if (!$hz) msg2('Боец не найден!', 1);

        if (mt_rand(1, 100) <= 70) {
            $regen = mt_rand(10 * (int)$me['lvl'], 20 * (int)$me['lvl']);
            if ($regen + $hz['hpnow'] > $hz['hpmax']) $regen = $hz['hpmax'] - $hz['hpnow'];
            $hz['hpnow'] += $regen;
            $log_hp = '<span style="color:' . $notice . '">' . $me['login'] . ' помогает ' . $hz['login'] . ', исцеляя ' . $regen . ' hp</span><br/><br/>';
            // ИСПРАВЛЕНО: обновляем HP $hz, а не $me
            if (empty($hz['flag_bot'])) {
                $db->query("UPDATE `users` SET `hpnow` = " . (int)$hz['hpnow'] . " WHERE `login` = '" . $db->real_escape_string($hz['login']) . "' LIMIT 1;");
            }
            $db->query("UPDATE `users` SET `magic_hod` = " . (int)$boi['round'] . " WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
            $db->query("UPDATE `combat` SET `hpnow` = " . (int)$hz['hpnow'] . " WHERE `id` = " . (int)$hz['id'] . " LIMIT 1;");
        } else {
            $log_hp = '<span style="color:' . $notice . '">' . $me['login'] . ' пытается помочь ' . $hz['login'] . ', но ничего не происходит</span><br/><br/>';
            $db->query("UPDATE `users` SET `magic_hod` = " . (int)$boi['round'] . " WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        }
        $db->query("UPDATE `users` SET `mananow` = `mananow` - 70 WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        $db->query("UPDATE `combat` SET `mananow` = `mananow` - 70 WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
        $db->query("INSERT INTO `battlelog` VALUES (0, {$bid}, '{$t}', '{$log_hp}');");
        break;

    case 4:
        if ($mananow < 250) msg('У вас недостаточно маны', 1);
        $lgn = isset($_REQUEST['lgn']) ? (int)$_REQUEST['lgn'] : 0;
        $kom3 = [];
        $q = $db->query("SELECT * FROM `combat` WHERE `boi_id` = {$bid} AND `komanda` = " . (int)$me['komanda'] . " AND `hpnow` < 1 AND `login` <> '" . $db->real_escape_string($me['login']) . "';");
        if ($q) {
            while ($hz = $q->fetch_assoc()) {
                $kom3[(int)$hz['id']] = $hz['login'] . ' (' . $hz['hpnow'] . '/' . $hz['hpmax'] . ')';
            }
        }
        if (empty($lgn)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="battle.php?mod=magic&magic=4" method="POST">';
            echo 'Воскресить:<br/>';
            echo '<select name="lgn">';
            foreach ($kom3 as $key => $val) echo '<option value="' . $key . '">' . $val . '</option>';
            echo '</select><br/>';
            echo '<input type="submit" value="Кастовать"></form></div>';
            knopka('battle.php', 'Вернуться', 1);
            fin();
        }
        if ($lgn <= 0) msg2('Не выбран союзник для воскрешения!', 1);
        $q = $db->query("SELECT * FROM `combat` WHERE `id` = {$lgn} AND `boi_id` = {$bid} AND `hpnow` < 1 AND `komanda` = " . (int)$me['komanda'] . " AND `login` <> '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        $hz = $q ? $q->fetch_assoc() : null;
        if (!$hz) msg2('Боец не найден!', 1);

        if (mt_rand(1, 100) <= 60) {
            $regen = (int)($hz['hpmax'] * 0.5);
            $hz['hpnow'] = $regen;
            $log_hp = '<span style="color:' . $notice . '">' . $me['login'] . ' воскрешает ' . $hz['login'] . ' с половиной hp</span><br/><br/>';
            // ИСПРАВЛЕНО: обновляем HP $hz, а не $me
            if (empty($hz['flag_bot'])) {
                $db->query("UPDATE `users` SET `hpnow` = " . (int)$hz['hpnow'] . " WHERE `login` = '" . $db->real_escape_string($hz['login']) . "' LIMIT 1;");
            }
            $db->query("UPDATE `users` SET `magic_hod` = " . (int)$boi['round'] . " WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
            $db->query("UPDATE `combat` SET `hpnow` = " . (int)$hz['hpnow'] . " WHERE `id` = " . (int)$hz['id'] . " LIMIT 1;");
        } else {
            $log_hp = '<span style="color:' . $notice . '">' . $me['login'] . ' пытается воскресить ' . $hz['login'] . ', но ничего не происходит</span><br/><br/>';
            $db->query("UPDATE `users` SET `magic_hod` = " . (int)$boi['round'] . " WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        }
        $db->query("UPDATE `users` SET `mananow` = `mananow` - 250 WHERE `login` = '" . $db->real_escape_string($me['login']) . "' LIMIT 1;");
        $db->query("UPDATE `combat` SET `mananow` = `mananow` - 250 WHERE `id` = " . (int)$me['id'] . " LIMIT 1;");
        $db->query("INSERT INTO `battlelog` VALUES (0, {$bid}, '{$t}', '{$log_hp}');");
        break;
}
