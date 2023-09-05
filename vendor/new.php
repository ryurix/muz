<?

$plan = w('plan-vendor');
w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value'] == 1) {
	$data = array(
		'name'=>$plan['name']['value'],
		'typ'=>$plan['typ']['value'],
		'up'=>$plan['up']['value'],
		'w'=>$plan['w']['value'],
		'price'=>$plan['price']['value'],
		'prmin'=>$plan['prmin']['value'],
		'city'=>$plan['city']['value'],
//		'speed'=>$plan['speed']['value'],
		'ccode'=>$plan['ccode']['value'],
		'cname'=>$plan['cname']['value'],
		'ctype'=>$plan['ctype']['value'],
		'cbrand'=>$plan['cbrand']['value'],
		'ccount'=>$plan['ccount']['value'],
		'cprice'=>$plan['cprice']['value'],
		'copt'=>$plan['copt']['value'],
		'curr'=>$plan['curr']['value'],
		'short'=>$plan['short']['value'],
		'info'=>$plan['info']['value'],
	);
	db_insert('vendor', $data);
	$id = db_insert_id();
	if ($id) {
		alert('Поставщик добавлен');
		w('cache-vendor');
		redirect('/vendor/');
	} else {
		alert('Ошибка добавления поставщика');
	}
}