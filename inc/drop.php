<?php
##############
# 24.12.2014 #
##############
if($uz['flag_bot'] == 1)
	{
	if($uz['login'] == 'Гарпия' && mt_rand(1, 100) <= 20)
		{
		$item = $items->base_shmot(168); // тотем гарпии
		if(!empty($me['klan'])) klan_points($me['klan'],1);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if($uz['login'] == 'Ползун' && mt_rand(1, 100) <= 20)
		{
		$item = $items->base_shmot(169); // сломаный брас
		if(!empty($me['klan'])) klan_points($me['klan'],1);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if($uz['login'] == 'Остер' && mt_rand(1, 100) <= 20)
		{
		$item = $items->base_shmot(162);	// руна 1
		if(!empty($me['klan'])) klan_points($me['klan'],1);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if($uz['login'] == 'Черный гоблин' && mt_rand(1, 100) <= 20)
		{
		$item = $items->base_shmot(161); // руна 2
		if(!empty($me['klan'])) klan_points($me['klan'],1);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if($uz['login'] == 'Падальщик' && mt_rand(1, 100) <= 20)
		{
		$item = $items->base_shmot(160); // руна 3
		if(!empty($me['klan'])) klan_points($me['klan'],1);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if($uz['login'] == 'Кротокрыс' && mt_rand(1, 100) <= 20)
		{
		$item = $items->base_shmot(159); // руна 4
		if(!empty($me['klan'])) klan_points($me['klan'],1);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if($uz['login'] == 'Орочий маг' && mt_rand(1, 100) <= 20)
		{
		$item = $items->base_shmot(158); // руна 5
		if(!empty($me['klan'])) klan_points($me['klan'],1);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if($uz['login'] == 'Шелкопряд' && mt_rand(1, 100) <= 40)
		{
		$item = $items->base_shmot(164);	// шелковая нить
		if(!empty($me['klan'])) klan_points($me['klan'],1);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if($uz['login'] == 'Ледяной голем')
		{
		$kvest = unserialize($f['kvest']);
		$kvest['loc56ks']['lg'] = 1;
		$f['kvest'] = serialize($kvest);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает Сердце ледяного голема</span> <br/> '.$udar_log;
		$q = $db->query("update `users` set kvest='{$f['kvest']}' where id={$f['id']} limit 1;");
		}
	if($uz['login'] == 'Огненный голем')
		{
		$kvest = unserialize($f['kvest']);
		$kvest['loc56ks']['og'] = 1;
		$f['kvest'] = serialize($kvest);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает Сердце огненного голема</span> <br/> '.$udar_log;
		$q = $db->query("update `users` set kvest='{$f['kvest']}' where id={$f['id']} limit 1;");
		}
	if($uz['login'] == 'Каменный голем')
		{
		$kvest = unserialize($f['kvest']);
		$kvest['loc56ks']['kg'] = 1;
		$f['kvest'] = serialize($kvest);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает Сердце каменного голема</span> <br/> '.$udar_log;
		$q = $db->query("update `users` set kvest='{$f['kvest']}' where id={$f['id']} limit 1;");
		}
	if($uz['login'] == 'Тролль')
		{
		$af = $q = $db->query("select login from `combat` where boi_id={$me['boi_id']} and uron_boi>999 and flag_bot=0 and komanda=2;");
		$l = array();
		while($logins = $af->fetch_assoc())
			{
			$l[] = $logins['login'];
			}
		shuffle($l);
		$winner = $l[0];
		$item = $items->base_shmot(127); // точильный камень
		$udar_log = '<span style="color:'.$female.'">'.$winner.' подбирает с распростертого тролля '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if($uz['login'] == 'Дракон')
		{
		$af = $q = $db->query("select login from `combat` where boi_id={$me['boi_id']} and uron_boi>999 and flag_bot=0 and komanda=2;");
		$l = array();
		while($logins = $af->fetch_assoc())
			{
			$l[] = $logins['login'];
			}
		shuffle($l);
		$winner = $l[0];
		$item = $items->base_shmot(157); // молния судьбы
		$udar_log = '<span style="color:'.$female.'">'.$winner.' подбирает с убитого монстра '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if(mt_rand(1, 100) == 12)
		{
		$item = $items->base_shmot(121); // свиток нападения
		if(!empty($me['klan'])) klan_points($me['klan'],1);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	if(mt_rand(1, 100) == 45)
		{
		$item = $items->base_shmot(122); // свиток развоплощения
		if(!empty($me['klan'])) klan_points($me['klan'],1);
		$udar_log = '<span style="color:'.$female.'">'.$me['login'].' выбивает '.$item['name'].'</span> <br/> '.$udar_log;
		$items->add_item($me['login'], $item['id'], 1);
		}
	}
?>
