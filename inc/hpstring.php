<?php
/**
 * Полоски HP/MP/EXP.
 * PHP 8.2-совместимая версия.
 */

$hpmax    = (int)($f['hpmax']    ?: 1);
$manamax  = (int)($f['manamax']  ?: 1);
$exp_max  = isset($exp) && $exp > 0 ? (int)$exp : 1;

$hpperc = (int)($f['hpnow']    * 100 / $hpmax);
if ($hpperc < 0)   $hpperc = 0;
if ($hpperc > 100) $hpperc = 100;

$manaperc = (int)($f['mananow'] * 100 / $manamax);
if ($manaperc < 0)   $manaperc = 0;
if ($manaperc > 100) $manaperc = 100;

$expperc = (int)($f['exp'] * 100 / $exp_max);
if ($expperc < 0)   $expperc = 0;
if ($expperc > 100) $expperc = 100;

$expstr = '<div style="width:100%;height:4px;border:0;position:relative;background-color:#ffe7ba;margin:0;text-align:center;">
<div style="background-color:#8b7e66;width:' . $expperc . '%;position:absolute;height:4px;">
</div></div>

<div style="width:100%;height:4px;border:0;border-top:1px solid #4f4f4f;position:relative;background-color:#ffe7ba;margin:0;text-align:center;">
<div style="background-color:#ee5c42;width:' . $hpperc . '%;position:absolute;height:4px;">
</div></div>

<div style="width:100%;height:4px;border:0;border-top:1px solid #4f4f4f;position:relative;background-color:#ffe7ba;margin:0;text-align:center;">
<div style="background-color:#8968cd;width:' . $manaperc . '%;position:absolute;height:4px;">
</div></div>';

echo $expstr;
