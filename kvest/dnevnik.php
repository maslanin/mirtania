<?php
##############
# 24.12.2014 #
##############

echo '<div class="board2" style="text-align:left">';
$kvest = unserialize($f['kvest']);
// Ксардас
if (empty($kvest['loc56ks'])) $kvest['loc56ks']['date'] = 0;
if (empty($kvest['loc56ks']['nagrada']))
	{
	echo 'Вы взяли задание у некроманта Ксардаса. Вам нужно принести ему сердца Ледяного, Каменного и Огненного големов до полуночи.<br/>';
	if (!empty($kvest['loc56ks']['lg'])) echo 'Вы уже нашли сердце Ледяного голема.<br/>';
	if (!empty($kvest['loc56ks']['og'])) echo 'Вы уже нашли сердце Огненного голема.<br/>';
	if (!empty($kvest['loc56ks']['kg'])) echo 'Вы уже нашли сердце Каменного голема.<br/>';
	}
elseif (date('d.m.Y', $kvest['loc56ks']['date']) == date('d.m.Y'))
	{
	echo 'Задание у некроманта Ксардаса будет доступно после полуночи.<br/>';
	}
else
	{
	echo 'Задание у некроманта Ксардаса доступно прямо сейчас.<br/>';
	}
echo '</div>';
echo '<div class="board2" style="text-align:left">';
// Старик с рунами
if (empty($kvest['loc77st'])) $kvest['loc77st']['date'] = 0;
if (date('d.m.Y', $kvest['loc77st']['date']) == date('d.m.Y'))
	{
	echo 'Вы можете отнести руны старику после полуночи.<br/>';
	}
else
	{
	echo 'Вы можете отнести руны старику прямо сейчас.<br/>';
	}
echo '</div>';
echo '<div class="board2" style="text-align:left">';
// Старик с рунами
if (empty($kvest['loc69'])) $kvest['loc69']['date'] = 0;
if ($kvest['loc69']['date'] > $_SERVER['REQUEST_TIME'])
	{
	echo 'Корован будет проходить по пляжу через '.ceil(($kvest['loc69']['date'] - $_SERVER['REQUEST_TIME']) / 60).' мин.<br/>';
	}
else
	{
	echo 'Вы можете ограбить корован прямо сейчас.<br/>';
	}
echo '</div>';
echo '<div class="board2" style="text-align:left">';
// удочка
if (empty($f['fishrod']))
	{
	echo 'Вы можете получить удочку у рыбака прямо сейчас.';
	}
else
	{
	echo 'Осталось закинуть удочку: '.$f['fishrod'].' раз(а).';
	}
echo '</div>';
echo '<div class="board2" style="text-align:left">';
if(empty($kvest['loc15']['date']) or $kvest['loc15']['date'] < $_SERVER['REQUEST_TIME']) echo 'Рис можно собрать прямо сейчас<br/>';
else echo 'Рис можно будет собрать через '.ceil(($kvest['loc15']['date'] - $_SERVER['REQUEST_TIME'])/60).' минут<br/>';
if(empty($kvest['loc18']['date']) or $kvest['loc18']['date'] < $_SERVER['REQUEST_TIME']) echo 'Целебную траву можно собрать прямо сейчас<br/>';
else echo 'Целебную траву можно будет собрать через '.ceil(($kvest['loc18']['date'] - $_SERVER['REQUEST_TIME'])/60).' минут<br/>';
if(empty($kvest['loc31']['date']) or $kvest['loc31']['date'] < $_SERVER['REQUEST_TIME']) echo 'Корень маны можно собрать прямо сейчас<br/>';
else echo 'Корень маны можно будет собрать через '.ceil(($kvest['loc31']['date'] - $_SERVER['REQUEST_TIME'])/60).' минут<br/>';
if(empty($kvest['loc51']['date']) or $kvest['loc51']['date'] < $_SERVER['REQUEST_TIME']) echo 'Виноград можно собрать прямо сейчас<br/>';
else echo 'Виноград можно будет собрать через '.ceil(($kvest['loc51']['date'] - $_SERVER['REQUEST_TIME'])/60).' минут<br/>';
if(empty($kvest['loc68']['date']) or $kvest['loc68']['date'] < $_SERVER['REQUEST_TIME']) echo 'Солод можно собрать прямо сейчас<br/>';
else echo 'Солод можно будет собрать через '.ceil(($kvest['loc68']['date'] - $_SERVER['REQUEST_TIME'])/60).' минут<br/>';
if(empty($kvest['loc78']['date']) or $kvest['loc78']['date'] < $_SERVER['REQUEST_TIME']) echo 'Мед можно собрать прямо сейчас<br/>';
else echo 'Мед можно будет собрать через '.ceil(($kvest['loc78']['date'] - $_SERVER['REQUEST_TIME'])/60).' минут<br/>';
if(empty($kvest['loc83']['date']) or $kvest['loc83']['date'] < $_SERVER['REQUEST_TIME']) echo 'Хмель можно собрать прямо сейчас<br/>';
else echo 'Хмель можно будет собрать через '.ceil(($kvest['loc83']['date'] - $_SERVER['REQUEST_TIME'])/60).' минут<br/>';
echo '</div>';
fin();
?>
