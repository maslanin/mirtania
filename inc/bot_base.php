<?php
##############
# 24.12.2014 #
##############

//делаю пока сюда, для совместимости со старыми версиями. Потом всех ботов надо будет загнать в базу
//и создать им всем статические параметры.
switch($name):
case 'Младший тренер':
	$sila	=	getSila(0.8);
	$inta	=	getInta(0.8);
	$lovka	=	getLovka(0.8);
	$hp		=	getHP();
break;

case 'Тренер':
	$sila	=	getSila(0.9);
	$inta	=	getInta(0.9);
	$lovka	=	getLovka(0.9);
	$hp		=	getHP();
break;

case 'Старший тренер':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Падальщик': // руна 3
	$sila	=	getSila(1.1);
	$inta	=	getInta(1.1);
	$lovka	=	getLovka(1.1);
	$hp		=	getHP();
break;

case 'Молодой падальщик':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Сильный охранник корована':
	$sila	=	getSila(1.1);
	$inta	=	getInta(1.1);
	$lovka	=	getLovka(1.1);
	$hp		=	getHP();
break;

case 'Охранник в красном':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Охранник в черном':
	$sila	=	getSila(0.9);
	$inta	=	getInta(0.9);
	$lovka	=	getLovka(0.9);
	$hp		=	getHP();
break;

case 'Кротокрыс': // руна 4
	$sila	=	getSila(1.1);
	$inta	=	getInta(1.1);
	$lovka	=	getLovka(1.1);
	$hp		=	getHP();
break;

case 'Молодой кротокрыс':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Мясной жук':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Стервятник':
	$sila	=	getSila(1.2);
	$inta	=	getInta(1.2);
	$lovka	=	getLovka(1.2);
	$hp		=	getHP();
break;

case 'Молодой стервятник':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Кровосос':
	$sila	=	getSila(1.1);
	$inta	=	getInta(1.1);
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Шершень':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka(1.5);
	$hp		=	getHP();
break;

case 'Волк':
	$sila	=	getSila(1.3);
	$inta	=	getInta(1.1);
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Волчица':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka(1.3);
	$hp		=	getHP(1.1);
break;

case 'Остер': // руна 1
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Черный гоблин': // руна 2
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP(0.7);
break;

case 'Гоблин':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP(1.4);
break;

case 'Тролль': // точильный камень
	$sila = 2000;
	$inta = 5000;
	$lovka = 1;
	$hp = 50000;
break;

case 'Дракон': // молния судьбы
	$sila = 1800;
	$inta = 4500;
	$lovka = 1;
	$hp = 40000;
break;

case 'Глорх':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Кусач':
	
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Шмыг':
	$sila	=	getSila(1.2);
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Оборотень':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP(0.5);
break;

case 'Упырь':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Орочий маг': // руна 5
	$sila	=	getSila(1.1);
	$inta	=	getInta(1.1);
	$lovka	=	getLovka(1.1);
	$hp		=	getHP();
break;

case 'Орочий шаман':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp	=	getHP(1.1);
break;

case 'Ледяной голем': // сердце ледяного голема
	$sila	=	getSila(0.7);
	$inta	=	getInta();
	$lovka	=	getLovka(0.7);
	$hp		=	getHP();
break;

case 'Огненный голем': // сердце огненного голема
	$sila	=	getSila(0.7);
	$inta	=	getInta(0.7);
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Каменный голем': // сердце каменного голема
	$sila	=	getSila();
	$inta	=	getInta(0.7);
	$lovka	=	getLovka(0.7);
	$hp		=	getHP();
break;

case 'Гарпия': //тотем гарпии
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Орочий воин':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Орк':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP(1.1);
break;

case 'Болотожор':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Огненная ящерица':
	$sila	=	getSila(1.3);
	$inta	=	getInta(1.3);
	$lovka	=	getLovka(1.3);
	$hp		=	getHP();
break;

case 'Огненный варан':
	$sila	=	getSila(1.2);
	$inta	=	getInta(1.2);
	$lovka	=	getLovka(1.2);
	$hp		=	getHP();
break;

case 'Скелет':
	$sila	=	getSila();
	$inta	=	getInta(1.3);
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Зомби':
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka(1.3);
	$hp		=	getHP();
break;

case 'Ползун': // сломаный браслет
	$sila	=	getSila();
	$inta	=	getInta();
	$lovka	=	getLovka();
	$hp		=	getHP();
break;

case 'Шелкопряд': // шелковая нить
	$sila	=	getSila(1.1);
	$inta	=	getInta(1.1);
	$lovka	=	getLovka(1.1);
	$hp		=	getHP();
break;

default: msg2('Нет бота '.ekr($name),1); break;
endswitch;
?>
