<?php
/**
 * Клановый распорядитель.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО: в klan_log писался $f['klan'] (пустой при создании), теперь $name.
 */

if (!empty($f['klan'])) msg2('Вы уже состоите в клане!', 1);

$q = $db->query("SELECT COUNT(*) AS `c` FROM `klans`;");
$all = $q ? (int)$q->fetch_assoc()['c'] : 0;
$sum = $all - 2;
if ($sum < 0) $sum = 0;
$cena = $sum * 25000;

msg2('<b>Клановый распорядитель</b>');

if (empty($mod)) {
    msg2('О, ' . $f['login'] . ', приветствую! Я - клановый распорядитель, занимаюсь тем, что записываю сведения о новых кланах.');
    knopka('kvest.php?mod=1', 'То есть я могу создать свой клан?', 1);
    knopka('loc.php', 'Попрощаться', 1);
    fin();
} elseif ($mod == 1) {
    msg2('Ну конечно можешь! Достаточно достичь 6 уровня силы и накопить монет. Стоят мои услуги не дешево, но тебе это обойдется в ' . $cena . ' монет. Что скажешь?');
    if ($f['lvl'] >= 6) knopka('kvest.php?mod=2', 'Хочу зарегистрировать клан!', 1);
    knopka('loc.php', 'Попрощаться', 1);
    fin();
} elseif ($mod == 2) {
    if ($f['lvl'] < 6) msg2('Доступно с 6 уровня', 1);
    msg2('Отлично! Тогда введи название клана. И всё, как только моя работа будет оплачена, ты станешь главой своего нового клана! Только имей в виду, что название клана не должно нарушать законов игры, и при этом должно вписываться в игровую концепцию. Кланы, чьи названия нарушают данные требования, будут удаляться.');
    echo '<div class="board" style="text-align:left">';
    echo '<form action="kvest.php?mod=3" method="post">';
    echo 'Название (<small>не более 50 символов</small>):<br/>';
    echo '<input type="text" value="" name="name"/><br/>';
    echo '<input type="submit" value="Продолжить"/></form></div>';
    knopka('loc.php', 'Попрощаться', 1);
    fin();
} elseif ($mod == 3) {
    if ($f['lvl'] < 6) msg2('Доступно с 6 уровня', 1);
    if (empty($_REQUEST['name'])) msg2('Вы не ввели название клана!', 1);
    $name = ekr($_REQUEST['name']);
    if ($f['money'] < $cena) msg2('У вас недостаточно монет, приходите, когда накопите нужную сумму!', 1);
    if (preg_match('/[^a-zA-Zа-яА-ЯёЁ ]/u', $name) || mb_strlen($name, 'UTF-8') > 50) msg('Неверное название клана!', 1);
    $name_esc = $db->real_escape_string($name);
    $q = $db->query("SELECT * FROM `klans` WHERE `name` = '{$name_esc}' LIMIT 1;");
    if ($q && $q->num_rows > 0) msg2('Такой клан уже зарегистрирован!', 1);

    $db->query("INSERT INTO `klans` (`id`, `name`, `datereg`) VALUES (0, '{$name_esc}', '{$t}');");
    $f['money'] -= $cena;
    // ИСПРАВЛЕНО: было $f['klan'] (пустой), стало $name
    $log = $f['login'] . ' [' . $f['lvl'] . '] создает клан ' . $name . ' за ' . $cena . ' монет.';
    $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '{$name_esc}', '{$t}');");
    $db->query("UPDATE `users` SET `klan` = '{$name_esc}', `klan_status` = 3, `klan_time` = '{$t}', `money` = " . (int)$f['money'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");

    $str = 'Поздравляю тебя, ' . $f['login'] . '! Клан ' . $name . ' успешно создан!<br/>У тебя есть неделя, чтобы собрать 500 камней и построить клановый замок. Иначе, увы, запись о клане придется удалить.';
    if ($cena > 0) $str .= '<br/>[Потеряно ' . $cena . ' монет]';
    msg2($str);
    knopka('loc.php', 'Попрощаться', 1);
    fin();
}
