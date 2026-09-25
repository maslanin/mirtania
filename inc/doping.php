<?php
/**
 * Эффекты допинга.
 * PHP 8.2-совместимая версия.
 *
 * ИСПРАВЛЕН БАГ: в case 6 было $s['uvurot'] вместо $s['uvorot'].
 */

$doping = (int)($s['doping'] ?? 0);
switch ($doping) {
    case 1:
        // брага - урон + 30%
        $s['uron'] = (int)(($s['uron'] ?? 0) * 1.3);
        break;
    case 2:
        // пиво - урон + 50%
        $s['uron'] = (int)(($s['uron'] ?? 0) * 1.5);
        break;
    case 3:
        // вино - урон + 80%
        $s['uron'] = (int)(($s['uron'] ?? 0) * 1.8);
        break;
    case 4:
        // самогон - урон + 50%, крит + 20%, уворот + 20%
        $s['uron']   = (int)(($s['uron']   ?? 0) * 1.5);
        $s['krit']   = (int)(($s['krit']   ?? 0) * 1.2);
        $s['uvorot'] = (int)(($s['uvorot'] ?? 0) * 1.2);
        break;
    case 5:
        // шнапс - урон + 80%, крит + 50%, уворот + 50%
        $s['uron']   = (int)(($s['uron']   ?? 0) * 1.8);
        $s['krit']   = (int)(($s['krit']   ?? 0) * 1.5);
        $s['uvorot'] = (int)(($s['uvorot'] ?? 0) * 1.5);
        break;
    case 6:
        // ловкость - уворот + 100
        // ИСПРАВЛЕНО: было $s['uvurot'] (опечатка).
        $s['uvorot'] = (int)(($s['uvorot'] ?? 0) + 100);
        break;
    case 7:
        // реакция - крит + 100
        $s['krit'] = (int)(($s['krit'] ?? 0) + 100);
        break;
    case 8:
        // жизненная сила - ХП + 100
        $s['hpmax'] = (int)(($s['hpmax'] ?? 0) + 100);
        break;
    case 9:
        // магическая сила - МП + 100
        $s['manamax'] = (int)(($s['manamax'] ?? 0) + 100);
        break;
    case 10:
        // вышибала - урон + 50
        $s['uron'] = (int)(($s['uron'] ?? 0) + 50);
        break;
}
