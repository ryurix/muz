<?

if (\User::is('catalog')) {

	if ($args['i'] > 0) {
		$action = [
	//		['href'=>'/menu/new', 'action'=>'<i class="icon-plus"></i> Добавить'),
			''=>['href'=>'/store/'.$args['url'], 'action'=>'Посмотреть'],
			'edit'=>['href'=>'/store/'.$args['url'].'/edit', 'action'=>'Изменить'],
			'order'=>['href'=>'/store/'.$args['url'].'/order', 'action'=>'Заказы'],
			'sync'=>['href'=>'/store/'.$args['url'].'/sync', 'action'=>'Синхронизация'],
			'clone'=>['href'=>'/store/'.$args['url'].'/clone', 'action'=>'Клонировать'],
			'erase'=>['href'=>'/store/'.$args['url'].'/erase', 'action'=>'Удалить'],
			'qr'=>['href'=>'/store/'.$args['url'].'/qr" target="_BLANK', 'action'=>'QR'],
		];

		if ($args['complex']) {
			$action['complex'] = ['href'=>'/store/'.$args['url'].'/complex', 'action'=>'Составной'];
		}

		$code = \Page::menu()['code'];
		if (isset($action[$code])) {
			$action[$code]['here'] = 1;
		}

		foreach ($action as $i) {
			\Action::before($i['href'], $i['action']);
		}
	}
}