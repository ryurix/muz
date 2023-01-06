<?

if ($args) {
	$config['action'] = array(
//		array('href'=>'/menu/new', 'action'=>'<i class="icon-plus"></i> Добавить'),
		array('href'=>'/portfolio/'.$args, 'action'=>'Посмотреть'),
		array('href'=>'/portfolio/'.$args.'/edit', 'action'=>'Изменить запись'),
		array('href'=>'/portfolio/'.$args.'/erase', 'action'=>'Удалить запись'),
	);
}

?>