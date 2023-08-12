<?

$config['full'] = 1;

/*
w('clean');
$shop = first_int($config['args'][0]);

$q = db_query('SELECT * FROM catalog WHERE i='.$shop);
if ($row = db_fetch($q)) {
	$config['row'] = $row;
	db_close($q);
} else {
	redirect(404);
}

$config['name'] = $row['name'];
*/

w('clean');
$shop = first_int($config['args'][0]);

if (is_user('catalog')) {
	w('user-config');
	$hide = '';
} else {
	$children = cache_load('children');
	$hide = isset($children[$shop]) ? '' : ' AND 1=0';
}

$q = db_query('SELECT * FROM catalog WHERE i="'.$shop.'"'.$hide);
$root = '/catalog';

if ($row = db_fetch($q)) {
	db_close($q);

	if (strlen($row['icon'])) {
		$config['og:image'] = $row['icon'];
	}

	if (count($config['args']) > 1) {
		$action = $config['args'][1];
	} else {
		$action = '';
	}

	if (is_user('catalog')) {
		$config['action'] = array(
			array('action'=>'+ Товар', 'href'=>'/store/new?catalog='.$row['i']),
			array('action'=>'+ Раздел', 'href'=>'/catalog/new?catalog='.$row['i']),
		);
		if ($row['i']) {
			$config['action'][] = array('action'=>'Изменить раздел', 'href'=>'/catalog/'.$row['url'].'/edit');
			$config['action'][] = array('action'=>'Удалить раздел', 'href'=>'/catalog/'.$row['url'].'/erase');
		}
	}

	if ($action == 'edit') {
		$up = w('catalog-all');
		unset($up[$row['i']]);
		$plan = w('plan-catalog', $up);
		$plan['']['default'] = $row;
		w('request', $plan);
		$icon = $row['icon'];

		if ($plan['']['valid']) {
			if ($plan['send']['value'] == 1) {
				$data = array(
					'up'=>$plan['up']['value'],
					'tag0'=>$plan['tag0']['value'],
					'tag1'=>$plan['tag1']['value'],
					'tag2'=>$plan['tag2']['value'],
					'tag3'=>$plan['tag3']['value'],
					'name'=>$plan['name']['value'],
					'name2'=>$plan['name2']['value'],
					'url'=>$plan['url']['value'],
					'hide'=>$plan['hide']['value'],
					'w'=>$plan['w']['value'],
					'filter'=>$plan['filter']['value'],
					'google'=>$plan['google']['value'],
					'short'=>$plan['short']['value'],
					'info'=>$plan['info']['value'],
					'size'=>$plan['size']['value'],
				);
				$icon = w('catalog-icon', $plan);
				if (strlen($icon)) {
					$data['icon'] = $icon;
				}
				db_update('catalog', $data, array('i'=>$row['i']));
				alert('Раздел изменен');
				w('catalog-cache');
//				redirect($root.'/'.$row['i']);
			} elseif ($plan['send']['value'] == 2) {
				db_delete('catalog', array(
					'i'=>$row['i']
				));
				db_delete('catalog', array(
					'up'=>$row['i']
				));
				db_delete('store', array(
					'catalog'=>$row['i']
				));
				unlink($row['icon']);
				alert('Раздел вместе со всем содержимым удален');
				w('catalog-cache');
				redirect($root);
			}
		}
		$plan['icon']['link'] = $icon;
		$config['plan'] = $plan;
		refile('../new.html');
		return;
	}
} else {
	redirect($root);
}

// * * * Товары и Фильтры

$params = array(); // url => код параметра
$filter = array(); // Фильтры
$parfil = array(); // код параметра => фильтр
$parnam = array(); // код параметра => название

// Остальные фильтры (если есть)

$pw = cache_load('pathway-hide');
$fs = $pw[$shop]['f'];
if (count($fs)) {
	$q = db_query('SELECT * FROM filter WHERE i IN ('.implode(',', $fs).')');
	while ($i = db_fetch($q)) {
		$filter[$i['i']] = array(
			'name'=>$i['name'],
			'info'=>$i['info'],
			'values'=>array(), // values -- код=>текст
			'counts'=>array(), // counts -- код=>счетчик
		);
	}
	db_close($q);

	$q = db_query('SELECT * FROM param WHERE filter IN ('.implode(',', $fs).') ORDER BY w');
	while ($i = db_fetch($q)) {
		$params[$i['code']] = $i['i'];
		$parfil[$i['i']] = $i['filter'];
		$parnam[$i['i']] = $i['value'];

		$filter[$i['filter']]['values'][$i['i']] = $i['value'];
		$filter[$i['filter']]['counts'][$i['i']] = 0;
	}
	db_close($q);
}

// Фильтр по бренду

$brand = cache_load('brand');
if ($row['i']) {
	$brand2 = cache_load('brand2');

	$filter[0] = array(
		'name'=>'Бренд',
		'info'=>'Марка производителя',
		'values'=>array(),
		'counts'=>array(),
	);

	$brands = explode(',', $row['brand']);
	foreach ($brands as $k) {
		if (!strlen(kv($brand, $k, '')) || !strlen(kv($brand2, $k, ''))) { continue; }

		$params[$brand2[$k]] = -$k;
		$parfil[-$k] = -1;

		$filter[0]['values'][-$k] = $brand[$k];
		$filter[0]['counts'][-$k] = 0;
	}
}

// Декодируем параметры фильтров (если есть)

$param = array();
if (isset($config['args'][1])) {
	w('clean');
	$p = $config['args'][1];

	$a = explode(' ', $p);

	foreach ($a as $i) {
		if (!strlen($i)) { continue; }
		if (isset($params[$i])) {
			$param[] = $params[$i];
		}
	}
/*
	if (isset($config['args'][2])) {
		redirect(404);
	}
*/
}

// Если есть параметры -- тексты убираем

if (count($param)) {
	$row['info'] = '';
	$row['short'] = '';
}

// Проверяем каноничность url и перенаправляем если url неправильный

$url = '/catalog/'.$row['url'];
if (count($param)) {
	$url.= '/'.params($param, $params);
}
if (rtrim($url, '/') != str_replace(' ', '+', $config['q'])) {
	redirect($url, 301);
}

// Если есть параметры -- ищем подкатегорию

$subcat = 0;
if (count($param)) {
	$code = $param;
	sort($code);
	$code = implode(',', $code);

	$q = db_query('SELECT * FROM subcat WHERE up='.$row['i'].' AND code="'.$code.'"');
	if ($sub = db_fetch($q)) {
		$subcat = $sub['i'];
		$row['tag0'] = $sub['tag0'];
		$row['tag1'] = $sub['tag1'];
		$row['tag2'] = $sub['tag2'];
		$row['tag3'] = $sub['tag3'];
		$row['name2'] = $sub['name2'];
		$row['short'] = $sub['short'];
		$row['info'] = $sub['info'];
		db_close($q);

		if (is_user('catalog')) {
			$config['action'] = array(array('href'=>'/subcat?up='.$row['i'].'&code='.$code, 'action'=>'Изменить подраздел'));
		}
	} else {
		if (is_user('catalog')) {
			$config['action'] = array(array('href'=>'/subcat?up='.$row['i'].'&code='.$code, 'action'=>'Создать подраздел'));
		}
	}
}

// Добавляем фильтры к имени страницы

$name = $row['name2'];
if (!$subcat) {
	$namep = '';
	$usedf = array();
	foreach ($param as $p) {
		$f = kv($parfil, $p);

		if ($p < 0) {
			$name.= ' '.kv($brand, -$p);
		} else {
			$namef = isset($usedf[$f]) ? '' : kv(kv($filter, $f), 'name').' ';
			$namep.= ', '.$namef.kv($parnam, $p);
		}

		$usedf[$f] = 1;
	}
	$name.= $namep;
}


// Сортировка и ограничения

//$where = array('speed2.vendor=store.vendor', 'speed2.cire='.kv($_SESSION, 'cire', 0));
$worth = ',IF(store.price > 0, 0, IF(store.count > 0, 1, 2)) worth';
$avail = ',IF(store.count > 0, IF(store.price > 0, 0, 1), IF(store.price > 0, 2, 3)) avail';
$from = ['store'];
$where = [];

if (!is_user('catalog')) {
	$where[] = 'hide<=0';
}

$get = w('plan-catalog-get');

// Пробуем загрузить из конфига сортировку по умолчанию
w('user-config');

$get['sort']['default'] = get_user_config('sort', 'pop');
$get['per']['default'] = get_user_config('per', 24);

w('request', $get);
if (!$get['sort']['valid']) { $get['sort']['value'] = 'pop'; }
if (!$get['per']['valid']) { $get['per']['value'] = 24; }
$config['get'] = $get;

// Сохраняем сортировку
w('user-config');
set_user_config('sort', $get['sort']['value']);
set_user_config('per', $get['per']['value']);


if ($get['min']['value']) {
	$where[] = 'store.price>='.$get['min']['value'];
}
/*
if ($get['max']['value']) {
	$where[] = 'store.price<='.$get['max']['value'];
}
*/

$sort = $get['sort']['value'];
switch ($sort) {
	case 'pop': $order = 'hide,avail,price'; break;
	case 'name': $order = 'brand,name'; break;
	case 'price': $order = 'worth,price'; break;
	case 'price2': $order = 'avail,price'; break;
//	case 'speed': $order = 'avail,speed,price'; break;
	default: $order = 'hide,avail,price';
}

if ($get['sklad']['value'] && $get['sklad']['valid']) {
	$from[] = 'sync';
	$where[] = 'sync.store=store.i';
	$where[] = 'sync.vendor='.$get['sklad']['value'];
	$worth = ',IF(sync.price > 0, 0, IF(sync.count > 0, 1, 2)) worth';
	$avail = ',IF(sync.count > 0, IF(sync.price > 0, 0, 1), IF(sync.price > 0, 2, 3)) avail';
	//$where[] = 'EXISTS (SELECT * FROM sync WHERE sync.store=store.i AND sync.vendor='.$get['sklad']['value'].')'; //  AND sync.count>0
}

if ($get['group']['value'] && $get['group']['valid']) {
	$where[] = 'store.grp='.$get['group']['value'];
}

// Получаем список товаров

$ch = cache_load('children'.(is_user('catalog') ? '-hide' : ''));

$where[] = 'store.up IN ('.implode(',', $ch[$shop]).')';

if ($get['search']['value']) {
	w('search');
	$s = $get['search']['value'];
	$i = str_replace(array('M','м','М'), 'm', $s);

	if (preg_match('/^m[0-9]+$/', $i)) {
		$where[] = 'store.i='.substr($i, 1);
	} else {
		$where[] = 'store.quick LIKE "%'.search_like($s).'%"';
	}
}

$store = array();
$select = 'SELECT store.i,store.url,store.brand,store.filter'
.',store.name'
.',store.up'
.',store.model'
.',store.icon'
.',store.price'
.',store.count'
.',store.vendor'
.',store.sign1'
.',store.sign2'
.',store.sign4'
.$worth
.$avail
//.',speed2.speed speed'
.' FROM '.implode(',', $from).' WHERE '.implode(' AND ', $where).' ORDER BY '.$order; // ,speed2

$fil = array(); // Собираем фильтры и параметры в один массив
foreach ($filter as $k=>$f) {
	foreach ($param as $p) {
		if (isset($f['counts'][$p])) {
			if (!isset($fil[$k])) {
				$fil[$k] = array();
			}
			$fil[$k][] = $p;
		}
	}
}

$price2 = 0;

$q = db_query($select);
while ($i = db_fetch($q)) {
	$price2 = max($price2, $i['price']);
	if ($get['max']['value'] && $get['max']['value'] < $i['price']) {
		continue;
	}

	$pp = explode(',', $i['filter']);
	$pp[] = -$i['brand'];
/*
	foreach ($pp as $p) {
		foreach ($filter as $k=>&$f) {
			if (isset($f['counts'][$p])) {
				$f['counts'][$p]++;
			}
		}
	}
*/
	$ff = array(); // Собираем фильтры и параметры в массив для товара
	foreach ($filter as $k=>$f) {
		foreach ($pp as $p) {
			if (isset($f['counts'][$p])) {
				$ff[$k] = $p;
			}
		}
	}

	$show = true;
	$hide = true;
	$skip = array();

	// Сравниваем фильтр и товар
	foreach ($filter as $k=>$f) {
		if (isset($fil[$k])) {
			if (isset($ff[$k])) {
				if (!in_array($ff[$k], $fil[$k])) {
					$hide = $show;
					$show = false;
				}
			} else {
				$hide = false;
				$show = false;
			}
		} else {
			if (isset($ff[$k])) {
				if ($k && $get['nofilter']['value']) {
					$hide = $show;
					$show = false;
				} else {
					$skip[] = $ff[$k];
				}
			}
		}
	}

	if ($hide || $show) {
		foreach ($filter as $k=>$f) {
			foreach ($pp as $p) {
				if (isset($f['counts'][$p])) {
					if ((!in_array($p, $param) && !in_array($p, $skip)) || $show) {
						$filter[$k]['counts'][$p]++;
					}
				}
			}
		}
	}

	if ($show) {
		$i['brand'] = kv($brand, $i['brand']);
		$store[$i['i']] = $i;
	}
}
db_close($q);

$limit = $get['per']['value'] ?? $get['per']['default'];
$max = ceil(count($store) / $limit);

$page = $get['page']['value'];
$page = $page < $max ? $page : $max;
//$config['page'] = $page;
//alert($page.' : '.$max);
$config['pager'][0] = array(
	'max'=>$max,
	'page'=>$page,
);

if (count($store) > $limit) {
	$config['pages'] = count($store);
	$store = array_slice($store, $limit*($page - 1), $limit, true);
}

// Проверяем страницу на предмет каноничности

w('catalog-get');
$getline = catalog_param($config['get']);
$getlen = strlen($getline);

$here = array('i'=>0);
$canon = array();
if ($getlen) {
	$url32 = crc32($url);
	$code32 = 0;

	$q = db_query('SELECT * FROM canon WHERE url32='.$url32.' ORDER BY dt DESC');
	while ($i = db_fetch($q)) {
		if ($url == $i['url']) {
			$canon = $i;
			$here = $i;
		}
	}

	if (!count($canon)) {
		$canon = array(
			'i'=>0,
			'canon'=>1,
			'url'=>$url,
		);
	}
	$canon['i'] = 1;
} else {
	$code = implode(',', array_keys($store));
	$code32 = crc32($code);
}

$w = 0;
if ($code32) {
	$q = db_query('SELECT * FROM canon WHERE code32='.$code32.' ORDER BY canon,dt DESC');
	while ($i = db_fetch($q)) {
		if ($code == $i['code']) {
			$canon = $i;

			if ($i['url'] == $url) {
				$here = $i;
			}
		}
	}
	db_close($q);

	if (!$getlen) {
		$deep = count(kv($pw[$shop], 'pre', array()));
		$w = 100 - count($param) + $deep*5; // TODO

		if (count($canon) && $canon['canon'] < $w && $canon['i']) {
			db_query('DELETE FROM canon WHERE i='.$canon['i']);
			$canon = array();
		}

		if (!count($canon)) {
			db_insert('canon', array(
				'dt'=>now(),
				'code32'=>$code32,
				'code'=>$code,
				'url'=>$url,
				'url32'=>crc32($url),
				'canon'=>$w,
			));
		}
	}
}

// Получаем количество параметров без учёта поиска
$gets = isset($_GET['search']) ? 0 : count($_GET);

// Если есть параметры после ? -- ставим каноническую ссылку
// Для разделов без фильтров канонические ссылки не ставим
if ($gets || (count($param) && count($canon) && $canon['i']!=$here['i'] && $canon['canon']>=$w)) {
	$config['canonical'] = kv($canon, 'url', $url);
/*
	$canonical = '<link rel="canonical" href="https://'.$config['domain'].(kv($canon, 'url', $url)).'">';
	if (isset($block['head'])) {
		$block['head'].= "\n".$canonical;
	} else {
		$block['head'] = $canonical;
	}
*/
}

// Теги

if (strlen($get['search']['value'])) {
	$tag_search = '-search';
	$search = mb_substr(htmlentities($get['search']['value']), 0, 50);
} else {
	$tag_search = '';
	$search = '';
}

$tag_page = $get['page']['value'] == 1 ? '' : ', страница '.$get['page']['value']; // Дополнение к основным тегам с номером страницы

if (count($param)) {
	$row['tag0'] = 0;
}

$config['name'] = $name;
$config['title2'] = str_replace(array('%name%', '%search%'), array($name, $search), dict(cache_get('catalog-title'.$tag_search)));
$config['title2'].= $tag_page;

if ($row['tag0'] && !strlen($search)) {
/*
	if (strlen($search)) {
		$row['tag1'] = str_replace(array('%name%', '%search%'), array($name, $search), dict(cache_get('catalog-description'.$tag_search)));
		$row['tag3'] = $config['title2'];
	}
//*/
	$row['tag1'].= $tag_page;
	$row['tag3'].= $tag_page;
} else {
	$config['description'] = str_replace(array('%name%', '%search%'), array($name, $search), dict(cache_get('catalog-description'.$tag_search)));
	$config['description'].= $tag_page;
	$config['keywords'] = str_replace(array('%name%', '%search%'), array($name, $search), dict(cache_get('catalog-keywords')));

}

// Передача данных

if (count($param)) {
	$row['up'] = $row['i']; // для pathway
}

$config['row'] = $row;
$config['store'] = $store;
$config['price2'] = $price2;
$config['filter'] = $filter;
$config['params'] = $params;
$config['param'] = $param;

$config['gtag-event'] = array(
	'event_name'=>'page_view',
	'ecomm_pagetype'=>($config['get']['search']['value'] ? 'searchresults' : 'category'),
);

if (strlen($config['get']['search']['value'])) {
	$keys = array();
	foreach ($store as $k=>$v) {
		if ($v['count']) {
			$keys[] = $k;
		}
	}
	$config['gtag-event']['ecomm_prodid'] = $keys;
}

function params($param, $params) {
	$s = array();
	foreach ($params as $k=>$v) {
		if (in_array($v, $param)) {
			$s[] = $k;
		}
	}
	return implode('+', $s);
}

function remove_value($value, $array) {
	$a = array();
	foreach($array as $k=>$v) {
		if ($v != $value) {
			$a[$k] = $v;
		}
	}
	return $a;
}

//$s = params($param, $params);
//print_pre($s);

?>