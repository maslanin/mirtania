<?php
##############
# 29.07.2014 #
##############
$day = 60*60*20; // раз в 20 часов
//$bonus_t = $f['bonus_time'] + $day;
if($f['bonus_time'] + $day < $t and $f['autoreg'] == 0)
	{
	if(($f['bonus_day'] > 0 and $f['bonus_time'] + $day * 2 < $t) or ($f['bonus_day'] >= 10)) $f['bonus_day'] = 0;
	$f['bonus_day'] ++;
	$rand = 5 * $f['lvl'] * $f['bonus_day'];
	$nagr = 0;
	if($f['bonus_day'] == 5) $nagr = mt_rand(1,3);
	if($f['bonus_day'] == 10) $nagr = mt_rand(3,5);
	$str = '<img src="pic/bonus.png"> Вам начислен ежедневный бонус: '.$rand.' монет';
	if(!empty($nagr)) $str .= ' и '.$nagr.' руды';
	$str .= ' за '.$f['bonus_day'].'-й день</div>';
	msg2($str);
	$f['money'] += $rand;
	$f['ruda'] += $nagr;
	$q = $db->query("update `users` set money={$f['money']},ruda={$f['ruda']},bonus_time=UNIX_TIMESTAMP(),bonus_day={$f['bonus_day']} WHERE id={$f['id']} LIMIT 1;");
	}
?>
