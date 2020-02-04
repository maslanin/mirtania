<?php
require_once('class/DBC.php');
require_once('inc/func.php');
require_once('class/items.php');
$q = $db->query("select login from `users`;");

$count = 0;
while($a = $q->fetch_assoc())

{
	$items->add_item($a['login'], 636, 1); // 1 в конце флаг передачи, если не поставить, то игрок не сможет эту вещь передавать потом
	$count++;
}

echo 'Игрокам передано '.$count.' вещей';

?>