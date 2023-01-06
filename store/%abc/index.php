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

	if ($action == 'view') {
		$gets = isset($_GET['search']) ? 0 : count($_GET);
		if ($gets) {
			$config['canonical'] = '/store/'.$row['i'].'-'.str2url($name);
		}
		$config['og:image'] = $row['pic'];
		$config['row'] = $row;
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
		$config['row']['up'] = $row['up'];
		$up = w('catalog-all');
		if (!isset($up[$row['up']])) {
			$up = array_reverse($up, true);
			$up[$row['up']] = '';
			$up = array_reverse($up, true);
		}
		//$up = w('catalog-up', $row['up']);
		$plan = w('plan-store', $up);
		$plan['']['default'] = $row;

		$path = '/files/store/'.$row['i'].'/';
		$plan['']['path'] = $path;
		$plan['pics']['path'] = $path;
		$plan['files']['path'] = $path;
		if ($row['yandex'] > 0) {
			$plan['yandex']['suffix'] = '<a href="http://market.yandex.ru/product/'.$row['yandex']
				.'/" target=_BLANK>Посмотреть на Яндексе <i class="icon-share"></i></a>';
		}
		$icon = $row['icon'];

		w('request', $plan);
		if ($plan['']['valid']) {
			if ($plan['send']['value'] >= 1) {

				$pathway = cache_load('pathway-hide');
				function get_product_type($i, $pathway) {
					$node = $pathway[$i];
					$up = isset($node['up']) ? $node['up'] : 0;

					if ($up) {
						return get_product_type($up, $pathway).' '.$node['name'];
					} else {
						return $node['name'];
					}
				}
				if (!$plan['tag0']['value']) {
					$plan['tag1']['value'] = get_product_type($plan['up']['value'], $pathway)
						.' '.$plan['brand']['values'][$plan['brand']['value']]
						.' '.$plan['model']['value'].' '.$plan['name']['value'];
				}

				w('search');
				$quick = $plan['brand']['values'][$plan['brand']['value']];
				$quick.= $plan['model']['value'].$plan['name']['value'];
				$quick.= $plan['short']['value'].$plan['info']['value'];
				$quick = search_quick($quick);

				w('clean');
				$url = $plan['brand']['values'][$plan['brand']['value']].' '.$plan['model']['value'].' '.$plan['name']['value'];
				$url = $row['i'].'-'.str2url(trim($url));

				$data = array(
					'url'=>$url,
					'up'=>$plan['up']['value'],
					'grp'=>$plan['grp']['value'],
					'hide'=>$plan['hide']['value'],
					'sign1'=>$plan['sign1']['value'],
					'sign2'=>$plan['sign2']['value'],
					'sign3'=>$plan['sign3']['value'],
					'sign4'=>$plan['sign4']['value'],
					'quick'=>$quick,
					'name'=>$plan['name']['value'],
					'model'=>$plan['model']['value'],
					'tag0'=>$plan['tag0']['value'],
					'tag1'=>$plan['tag1']['value'],
					'tag2'=>$plan['tag2']['value'],
					'tag3'=>$plan['tag3']['value'],
					'brand'=>$plan['brand']['value'],
					'vendor'=>$plan['vendor']['value'],
					'short'=>$plan['short']['value'],
//					'city'=>$plan['city']['value'],
//					'speed'=>$plan['speed']['value'],
					'price'=>$plan['price']['value'],
					'price2'=>$plan['price2']['value'],
					'sale'=>$plan['sale']['value'],
					'count'=>$plan['count']['value'],
					'user'=>$_SESSION['i'],
					'dt'=>now(),
					'info'=>$plan['info']['value'],
					'size'=>$plan['size']['value'],
					'w'=>$plan['w']['value'],
					'filter'=>$plan['filter']['value'],
					'files'=>$plan['files']['value'],
					'pics'=>$plan['pics']['value'],
					'yandex'=>$plan['yandex']['value'],
				);

				if (w('store-pic', $plan)) {
					$data['icon'] = $plan['icon']['value'];
					$data['mini'] = $plan['icon']['mini'];
					$data['pic'] = $plan['icon']['pic'];
					$icon = $data['icon'];
				}
				if ($plan['send']['value'] == 2) {
					$data['brand'] = 0;
				}
				if ($plan['send']['value'] == 3) {
					$data['icon'] = '';
					$data['mini'] = '';
					$data['pic'] = '';
				}
//*
	db_insert('log', array(
		'dt'=>now(),
		'user'=>$_SESSION['i'],
		'type'=>3,
		'info'=>'store: '.$row['i'].', ip: '.client_ip(),
	));
//*/
				db_update('store', $data, array('i'=>$row['i']));
				alert('<a href="/store/'.$row['i'].'/edit">Товар</a> изменен');
				cache_delete('sync-chunk');
				cache_delete('sync-names');
				redirect('/store/'.$row['i']);
			}
		}
		$plan['icon']['link'] = $icon;
		$plan['filter']['up'] = $plan['up']['value'];
		$config['plan'] = $plan;
		refile('../new.html');
	}
} else {
	redirect('/404');
}

?>