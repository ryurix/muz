<?

if (\User::is('catalog')) {
	$hide = '';
} else {
	$children = cache_load('children');
	$hide = ' AND up IN ('.implode(',', array_keys($children)).')';
}

w('clean');
$num = first_int(\Page::arg());
$q = db_query('SELECT * FROM store WHERE i="'.$num.'"'.$hide);

if ($row = db_fetch($q)) {
	\Page::set('up', $row['up']);

	// Проверяем url
	if (\Page::arg() != $row['url']) {
		\Page::redirect('/store/'.$row['url'], 301);
	}

	w('store-action', $row);

	$brand = cache_load('brand');
	$name = $row['name'];
	if (strlen($row['model']) > 0) {
		$name = $brand[$row['brand']].' '.$row['model'].' '.$name;
	}

	$shop = 'Muzmart';
	$city = cache_load('city');
	if (isset($config['site']) && $config['site'] && isset($city[$config['site']])) {
		$shop.= ' '.$city[$config['site']];
	}

	// $name.' купить, цена, фото - в магазине музыкальных инструментов '.$shop;
	$config['title2'] = str_replace('%name%', $name, \Page::dict(cache_get('store-title')));
	\Page::name($name);
	if (!$row['tag0']) {
		//'Купить '.$name.' по выгодной цене с доставкой в музыкальном интернет-магазине'.$shop.'. Выгодное предложение!';
		$config['description'] = str_replace('%name%', $name, \Page::dict(cache_get('store-description')));
		$config['keywords'] = str_replace('%name%', $name, \Page::dict(cache_get('store-keywords')));
	}
//	$config['keywords'] = strlen($row['tags']) ? $row['tags'] : $name;
//	$config['meta'] = $row['tags'];

	$action = \Page::arg(1, 'view');
	$row['code'] = trim($row['code'] ?? '', ',');


	if ($action == 'view') {
		$gets = isset($_GET['search']) ? 0 : count($_GET);
		if ($gets) {
			$config['canonical'] = '/store/'.$row['i'].'-'.str2url($name);
		}
		$config['og:image'] = $row['pic'];
		$config['gtag-event'] = array(
			'event_name'=>'page_view',
			'ecomm_pagetype'=>'product',
			'ecomm_prodid'=>$row['i'],
		);

		$rate = w('plan-rate');
		w('request', $rate);
		if ($rate['']['valid'] && $rate['send']['value'] == 1) {
			$data = array(
				'state'=>isset($rate['state']) ? $rate['state']['value'] : 0,
				'store'=>$row['i'],
				'usr'=>$_SESSION['i'],
				'dt0'=>\Config::now(),
				'dt'=>isset($rate['dt']) ? $rate['dt']['value'] : \Config::now(),
				'name'=>$rate['name']['value'],
				'rate'=>$rate['rate']['value'],
				'plus'=>$rate['plus']['value'],
				'minus'=>$rate['minus']['value'],
				'body'=>$rate['body']['value'],
			);

			if ($rate['rate']['value']) {
				$q = db_query('SELECT * FROM rate ORDER BY i DESC LIMIT 5');
				while ($i = db_fetch($q)) {
					if ($i['store'] == $data['store']
					&& $i['name'] == $data['name']
					&& $i['plus'] == $data['plus']
					&& $i['minus'] == $data['minus']
					&& $i['body'] == $data['body']) {
						$rate['info']['value'] = 1;
					}
				}

				if (strlen($rate['info']['value']) == 0) { // Проверка на спам
					db_insert('rate', $data);
				}
				\Flydom\Alert::danger('Отзыв сохранён, после проверки модератором он будет добавлен.', 'success');
				$rate = array();
			} else {
				\Flydom\Alert::warning('Пожалуйста оцените товар!');
			}
		}
		$config['plan-rate'] = $rate;
	} elseif ($action == 'order') {
		\Page::name('Все заказы '.$name);
		$config['row'] = $row;
		\Page::body('order');
	} elseif ($action == 'qr') {
		$config['row'] = $row;
		w('qrstore');
	} elseif ($action == 'edit') {
		include __DIR__.'/edit.php';
		$config['plan'] = $plan;
		\Page::body(['store', 'new']);
	} elseif ($action == 'complex') {
		include __DIR__.'/complex.php';
		\Page::body('complex');
	}
} else {
	\Page::redirect('/404');
}