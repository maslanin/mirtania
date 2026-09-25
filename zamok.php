<?php
/**
 * Клановый замок: постройки, крафт.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕНО:
 *  - в логе uporuzh было $klan['altar'] вместо $klan['oruzh']
 *  - проверки fetch_assoc
 */

require_once __DIR__ . '/inc/top.php';
require_once __DIR__ . '/inc/check.php';
require_once __DIR__ . '/inc/head.php';
require_once __DIR__ . '/class/items.php';
require_once __DIR__ . '/inc/hpstring.php';

$q = $db->query("SELECT * FROM `klans` WHERE `loc` = " . (int)$f['loc'] . " OR `point` = " . (int)$f['loc'] . " LIMIT 1;");
if (!$q || $q->num_rows == 0) {
    knopka('loc.php', 'Замка здесь нет', 1);
    fin();
}
$klan = $q->fetch_assoc();

$mod   = isset($_REQUEST['mod'])   ? $_REQUEST['mod'] : '';
$go    = isset($_REQUEST['go'])    ? (int)$_REQUEST['go'] : 0;
$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$iid   = isset($_REQUEST['iid'])   ? (int)$_REQUEST['iid'] : 0;

// --- Недостроенный замок ---
if (!empty($klan['point']) && empty($klan['loc'])) {
    msg2('Недостроенный замок ' . $klan['name']);
    switch ($mod) {
        default:
            if ($f['klan'] != $klan['name']) msg2('Этот замок необходимо отстроить, а пока лишь ветер гуляет среди руин...', 1);
            msg2('У вас ' . $klan['kamni'] . ' камней.');
            knopka('zamok.php?mod=build', 'Отстроить замок (100 камней)', 1);
            if (3 <= $f['klan_status']) knopka('zamok.php?mod=drop', 'Отказаться от замка', 1);
            fin();

        case 'drop':
            if ($f['klan_status'] < 3) msg('Недоступно для вас', 1);
            if (empty($go)) {
                msg2('Вы уверены, что хотите освободить это место?');
                knopka('zamok.php?mod=drop&go=1', 'Отказаться от места', 1);
                knopka('loc.php', 'В игру', 1);
                fin();
            }
            $log = $f['login'] . ' [' . $f['lvl'] . '] освобождает занятое для замка место.';
            $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($f['klan']) . "', '{$t}');");
            $db->query("UPDATE `klans` SET `point` = 0 WHERE `name` = '" . $db->real_escape_string($f['klan']) . "' LIMIT 1;");
            msg2('Вы успешно отказались от места, теперь здесь может строиться любой клан.', 1);
            break;

        case 'build':
            if ($klan['kamni'] < 100) msg('Недостаточно камней.', 1);
            if (empty($go)) {
                msg2('Вы уверены, что хотите построить замок?');
                knopka('zamok.php?mod=build&go=1', 'Построить замок', 1);
                knopka('loc.php', 'В игру', 1);
                fin();
            }
            $log = $f['login'] . ' [' . $f['lvl'] . '] строит замок для клана из 100 камней.';
            $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($f['klan']) . "', '{$t}');");
            $db->query("UPDATE `klans` SET `point` = 0, `loc` = " . (int)$f['loc'] . ", `kamni` = `kamni` - 100 WHERE `name` = '" . $db->real_escape_string($f['klan']) . "' LIMIT 1;");
            msg2('Вы успешно построили замок.');
            knopka('zamok.php', 'В замок', 1);
            knopka('loc.php', 'В игру', 1);
            fin();
    }
    fin();
}

// --- Готовый замок ---
msg2('Замок клана ' . $klan['name']);

switch ($mod) {
    default:
        knopka('zamok.php?mod=info', 'Информация', 1);
        if ($klan['name'] == $f['klan'] && !empty($klan['altar'])) knopka('zamok.php?mod=altar', 'Алтарь', 1);
        if ($klan['name'] == $f['klan'] && !empty($klan['pivo'])) knopka('zamok.php?mod=pivo', 'Пивоварня', 1);
        if ($klan['name'] == $f['klan'] && !empty($klan['laba'])) knopka('zamok.php?mod=laba', 'Лаборатория', 1);
        if ($klan['name'] == $f['klan'] && !empty($klan['kuznica'])) knopka('zamok.php?mod=kuznica', 'Кузница', 1);
        if ($klan['name'] == $f['klan'] && !empty($klan['oruzh'])) knopka('zamok.php?mod=oruzh', 'Оружейная мастерская', 1);
        if ($klan['name'] == $f['klan'] && 2 <= $f['klan_status']) knopka('zamok.php?mod=buildings', 'Управление постройками', 1);
        break;

    case 'info':
        echo '<div class="board"">';
        echo 'Сводка';
        echo '</div>';
        echo '<div class="board2" style="text-align:left">Казна: ' . $klan['kazna'] . '</div>';
        echo '<div class="board2" style="text-align:left">Камни: ' . $klan['kamni'] . '</div>';
        if ($klan['altar'] > 0) echo '<div class="board2" style="text-align:left">Уровень алтаря: ' . $klan['altar'] . '</div>';
        if ($klan['pivo'] > 0) echo '<div class="board2" style="text-align:left">Уровень пивоварни: ' . $klan['pivo'] . '</div>';
        if ($klan['laba'] > 0) echo '<div class="board2" style="text-align:left">Уровень лаборатории: ' . $klan['laba'] . '</div>';
        if ($klan['kuznica'] > 0) echo '<div class="board2" style="text-align:left">Уровень кузницы: ' . $klan['kuznica'] . '</div>';
        if ($klan['oruzh'] > 0) echo '<div class="board2" style="text-align:left">Уровень оружейной мастерской: ' . $klan['oruzh'] . '</div>';
        if ($klan['name'] == $f['klan']) knopka('zamok.php?mod=log', 'Дворовая книга', 1);
        break;

    case 'buildings':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if ($f['klan_status'] < 2) msg('Вам нечего тут делать, доступно главам и наместникам!', 1);
        if ($klan['altar'] == 0) $str = 'Построить алтарь'; else $str = 'Улучшить алтарь';
        if ($klan['altar'] < 5) knopka('zamok.php?mod=upaltar', $str);
        if ($klan['pivo'] == 0) $str = 'Построить пивоварню'; else $str = 'Улучшить пивоварню';
        if ($klan['pivo'] < 5) knopka('zamok.php?mod=uppivo', $str);
        if ($klan['laba'] == 0) $str = 'Построить лабораторию'; else $str = 'Улучшить лабораторию';
        if ($klan['laba'] < 5) knopka('zamok.php?mod=uplaba', $str);
        if ($klan['kuznica'] == 0) $str = 'Построить кузницу'; else $str = 'Улучшить кузницу';
        if ($klan['kuznica'] < 5) knopka('zamok.php?mod=upkuznica', $str);
        if ($klan['oruzh'] == 0) $str = 'Построить оружейную мастерскую'; else $str = 'Улучшить оружейную мастерскую';
        if ($klan['oruzh'] < 3) knopka('zamok.php?mod=uporuzh', $str);
        break;

    case 'altar':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if (empty($klan['altar'])) msg2('В замке нет алтаря!', 1);
        msg('Небольшой постамент, на котором расположен жертвенный камень. От него ощутимо веет мощью.');
        $timer = $t + 86400 * 7;
        $db->query("UPDATE `users` SET `altar` = " . (int)$klan['altar'] . ", `altar_time` = '{$timer}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        $f = calcparam($f);
        msg2('Боги довольны вами, усиление +' . $klan['altar'] . '% на 7 суток');
        break;

    case 'upaltar':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if ($f['klan_status'] < 2) msg2('Вы не можете строить в замке.', 1);
        if (empty($klan['altar'])) { $money = 500; $kamni = 100; }
        elseif ($klan['altar'] == 1) { $money = 1000; $kamni = 200; }
        elseif ($klan['altar'] == 2) { $money = 2000; $kamni = 300; }
        elseif ($klan['altar'] == 3) { $money = 5000; $kamni = 400; }
        elseif ($klan['altar'] == 4) { $money = 10000; $kamni = 500; }
        else msg2('У вас алтарь максимального 5 уровня.', 1);

        if (empty($go)) {
            msg2('Вы действительно хотите построить или улучшить алтарь за ' . $money . ' монет и ' . $kamni . ' камней?');
            knopka('zamok.php?mod=upaltar&go=1', 'Продолжаем!', 1);
            knopka('zamok.php', 'Отказаться', 1);
            fin();
        }
        if ($klan['kazna'] < $money) msg('В казне недостаточно денег для постройки алтаря, нужно ' . $money, 1);
        if ($klan['kamni'] < $kamni) msg('У клана недостаточно камней для постройки алтаря, нужно ' . $kamni, 1);

        $new_altar = (int)$klan['altar'] + 1;
        $new_kazna = (int)$klan['kazna'] - $money;
        $new_kamni = (int)$klan['kamni'] - $kamni;
        if ($klan['altar'] == 0) $log = $f['login'] . ' [' . $f['lvl'] . '] строит алтарь клана за ' . $kamni . ' камней и ' . $money . ' монет.';
        else $log = $f['login'] . ' [' . $f['lvl'] . '] улучшает алтарь клана до ' . $new_altar . ' уровня за ' . $kamni . ' камней и ' . $money . ' монет.';
        $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($f['klan']) . "', '{$t}');");
        $db->query("UPDATE `klans` SET `altar` = {$new_altar}, `kazna` = {$new_kazna}, `kamni` = {$new_kamni} WHERE `id` = " . (int)$klan['id'] . " LIMIT 1;");
        if ($klan['altar'] == 0) msg2('Вы построили алтарь!');
        else msg2('Вы улучшили алтарь до ' . $new_altar . ' уровня!');
        knopka('zamok.php', 'В замок', 1);
        break;

    case 'pivo':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if (empty($klan['pivo'])) msg2('В замке нет пивоварни!', 1);
        if (empty($go)) {
            if (1 <= $klan['pivo']) knopka('zamok.php?mod=pivo&go=1', 'Брага (нужен хмель)', 1);
            if (2 <= $klan['pivo']) knopka('zamok.php?mod=pivo&go=2', 'Пиво (нужен солод)', 1);
            if (3 <= $klan['pivo']) knopka('zamok.php?mod=pivo&go=3', 'Вино (нужен виноград)', 1);
            if (4 <= $klan['pivo']) knopka('zamok.php?mod=pivo&go=4', 'Самогон (нужен мёд)', 1);
            if (5 <= $klan['pivo']) knopka('zamok.php?mod=pivo&go=5', 'Рисовый шнапс (нужен рис)', 1);
            fin();
        }

        $recipes = [
            1 => ['need' => 640, 'res' => 635, 'lvl' => 1, 'msg' => 'Вы приготовили брагу.'],
            2 => ['need' => 641, 'res' => 636, 'lvl' => 2, 'msg' => 'Вы приготовили пиво.'],
            3 => ['need' => 642, 'res' => 637, 'lvl' => 3, 'msg' => 'Вы приготовили вино.'],
            4 => ['need' => 643, 'res' => 638, 'lvl' => 4, 'msg' => 'Вы приготовили самогон.'],
            5 => ['need' => 644, 'res' => 639, 'lvl' => 5, 'msg' => 'Вы приготовили рисовый шнапс.'],
        ];

        if (!isset($recipes[$go])) {
            $m = $f['lvl'] * 1000;
            $db->query("UPDATE `users` SET `money` = `money` - '{$m}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
            msg2('Подменять ссылки нехорошо. С вас списан штраф ' . $m . ' монет за баловство.');
            break;
        }

        $r = $recipes[$go];
        if ($klan['pivo'] < $r['lvl']) msg('Необходима пивоварня минимум ' . $r['lvl'] . ' уровня!', 1);
        $need = $items->count_base_item($f['login'], $r['need']);
        if ($need == 0) {
            $item = $items->base_shmot($r['need']);
            msg2('У вас нет ' . ($item['name'] ?? 'ингредиента') . '!', 1);
        }
        $items->del_base_item($f['login'], $r['need'], 1);
        $items->add_item($f['login'], $r['res'], 1);
        msg2($r['msg']);
        break;

    case 'uppivo':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if ($f['klan_status'] < 2) msg2('Вы не можете строить в замке.', 1);
        if (empty($klan['pivo'])) { $money = 500; $kamni = 100; }
        elseif ($klan['pivo'] == 1) { $money = 1000; $kamni = 250; }
        elseif ($klan['pivo'] == 2) { $money = 2000; $kamni = 500; }
        elseif ($klan['pivo'] == 3) { $money = 5000; $kamni = 750; }
        elseif ($klan['pivo'] == 4) { $money = 10000; $kamni = 1000; }
        else msg2('У вас пивоварня максимального 5 уровня.', 1);

        if (empty($go)) {
            msg2('Вы действительно хотите построить или улучшить пивоварню за ' . $money . ' монет и ' . $kamni . ' камней?');
            knopka('zamok.php?mod=uppivo&go=1', 'Продолжаем!', 1);
            knopka('zamok.php', 'Отказаться', 1);
            fin();
        }
        if ($klan['kazna'] < $money) msg('В казне недостаточно денег для постройки или улучшения пивоварни, нужно ' . $money, 1);
        if ($klan['kamni'] < $kamni) msg('У клана недостаточно камней для постройки или улучшения пивоварни, нужно ' . $kamni, 1);

        $new_pivo = (int)$klan['pivo'] + 1;
        $new_kazna = (int)$klan['kazna'] - $money;
        $new_kamni = (int)$klan['kamni'] - $kamni;
        if ($klan['pivo'] == 0) $log = $f['login'] . ' [' . $f['lvl'] . '] строит пивоварню клана за ' . $kamni . ' камней и ' . $money . ' монет.';
        else $log = $f['login'] . ' [' . $f['lvl'] . '] улучшает пивоварню клана до ' . $new_pivo . ' уровня за ' . $kamni . ' камней и ' . $money . ' монет.';
        $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($f['klan']) . "', '{$t}');");
        $db->query("UPDATE `klans` SET `pivo` = {$new_pivo}, `kazna` = {$new_kazna}, `kamni` = {$new_kamni} WHERE `id` = " . (int)$klan['id'] . " LIMIT 1;");
        if ($klan['pivo'] == 0) msg2('Вы построили пивоварню!');
        else msg2('Вы улучшили пивоварню до ' . $new_pivo . ' уровня!');
        knopka('zamok.php', 'В замок', 1);
        break;

    case 'laba':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if (empty($klan['laba'])) msg2('В замке нет лаборатории!', 1);
        if (empty($go)) {
            msg('Для приготовления напитков лечения необходима целебная трава');
            if (1 <= $klan['laba']) knopka('zamok.php?mod=laba&go=1', 'Великая эссенця исцеления (+350HP)', 1);
            if (2 <= $klan['laba']) knopka('zamok.php?mod=laba&go=2', 'Великая вытяжка исцеления (+500HP)', 1);
            if (3 <= $klan['laba']) knopka('zamok.php?mod=laba&go=3', 'Великий эликсир лечения (+750HP)', 1);
            if (4 <= $klan['laba']) knopka('zamok.php?mod=laba&go=4', 'Великий напиток лечения (+1000HP)', 1);
            if (5 <= $klan['laba']) knopka('zamok.php?mod=laba&go=5', 'Лечебный экстракт (+1500HP)', 1);
            msg('Для приготовления напитков маны необходим корень маны');
            if (1 <= $klan['laba']) knopka('zamok.php?mod=laba&go=6', 'Великая эссенция мудрости (+350MP)', 1);
            if (2 <= $klan['laba']) knopka('zamok.php?mod=laba&go=7', 'Великая вытяжка мудрости (+500MP)', 1);
            if (3 <= $klan['laba']) knopka('zamok.php?mod=laba&go=8', 'Великий эликсир мудрости (+750MP)', 1);
            if (4 <= $klan['laba']) knopka('zamok.php?mod=laba&go=9', 'Великий напиток мудрости (+1000MP)', 1);
            if (5 <= $klan['laba']) knopka('zamok.php?mod=laba&go=10', 'Экстракт мудрости (+1500MP)', 1);
            fin();
        }

        $recipes = [
            1  => ['need' => 705, 'res' => 625, 'lvl' => 1],
            2  => ['need' => 705, 'res' => 626, 'lvl' => 2],
            3  => ['need' => 705, 'res' => 627, 'lvl' => 3],
            4  => ['need' => 705, 'res' => 628, 'lvl' => 4],
            5  => ['need' => 705, 'res' => 629, 'lvl' => 5],
            6  => ['need' => 706, 'res' => 630, 'lvl' => 1],
            7  => ['need' => 706, 'res' => 631, 'lvl' => 2],
            8  => ['need' => 706, 'res' => 632, 'lvl' => 3],
            9  => ['need' => 706, 'res' => 633, 'lvl' => 4],
            10 => ['need' => 706, 'res' => 634, 'lvl' => 5],
        ];

        if (!isset($recipes[$go])) {
            $m = $f['lvl'] * 1000;
            $db->query("UPDATE `users` SET `money` = `money` - '{$m}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
            msg2('Подменять ссылки нехорошо. С вас списан штраф ' . $m . ' монет за баловство.');
            break;
        }

        $r = $recipes[$go];
        if ($klan['laba'] < $r['lvl']) msg('Необходима лаборатория минимум ' . $r['lvl'] . ' уровня!', 1);
        $need = $items->count_base_item($f['login'], $r['need']);
        if ($need == 0) {
            $item = $items->base_shmot($r['need']);
            msg2('У вас нет ' . ($item['name'] ?? 'ингредиента') . '!', 1);
        }
        $items->del_base_item($f['login'], $r['need'], 1);
        $items->add_item($f['login'], $r['res'], 1);
        msg2('Вы приготовили зелье.');
        break;

    case 'uplaba':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if ($f['klan_status'] < 2) msg2('Вы не можете строить в замке.', 1);
        if (empty($klan['laba'])) { $money = 500; $kamni = 100; }
        elseif ($klan['laba'] == 1) { $money = 1000; $kamni = 250; }
        elseif ($klan['laba'] == 2) { $money = 2000; $kamni = 500; }
        elseif ($klan['laba'] == 3) { $money = 5000; $kamni = 750; }
        elseif ($klan['laba'] == 4) { $money = 10000; $kamni = 1000; }
        else msg2('У вас лаборатория максимального 5 уровня.', 1);

        if (empty($go)) {
            msg2('Вы действительно хотите построить или улучшить лабораторию за ' . $money . ' монет и ' . $kamni . ' камней?');
            knopka('zamok.php?mod=uplaba&go=1', 'Продолжаем!', 1);
            knopka('zamok.php', 'Отказаться', 1);
            fin();
        }
        if ($klan['kazna'] < $money) msg('В казне недостаточно денег для постройки или улучшения лаборатории, нужно ' . $money, 1);
        if ($klan['kamni'] < $kamni) msg('У клана недостаточно камней для постройки или улучшения лаборатории, нужно ' . $kamni, 1);

        $new_laba = (int)$klan['laba'] + 1;
        $new_kazna = (int)$klan['kazna'] - $money;
        $new_kamni = (int)$klan['kamni'] - $kamni;
        if ($klan['laba'] == 0) $log = $f['login'] . ' [' . $f['lvl'] . '] строит лабораторию клана за ' . $kamni . ' камней и ' . $money . ' монет.';
        else $log = $f['login'] . ' [' . $f['lvl'] . '] улучшает лабораторию клана до ' . $new_laba . ' уровня за ' . $kamni . ' камней и ' . $money . ' монет.';
        $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($f['klan']) . "', '{$t}');");
        $db->query("UPDATE `klans` SET `laba` = {$new_laba}, `kazna` = {$new_kazna}, `kamni` = {$new_kamni} WHERE `id` = " . (int)$klan['id'] . " LIMIT 1;");
        if ($klan['laba'] == 0) msg2('Вы построили лабораторию!');
        else msg2('Вы улучшили лабораторию до ' . $new_laba . ' уровня!');
        knopka('zamok.php', 'В замок', 1);
        break;

    case 'kuznica':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if (empty($klan['kuznica'])) msg2('В замке нет кузницы!', 1);

        $q = $db->query("SELECT MAX(`lvl`) AS `m` FROM `item`;");
        $max_lvl = $q ? (int)$q->fetch_assoc()['m'] : 1;
        if ($klan['kuznica'] == 1) $max_lvl = 9;
        if ($klan['kuznica'] == 2) $max_lvl = 13;
        if ($klan['kuznica'] == 3) $max_lvl = 17;
        if ($klan['kuznica'] == 4) $max_lvl = 21;

        if (empty($start)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="zamok.php?mod=kuznica" method="POST">
            На какой уровень желаете собрать вещи?<br/>
            <select name="start">';
            for ($i = 6; $i <= $max_lvl; $i++) echo '<option value=' . $i . '>' . $i . '</option>';
            echo '</select>';
            echo '<input type="submit" value="Далее"/></form>';
            fin();
        }
        if ($start < 6) $start = 6;
        if ($start > $max_lvl) $start = $max_lvl;

        if (empty($iid)) {
            $it1 = $items->base_shmot(168);
            $it2 = $items->base_shmot(169);
            $c = $start * 100;
            msg2('Вы хотите собрать вещи на ' . $start . ' уровень. Цена ' . $c . ' монет');
            knopka('zamok.php?mod=kuznica&start=' . $start . '&iid=1', 'Амулет (с) (необходимо ' . $it1['name'] . ')', 1);
            knopka('zamok.php?mod=kuznica&start=' . $start . '&iid=2', 'Амулет (к) (необходимо ' . $it1['name'] . ')', 1);
            knopka('zamok.php?mod=kuznica&start=' . $start . '&iid=3', 'Амулет (у) (необходимо ' . $it1['name'] . ')', 1);
            knopka('zamok.php?mod=kuznica&start=' . $start . '&iid=4', 'Браслет (с) (необходимо ' . $it2['name'] . ')', 1);
            knopka('zamok.php?mod=kuznica&start=' . $start . '&iid=5', 'Браслет (к) (необходимо ' . $it2['name'] . ')', 1);
            knopka('zamok.php?mod=kuznica&start=' . $start . '&iid=6', 'Браслет (у) (необходимо ' . $it2['name'] . ')', 1);
            fin();
        }

        if ($iid == 1) $num = $start * 3 - 17;
        elseif ($iid == 2) $num = $start * 3 - 16;
        elseif ($iid == 3) $num = $start * 3 - 15;
        elseif ($iid == 4) $num = $start * 3 + 43;
        elseif ($iid == 5) $num = $start * 3 + 44;
        elseif ($iid == 6) $num = $start * 3 + 45;
        else {
            $m = $f['lvl'] * 1000;
            $db->query("UPDATE `users` SET `money` = `money` - '{$m}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
            msg2('Подменять ссылки нехорошо. С вас списан штраф ' . $m . ' монет за баловство.', 1);
        }

        if ($f['money'] < ($start * 100)) msg2('У вас не хватает денег!', 1);
        $item = $items->base_shmot($num);
        if (!$item) msg2('Такой вещи нет!', 1);
        $zzz = ($item['equip'] == 'braslet') ? 169 : 168;
        $itz = $items->base_shmot($zzz);
        if ($items->count_base_item($f['login'], $zzz) == 0) msg2('У вас нет ' . $itz['name'], 1);

        if (empty($go)) {
            msg2('Вы уверены, что хотите собрать ' . $item['name'] . '?');
            knopka('zamok.php?mod=kuznica&start=' . $start . '&iid=' . $iid . '&go=1', 'Собрать', 1);
            knopka('loc.php', 'В игру', 1);
            fin();
        }
        $items->del_base_item($f['login'], $zzz, 1);
        $items->add_item($f['login'], $num, 1);
        $f['money'] -= ($start * 100);
        $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        msg2('Вы перековали ' . $itz['name'] . ' в ' . $item['name'] . '!');
        break;

    case 'upkuznica':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if ($f['klan_status'] < 2) msg2('Вы не можете строить в замке.', 1);
        if (empty($klan['kuznica'])) { $money = 500; $kamni = 100; }
        elseif ($klan['kuznica'] == 1) { $money = 1000; $kamni = 250; }
        elseif ($klan['kuznica'] == 2) { $money = 2000; $kamni = 500; }
        elseif ($klan['kuznica'] == 3) { $money = 5000; $kamni = 750; }
        elseif ($klan['kuznica'] == 4) { $money = 10000; $kamni = 1000; }
        else msg2('У вас кузница максимального 5 уровня.', 1);

        if (empty($go)) {
            msg2('Вы действительно хотите построить или улучшить кузницу за ' . $money . ' монет и ' . $kamni . ' камней?');
            knopka('zamok.php?mod=upkuznica&go=1', 'Продолжаем!', 1);
            knopka('zamok.php', 'Отказаться', 1);
            fin();
        }
        if ($klan['kazna'] < $money) msg('В казне недостаточно денег для постройки или улучшения кузницы, нужно ' . $money, 1);
        if ($klan['kamni'] < $kamni) msg('У клана недостаточно камней для постройки или улучшения кузницы, нужно ' . $kamni, 1);

        $new_kuznica = (int)$klan['kuznica'] + 1;
        $new_kazna = (int)$klan['kazna'] - $money;
        $new_kamni = (int)$klan['kamni'] - $kamni;
        if ($klan['kuznica'] == 0) $log = $f['login'] . ' [' . $f['lvl'] . '] строит кузницу клана за ' . $kamni . ' камней и ' . $money . ' монет.';
        else $log = $f['login'] . ' [' . $f['lvl'] . '] улучшает кузницу клана до ' . $new_kuznica . ' уровня за ' . $kamni . ' камней и ' . $money . ' монет.';
        $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($f['klan']) . "', '{$t}');");
        $db->query("UPDATE `klans` SET `kuznica` = {$new_kuznica}, `kazna` = {$new_kazna}, `kamni` = {$new_kamni} WHERE `id` = " . (int)$klan['id'] . " LIMIT 1;");
        if ($klan['kuznica'] == 0) msg2('Вы построили кузницу!');
        else msg2('Вы улучшили кузницу до ' . $new_kuznica . ' уровня!');
        knopka('zamok.php', 'В замок', 1);
        break;

    case 'oruzh':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if (empty($klan['oruzh'])) msg2('В замке нет оружейной мастерской!', 1);

        $q = $db->query("SELECT MAX(`lvl`) AS `m` FROM `item`;");
        $max_lvl = $q ? (int)$q->fetch_assoc()['m'] : 1;

        if (empty($start)) {
            echo '<div class="board" style="text-align:left">';
            echo '<form action="zamok.php?mod=oruzh" method="POST">
            На какой уровень желаете изготовить вещи?<br/>
            <select name="start">';
            for ($i = 6; $i <= $max_lvl; $i++) echo '<option value=' . $i . '>' . $i . '</option>';
            echo '</select>';
            echo '<input type="submit" value="Далее"/></form>';
            fin();
        }
        if ($start < 6) $start = 6;
        if ($start > $max_lvl) $start = $max_lvl;

        if (empty($iid)) {
            msg2('Вы хотите собрать вещи на ' . $start . ' уровень.');
            if (1 <= $klan['oruzh']) {
                $idd = $start + 639;
                $item = $items->base_shmot($idd);
                if ($item) {
                    echo '<div class="board2" style="text-align:left">';
                    echo '<a href="zamok.php?mod=oruzh&start=' . $start . '&iid=1">' . $item['name'] . '</a> (' . $item['price'] . ' монет)';
                    echo ' <a href="shop.php?mod=iteminfa&iid=' . $idd . '">[infa]</a>';
                    echo '</div>';
                }
            }
            if (2 <= $klan['oruzh']) {
                $idd = $start + 659;
                $item = $items->base_shmot($idd);
                if ($item) {
                    echo '<div class="board2" style="text-align:left">';
                    echo '<a href="zamok.php?mod=oruzh&start=' . $start . '&iid=2">' . $item['name'] . '</a> (' . $item['price'] . ' монет)';
                    echo ' <a href="shop.php?mod=iteminfa&iid=' . $idd . '">[infa]</a>';
                    echo '</div>';
                }
            }
            if (3 <= $klan['oruzh']) {
                $idd = $start + 679;
                $item = $items->base_shmot($idd);
                if ($item) {
                    echo '<div class="board2" style="text-align:left">';
                    echo '<a href="zamok.php?mod=oruzh&start=' . $start . '&iid=3">' . $item['name'] . '</a> (' . $item['price'] . ' монет)';
                    echo ' <a href="shop.php?mod=iteminfa&iid=' . $idd . '">[infa]</a>';
                    echo '</div>';
                }
            }
            fin();
        }

        if ($iid == 1) $num = $start + 639;
        elseif ($iid == 2) $num = $start + 659;
        elseif ($iid == 3) $num = $start + 679;
        else {
            $m = $f['lvl'] * 1000;
            $db->query("UPDATE `users` SET `money` = `money` - '{$m}' WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
            msg2('Подменять ссылки нехорошо. С вас списан штраф ' . $m . ' монет за баловство.', 1);
        }

        $item = $items->base_shmot($num);
        if (!$item) msg2('Такой вещи нет!', 1);
        if ($f['money'] < $item['price']) msg2('У вас не хватает денег!', 1);

        if (empty($go)) {
            msg2('Вы уверены, что хотите изготовить ' . $item['name'] . '?');
            knopka('zamok.php?mod=oruzh&start=' . $start . '&iid=' . $iid . '&go=1', 'Изготовить', 1);
            knopka('loc.php', 'В игру', 1);
            fin();
        }
        $items->add_item($f['login'], $num, 1);
        $f['money'] -= $item['price'];
        $db->query("UPDATE `klans` SET `kazna` = `kazna` + " . (int)$item['price'] . " WHERE `id` = " . (int)$klan['id'] . " LIMIT 1;");
        $db->query("UPDATE `users` SET `money` = " . (int)$f['money'] . " WHERE `id` = " . (int)$f['id'] . " LIMIT 1;");
        $log = $f['login'] . ' [' . $f['lvl'] . '] изготавливает ' . $item['name'] . ' за ' . $item['price'] . ' монет. Казна пополнена.';
        $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($f['klan']) . "', '{$t}');");
        msg2('Вы изготовили ' . $item['name'] . '!');
        break;

    case 'uporuzh':
        if ($f['klan'] != $klan['name']) msg2('Это не ваш замок!', 1);
        if ($f['klan_status'] < 2) msg2('Вы не можете строить в замке.', 1);
        if (empty($klan['oruzh'])) { $money = 500; $kamni = 100; }
        elseif ($klan['oruzh'] == 1) { $money = 5000; $kamni = 500; }
        elseif ($klan['oruzh'] == 2) { $money = 10000; $kamni = 1000; }
        else msg2('У вас оружейная мастерская максимального 3 уровня.', 1);

        if (empty($go)) {
            msg2('Вы действительно хотите построить или улучшить оружейную мастерскую за ' . $money . ' монет и ' . $kamni . ' камней?');
            knopka('zamok.php?mod=uporuzh&go=1', 'Продолжаем!', 1);
            knopka('zamok.php', 'Отказаться', 1);
            fin();
        }
        if ($klan['kazna'] < $money) msg('В казне недостаточно денег для постройки или улучшения оружейной мастерской, нужно ' . $money, 1);
        if ($klan['kamni'] < $kamni) msg('У клана недостаточно камней для постройки или улучшения оружейной мастерской, нужно ' . $kamni, 1);

        $new_oruzh = (int)$klan['oruzh'] + 1;
        $new_kazna = (int)$klan['kazna'] - $money;
        $new_kamni = (int)$klan['kamni'] - $kamni;
        if ($klan['oruzh'] == 0) $log = $f['login'] . ' [' . $f['lvl'] . '] строит оружейную мастерскую клана за ' . $kamni . ' камней и ' . $money . ' монет.';
        else $log = $f['login'] . ' [' . $f['lvl'] . '] улучшает оружейную мастерскую клана до ' . $new_oruzh . ' уровня за ' . $kamni . ' камней и ' . $money . ' монет.';
        $db->query("INSERT INTO `klan_log` VALUES (0, '" . $db->real_escape_string($f['login']) . "', '" . $db->real_escape_string($log) . "', '" . $db->real_escape_string($f['klan']) . "', '{$t}');");
        $db->query("UPDATE `klans` SET `oruzh` = {$new_oruzh}, `kazna` = {$new_kazna}, `kamni` = {$new_kamni} WHERE `id` = " . (int)$klan['id'] . " LIMIT 1;");
        if ($klan['oruzh'] == 0) msg2('Вы построили оружейную мастерскую!');
        else msg2('Вы улучшили оружейную мастерскую до ' . $new_oruzh . ' уровня!');
        knopka('zamok.php', 'В замок', 1);
        break;

    case 'log':
        if ($klan['name'] != $f['klan']) msg2('Смотреть дворовую книгу могут только члены клана ' . $klan['name'], 1);
        $numb = 50;
        $count = 0;
        $klan_esc = $db->real_escape_string($f['klan']);
        $q = $db->query("SELECT COUNT(*) AS `c` FROM `klan_log` WHERE `klan` = '{$klan_esc}';");
        $all_log = $q ? (int)$q->fetch_assoc()['c'] : 0;
        if ($start > (int)($all_log / $numb)) $start = (int)($all_log / $numb);
        if ($start < 0) $start = 0;
        $limit = $start * $numb;
        $count = $limit;
        $q = $db->query("SELECT * FROM `klan_log` WHERE `klan` = '{$klan_esc}' ORDER BY `id` DESC LIMIT {$limit}, {$numb};");
        if ($q) {
            while ($log = $q->fetch_assoc()) {
                $count++;
                echo '<div class="board2" style="text-align:left">';
                echo $count . '. ' . date('d.m.Y H:i', (int)$log['date']) . ' - ' . $log['log'];
                echo '</div>';
            }
        }
        if ($all_log > $numb) {
            echo '<div class="board">';
            if ($start > 0) echo '<a href="zamok.php?mod=log&start=' . ($start - 1) . '" class="navig"><-Назад</a>'; else echo '<a href="#" class="navig"> <-Назад</a>';
            echo ' | ';
            if ($limit + $numb < $all_log) echo '<a href="zamok.php?mod=log&start=' . ($start + 1) . '" class="navig" >Вперед-></a>'; else echo ' <a href="#" class="navig"> Вперед-></a>';
            echo '</div>';
        }
        fin();
        break;
}

if (!empty($mod)) knopka('zamok.php', 'Вернуться', 1);
fin();
