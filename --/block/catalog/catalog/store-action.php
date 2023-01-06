<?

if ($args['i'] > 0) {
	$config['action'] = array(
//		array('href'=>'/menu/new', 'action'=>'<i class="icon-plus"></i> Добавить'),
		array('href'=>'/store/'.$args['url'], 'action'=>'Посмотреть'),
		array('href'=>'/store/'.$args['url'].'/edit', 'action'=>'Изменить'),
		array('href'=>'/store/'.$args['url'].'/order', 'action'=>'Заказы'),
		array('href'=>'/store/'.$args['url'].'/sync', 'action'=>'Синхронизация'),
		array('href'=>'/store/'.$args['url'].'/clone', 'action'=>'Клонировать'),
		array('href'=>'/store/'.$args['url'].'/erase', 'action'=>'Удалить'),
		array('href'=>'/store/'.$args['url'].'/qr" target="_BLANK', 'action'=>'QR'),
	);
}

?>