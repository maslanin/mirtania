<?php
/**
 * Рыбалка.
 * PHP 8.2-совместимая версия.
 */

if ($f['lvl'] < 6) {
    knopka('loc.php', 'Доступно с 6 уровня', 1);
    fin();
}
if ($f['fishrod'] == 0) {
    knopka('loc.php', 'У вас нет удочки', 1);
    fin();
}

msg2('Навык рыболова: ' . $f['p_fishman'] . ' | Прочность удочки: ' . $f['fishrod']);

$q = $db->query("SELECT `ido`, COUNT(*) AS `c` FROM `invent` WHERE `login` = '" . $db->real_escape_string($f['login']) . "' AND (`ido` = 166 OR `ido` = 167 OR `ido` = 170 OR `ido` = 171 OR `ido` = 172 OR `ido` = 173) AND `flag_equip` = 0 AND `flag_rinok` = 0 AND `flag_sklad` = 0 AND `flag_arenda` = 0 GROUP BY `ido` ORDER BY `ido` DESC;");
if (!$q || $q->num_rows == 0) msg2('Вы не можете рыбачить, сначала нужно найти наживку.', 1);

$iid = isset($_REQUEST['iid']) ? (int)$_REQUEST['iid'] : 0;
$ok = isset($_REQUEST['ok']) ? $_REQUEST['ok'] : '';

if (empty($ok) && empty($iid)) {
    echo '<div class="board" style="text-align:left">';
    echo '<form action="kvest.php?ok=1" method="POST">';
    echo 'Выберите наживку:<br/>';
    echo '<select name="iid">';
    while ($s = $q->fetch_assoc()) {
        $item = $items->base_shmot((int)$s['ido']);
        if ($item === null) continue;
        echo '<option value="' . $s['ido'] . '">' . $item['name'] . ' (' . $s['c'] . ' шт.)</option>';
    }
    echo '</select><br/>';
    echo '<input type="submit" value="Далее" /></form></div>';
    knopka('loc.php', 'Вернуться', 1);
    fin();
}

if ($iid != 166 && $iid != 167 && $iid != 170 && $iid != 171 && $iid != 172 && $iid != 173) msg2('Неизвестная наживка...', 1);
if ($items->count_base_item($f['login'], $iid) == 0) msg2('У вас нет такой наживки', 1);

$fish = [];
$shns = (int)$f['p_fishman'];
switch ($iid) {
    case 173:
        $shns += 3;
        $fish[] = 174;
        if ($f['p_fishman'] >= 20) $fish[] = 175;
        if ($f['p_fishman'] >= 30) $fish[] = 176;
        break;
    case 172:
        $shns += 5;
        $fish[] = 174;
        if ($f['p_fishman'] >= 20) $fish[] = 175;
        if ($f['p_fishman'] >= 30) $fish[] = 176;
        if ($f['p_fishman'] >= 50) $fish[] = 177;
        if ($f['p_fishman'] >= 80) $fish[] = 178;
        if ($f['p_fishman'] >= 110) $fish[] = 179;
        break;
    case 171:
        $shns += 7;
        $fish[] = 174;
        if ($f['p_fishman'] >= 20) $fish[] = 175;
        if ($f['p_fishman'] >= 30) $fish[] = 176;
        if ($f['p_fishman'] >= 50) $fish[] = 177;
        if ($f['p_fishman'] >= 80) $fish[] = 178;
        if ($f['p_fishman'] >= 110) $fish[] = 179;
        if ($f['p_fishman'] >= 140) $fish[] = 180;
        if ($f['p_fishman'] >= 170) $fish[] = 181;
        if ($f['p_fishman'] >= 200) $fish[] = 182;
        break;
    case 170:
        $shns += 10;
        $fish[] = 174;
        if ($f['p_fishman'] >= 20) $fish[] = 175;
        if ($f['p_fishman'] >= 30) $fish[] = 176;
        if ($f['p_fishman'] >= 50) $fish[] = 177;
        if ($f['p_fishman'] >= 80) $fish[] = 178;
        if ($f['p_fishman'] >= 110) $fish[] = 179;
        if ($f['p_fishman'] >= 140) $fish[] = 180;
        if ($f['p_fishman'] >= 170) $fish[] = 181;
        if ($f['p_fishman'] >= 200) $fish[] = 182;
        if ($f['p_fishman'] >= 250) $fish[] = 183;
        if ($f['p_fishman'] >= 400) $fish[] = 184;
        if ($f['p_fishman'] >= 500) $fish[] = 185;
        break;
    case 167:
        $shns += 12;
        $fish[] = 174;
        if ($f['p_fishman'] >= 20) $fish[] = 175;
        if ($f['p_fishman'] >= 30) $fish[] = 176;
        if ($f['p_fishman'] >= 50) $fish[] = 177;
        if ($f['p_fishman'] >= 80) $fish[] = 178;
        if ($f['p_fishman'] >= 110) $fish[] = 179;
        if ($f['p_fishman'] >= 140) $fish[] = 180;
        if ($f['p_fishman'] >= 170) $fish[] = 181;
        if ($f['p_fishman'] >= 200) $fish[] = 182;
        if ($f['p_fishman'] >= 250) $fish[] = 183;
        if ($f['p_fishman'] >= 400) $fish[] = 184;
        if ($f['p_fishman'] >= 500) $fish[] = 185;
        if ($f['p_fishman'] >= 600) $fish[] = 186;
        if ($f['p_fishman'] >= 700) $fish[] = 187;
        if ($f['p_fishman'] >= 800) $fish[] = 188;
        break;
    case 166:
        $shns += 13;
        $fish[] = 174;
        if ($f['p_fishman'] >= 20) $fish[] = 175;
        if ($f['p_fishman'] >= 30) $fish[] = 176;
        if ($f['p_fishman'] >= 50) $fish[] = 177;
        if ($f['p_fishman'] >= 80) $fish[] = 178;
        if ($f['p_fishman'] >= 110) $fish[] = 179;
        if ($f['p_fishman'] >= 140) $fish[] = 180;
        if ($f['p_fishman'] >= 170) $fish[] = 181;
        if ($f['p_fishman'] >= 200) $fish[] = 182;
        if ($f['p_fishman'] >= 250) $fish[] = 183;
        if ($f['p_fishman'] >= 400) $fish[] = 184;
        if ($f['p_fishman'] >= 500) $fish[] = 185;
        if ($f['p_fishman'] >= 600) $fish[] = 186;
        if ($f['p_fishman'] >= 700) $fish[] = 187;
        if ($f['p_fishman'] >= 800) $fish[] = 188;
        if ($f['p_fishman'] >= 900) $fish[] = 189;
        if ($f['p_fishman'] >= 1000) $fish[] = 190;
        break;
}

if ($shns < 20) $shns = 20;
if ($shns > 90) $shns = 90;
if ($f['vip'] > time()) $shns = 100;

msg2('Вы забросили удочку. Шанс на удачу ' . $shns . '%');

if (mt_rand(1, 100) <= $shns) {
    shuffle($fish);
    $item = $items->base_shmot($fish[0]);
    if ($item !== null) {
        $items->del_base_item($f['login'], $iid, 1);
        $items->add_item($f['login'], $fish[0]);
        $db->query("UPDATE `users` SET `p_fishman` = `p_fishman` + 1 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2('[Поймано: ' . $item['name'] . ']');
    }
} else {
    $slova = [
        'Поплавок задергался, вы подсекли, но рыба сорвалась...',
        'Поплавок задергался, вы плавно потянули удилище на себя, но рыба сорвалась...',
        'Поплавок задергался и резко утонул, вы дернули удочку, но рыба сорвалась...',
        'Поплавок задергался и резко утонул, плавно потянули удочку, но рыба сорвалась...',
        'Вы не дождались поклевки...',
        'Не клюет...',
    ];
    shuffle($slova);
    msg2($slova[0]);
}

$db->query("UPDATE `users` SET `fishrod` = `fishrod` - 1 WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
knopka('loc.php', 'Вернуться', 1);
fin();
