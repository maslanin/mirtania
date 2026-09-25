<?php
/**
 * Класс работы с вещами.
 * PHP 8.2-совместимая версия.
 *
 * @package Mirtania
 */

final class items
{
    private static ?items $_instance = null;

    public static function instance(): items
    {
        if (self::$_instance === null) {
            self::$_instance = new items();
        }
        return self::$_instance;
    }

    /**
     * Информация о вещи из таблицы инвентаря (JOIN item + invent).
     */
    public function shmot(int $i): ?array
    {
        if ($i <= 0) {
            return null;
        }
        $db = DBC::instance();
        $q = $db->query("SELECT `invent`.`id` AS `inv_id`, `invent`.`ido`, `invent`.`login`, `invent`.`flag_pered`, `invent`.`flag_rinok`, `invent`.`flag_equip`, `invent`.`flag_arenda`, `invent`.`flag_sklad`, `invent`.`arenda_login`, `invent`.`arenda_time`, `invent`.`arenda_price`, `invent`.`rinok_price`, `invent`.`time`, `invent`.`up`, `item`.`id` AS `item_id`, `item`.`name`, `item`.`lvl`, `item`.`price`, `item`.`krit`, `item`.`uvorot`, `item`.`uron`, `item`.`bron`, `item`.`hp`, `item`.`zdor`, `item`.`sila`, `item`.`inta`, `item`.`lovka`, `item`.`intel`, `item`.`art`, `item`.`info`, `item`.`equip` FROM `item`, `invent` WHERE `invent`.`id` = {$i} AND `invent`.`ido` = `item`.`id` LIMIT 1;");
        if ($q === false || $q->num_rows === 0) {
            return null;
        }
        $a = $q->fetch_assoc();
        // Сохраним invent.id как 'id' — это то, что ожидает старый код.
        $a['id'] = $a['inv_id'];
        // Добавим up к названию.
        if (!empty($a['up'])) {
            $a['name'] .= ' (up ' . ($a['up'] > 0 ? '+' : '') . $a['up'] . '%)';
        }
        return $a;
    }

    /**
     * Информация о вещи из таблицы item (базовой).
     */
    public function base_shmot(int $i): ?array
    {
        if ($i <= 0) {
            return null;
        }
        $db = DBC::instance();
        $q = $db->query("SELECT * FROM `item` WHERE `id` = {$i} LIMIT 1;");
        if ($q === false || $q->num_rows === 0) {
            return null;
        }
        return $q->fetch_assoc();
    }

    /**
     * Экипировать вещь (ID из invent).
     */
    public function equip_item(string $login, int $i): bool
    {
        $l = get_login($login);
        if ($l === false) {
            return false;
        }
        $itm = $this->shmot($i);
        if ($itm === null) {
            return false;
        }
        // Снимем то, что уже надето в этот слот.
        $this->drop_equip($l['login'], $itm['equip']);

        $db = DBC::instance();
        $time = time();
        $q = $db->query("UPDATE `invent` SET `time` = '{$time}', `flag_equip` = 1 WHERE `id` = {$i} AND ((`login` = '{$l['login']}' AND `flag_arenda` = 0) OR (`arenda_login` = '{$l['login']}' AND `flag_arenda` = 1)) AND `flag_rinok` = 0 AND `flag_sklad` = 0 AND `flag_equip` = 0 LIMIT 1;");
        return $q !== false;
    }

    /**
     * Снять вещь из слота.
     */
    public function drop_equip(string $login, ?string $slot): bool
    {
        if (empty($slot)) {
            return false;
        }
        $l = get_login($login);
        if ($l === false) {
            return false;
        }
        $db = DBC::instance();
        $time = time();
        $slot_esc = $db->real_escape_string($slot);
        $q = $db->query("UPDATE `invent`, `item` SET `invent`.`flag_equip` = 0, `invent`.`time` = '{$time}' WHERE ((`invent`.`login` = '{$l['login']}' AND `invent`.`flag_arenda` = 0) OR (`invent`.`arenda_login` = '{$l['login']}' AND `invent`.`flag_arenda` = 1)) AND `invent`.`flag_equip` = 1 AND `item`.`equip` = '{$slot_esc}' AND `invent`.`ido` = `item`.`id`;");
        return $q !== false;
    }

    /**
     * Снять всю экипировку.
     */
    public function drop_equip_all(string $login): bool
    {
        $l = get_login($login);
        if ($l === false) {
            return false;
        }
        $db = DBC::instance();
        $time = time();
        $q = $db->query("UPDATE `invent` SET `flag_equip` = 0, `time` = '{$time}' WHERE ((`login` = '{$l['login']}' AND `flag_arenda` = 0) OR (`arenda_login` = '{$l['login']}' AND `flag_arenda` = 1)) AND `flag_equip` = 1;");
        return $q !== false;
    }

    /**
     * Посчитать количество вещей (ID из invent).
     */
    public function count_item(string $login, int $i, int $flag = 0): int
    {
        $l = get_login($login);
        if ($l === false) {
            return 0;
        }
        $item = $this->shmot($i);
        if ($item === null) {
            return 0;
        }
        $db = DBC::instance();
        $ido = (int)$item['ido'];
        if (empty($flag)) {
            $res = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `ido` = {$ido} AND `login` = '{$l['login']}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0;");
        } else {
            $res = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `ido` = {$ido} AND `login` = '{$l['login']}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 AND `flag_pered` = 1;");
        }
        if ($res === false) {
            return 0;
        }
        $summ = $res->fetch_assoc();
        return (int)$summ['c'];
    }

    /**
     * Посчитать количество вещей (ID из item).
     */
    public function count_base_item(string $login, int $i, int $flag = 0): int
    {
        $l = get_login($login);
        if ($l === false) {
            return 0;
        }
        $item = $this->base_shmot($i);
        if ($item === null) {
            return 0;
        }
        $db = DBC::instance();
        $ido = (int)$item['id'];
        if (empty($flag)) {
            $res = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `ido` = {$ido} AND `login` = '{$l['login']}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0;");
        } else {
            $res = $db->query("SELECT COUNT(*) AS `c` FROM `invent` WHERE `ido` = {$ido} AND `login` = '{$l['login']}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 AND `flag_pered` = 1;");
        }
        if ($res === false) {
            return 0;
        }
        $summ = $res->fetch_assoc();
        return (int)$summ['c'];
    }

    /**
     * Удалить вещь (ID из invent).
     */
    public function del_item(string $login, int $i, int $count = 1): bool
    {
        $l = get_login($login);
        if ($l === false) {
            return false;
        }
        $item = $this->shmot($i);
        if ($item === null) {
            return false;
        }
        $count = abs((int)$count);
        $have = $this->count_item($l['login'], $i);
        if ($count > $have) {
            return false;
        }
        $db = DBC::instance();
        $ido = (int)$item['ido'];
        if ($count === 1) {
            $q = $db->query("DELETE FROM `invent` WHERE `ido` = {$ido} AND `login` = '{$l['login']}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 AND `id` = {$i} LIMIT 1;");
        } else {
            $q = $db->query("DELETE FROM `invent` WHERE `ido` = {$ido} AND `login` = '{$l['login']}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 LIMIT {$count};");
        }
        return $q !== false;
    }

    /**
     * Удалить вещь (ID из item).
     */
    public function del_base_item(string $login, int $i, int $count = 1): bool
    {
        $l = get_login($login);
        if ($l === false) {
            return false;
        }
        $item = $this->base_shmot($i);
        if ($item === null) {
            return false;
        }
        $count = abs((int)$count);
        $have = $this->count_base_item($l['login'], $i);
        if ($count > $have) {
            return false;
        }
        $db = DBC::instance();
        $ido = (int)$item['id'];
        $q = $db->query("DELETE FROM `invent` WHERE `ido` = {$ido} AND `login` = '{$l['login']}' AND `flag_rinok` = 0 AND `flag_arenda` = 0 AND `flag_equip` = 0 AND `flag_sklad` = 0 LIMIT {$count};");
        return $q !== false;
    }

    /**
     * Вернуть вещи из аренды, у которых истёк срок.
     * ИСПРАВЛЕН БАГ: было $item['name'] вместо $itm['name'].
     */
    public function check_arenda(): bool
    {
        $db = DBC::instance();
        $time = time();
        $q = $db->query("SELECT `id` FROM `invent` WHERE `flag_arenda` = 1 AND `arenda_time` < '{$time}';");
        if ($q === false) {
            return false;
        }
        while ($a = $q->fetch_assoc()) {
            $itm = $this->shmot((int)$a['id']);
            if ($itm === null) {
                continue;
            }
            $log = 'Возврат аренды ' . $itm['name'] . ' (ID ' . $a['id'] . ') от ' . $itm['arenda_login'] . ' к ' . $itm['login'];
            $login_owner = $itm['login'];
            $login_arenda = $itm['arenda_login'];
            $db->query("INSERT INTO `log_peredach` VALUES (0, '{$login_owner}', '{$log}', '{$login_arenda}', '{$time}');");
            $db->query("UPDATE `invent` SET `arenda_login` = '', `time` = '{$time}', `flag_equip` = 0, `flag_arenda` = 0, `arenda_price` = 0, `arenda_time` = 0 WHERE `id` = {$a['id']} LIMIT 1;");
        }
        return true;
    }

    /**
     * Добавить вещь (ID из item).
     * @return int ID новой записи в invent
     */
    public function add_item(string $login, int $i, int $flag_pered = 0): int
    {
        $l = get_login($login);
        if ($l === false) {
            return 0;
        }
        $item = $this->base_shmot($i);
        if ($item === null) {
            return 0;
        }
        $db = DBC::instance();
        $time = time();
        $flag_pered = (int)$flag_pered;
        $ido = (int)$item['id'];
        $q = $db->query("INSERT INTO `invent` (`id`, `ido`, `login`, `flag_pered`, `time`) VALUES (0, {$ido}, '{$l['login']}', {$flag_pered}, {$time});");
        if ($q === false) {
            return 0;
        }
        return $db->insert_id();
    }
}

// Сразу же создадим экземпляр
$items = items::instance();
