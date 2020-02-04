<?php
##############
# 24.12.2014 #
##############
$hpperc = intval($f['hpnow'] * 100 / $f['hpmax']);
if($hpperc < 0) $hpperc = 0;
if($hpperc > 100) $hpperc = 100;

$manaperc = intval($f['mananow'] * 100 / $f['manamax']);
if($manaperc < 0) $manaperc = 0;
if($manaperc > 100) $manaperc = 100;

$expperc = intval($f['exp'] * 100 / $exp);
if($expperc < 0) $expperc = 0;
if($expperc > 100) $expperc = 100;

$expstr = '<div style="width:100%;height:4px;border: 0px solid #4f4f4f;border-top: 0px solid #4f4f4f; position:relative;background-color:#ffe7ba;margin: 0px 0px 0px 0px;text-align:center;">
<div style="background-color:#8b7e66;width:'.$expperc.'%;position:absolute;height:4px;">
</div></div>

<div style="width:100%;height:4px;border: 0px solid #333333;border-top: 1px solid #4f4f4f;position:relative;background-color:#ffe7ba;margin: 0px 0px 0px 0px;text-align:center;">
<div style="background-color:#ee5c42;width:'.$hpperc.'%;position:absolute;height:4px;">
</div></div>

<div style="width:100%;height:4px;border: 0px solid #333333;border-top: 1px solid #4f4f4f;position:relative;background-color:#ffe7ba;margin: 0px 0px 0px 0px;text-align:center;">
<div style="background-color:#8968cd;width:'.$manaperc.'%;position:absolute;height:4px;">
</div></div>';
echo $expstr;
?>
