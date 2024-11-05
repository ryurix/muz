<?

if (is_user('catalog')) {
	$hide = '';
} else {
	$children = cache_load('children');
	$hide = ' AND up IN ('.implode(',', array_keys($children)).')';
}

w('clean');
$num = first_int($config['args'][0]);
$q = db_query('SELECT * FROM store WHERE i="'.$num.'"'.$hide);

if ($row = db_fetch($q)) {

	// Проверяем url
	if ($config['args'][0] != $row['url'] || count($config['junk'])) {
		redirect('/store/'.$row['url'], 301);
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
	$config['title2'] = str_replace('%name%', $name, dict(cache_get('store-title')));
	$config['name'] = $name;
	if (!$row['tag0']) {
		//'Купить '.$name.' по выгодной цене с доставкой в музыкальном интернет-магазине'.$shop.'. Выгодное предложение!';
		$config['description'] = str_replace('%name%', $name, dict(cache_get('store-description')));;
		$config['keywords'] = str_replace('%name%', $name, dict(cache_get('store-keywords')));
	}
//	$config['keywords'] = strlen($row['tags']) ? $row['tags'] : $name;
//	$config['meta'] = $row['tags'];

	$action = count($config['args']) > 1 ? $config['args'][1] : 'view';
	$row['code'] = trim($row['code'] ?? '', ',');

	$config['row'] = $row;
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
				'dt0'=>now(),
				'dt'=>isset($rate['dt']) ? $rate['dt']['value'] : now(),
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
				alert('Отзыв сохранён, после проверки модератором он будет добавлен.', 'success');
				$rate = array();
			} else {
				alert('Пожалуйста оцените товар!', 'danger');
			}
		}
		$config['plan-rate'] = $rate;
	} elseif ($action == 'order') {
		$config['name'] = 'Все заказы '.$name;
		$config['row'] = $row;
		refile('order.html');
	} elseif ($action == 'qr') {
		$config['row'] = $row;
		w('qrstore');
	} elseif ($action == 'edit') {
		include __DIR__.'/edit.php';
		$config['plan'] = $plan;
		refile('../new.html');
	} elseif ($action == 'complex') {
		include __DIR__.'/complex.php';
		refile('complex.html');
	}
} else {
	redirect('/404');
}

?>