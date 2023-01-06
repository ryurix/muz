<?

w('keys');
$key = key_next('store');

$catalog = w('catalog-def');
//$up = w('catalog-up', $catalog);
$up = w('catalog-all');
$plan = w('plan-store', $up);
$path = '/files/store/'.$key.'/';
$plan['']['path'] = $path;
$plan['pics']['path'] = $path;
$plan['files']['path'] = $path;
$plan['grp']['default'] = 1;
$plan['sign1']['default'] = 0;
$plan['sign2']['default'] = 0;
$plan['sign3']['default'] = 0;
$plan['sign4']['default'] = 0;
$plan['up']['default'] = $catalog;
$plan['w']['default'] = 100;
$plan['sale']['default'] = 10;
$plan['dt']['default'] = now();
//$plan['speed']['default'] = 100;
$plan['hide']['default'] = 0;
$plan['vendor']['default'] = 1;

w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value'] == 1) {
	w('search');
	$quick = $plan['brand']['values'][$plan['brand']['value']];
	$quick.= $plan['model']['value'].$plan['name']['value'];
	$quick.= $plan['short']['value'].$plan['info']['value'];
	$quick = search_quick($quick);

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

	w('clean');
	$url = $plan['brand']['values'][$plan['brand']['value']].' '.$plan['model']['value'].' '.$plan['name']['value'];
	$url = $key.'-'.str2url(trim($url));

	$data = array(
		'i'=>$key,
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
//		'city'=>$plan['city']['value'],
//		'speed'=>$plan['speed']['value'],
		'price'=>$plan['price']['value'],
		'price2'=>$plan['price2']['value'],
		'sale'=>$plan['sale']['value'],
		'count'=>$plan['count']['value'],
		'user'=>$_SESSION['i'],
		'dt'=>now(),
		'info'=>$plan['info']['value'],
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
	}
	db_insert('store', $data);

	db_insert('log', array(
		'type'=>4,
		'dt'=>now(),
		'user'=>$_SESSION['i'],
		'info'=>'store: '.$key,
	));
	alert('<a href="/store/'.$key.'">Товар</a> добавлен');
	cache_delete('sync-chunk');
	cache_delete('sync-names');
	//redirect('/catalog/'.$plan['up']['value']);
	redirect('/store/'.$key);
	//$config['plan'] = $plan;
} else {
	$plan['filter']['up'] = $plan['up']['value'];
	$config['plan'] = $plan;
}

?>