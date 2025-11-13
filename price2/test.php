<?php

use \Flydom\Form\Form;

Form::plan([
	'store'=>new \Flydom\Input\Integer('Артикул'),
	'type'=>new \Flydom\Input\Select([
		'name'=>'Тип цены',
		'default'=>0,
		'values'=> \Price\Type::names()
	]),
	'send'=>new \Flydom\Input\Button(['names'=>['test'=>'Проверить']]),
]);

Form::parse();