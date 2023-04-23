<?

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
			'complex'=>$plan['complex']['value'],
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