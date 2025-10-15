<?

$plan = w('plan-brand');
w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value'] == 1) {
	$data = array(
		'code'=>$plan['code']['value'],
		'name'=>$plan['name']['value'],
		'icon'=>$plan['icon']['value'],
		'info'=>$plan['info']['value'],
		'w'=>$plan['w']['value']
	);
	db_insert('brand', $data);
	w('cache-brand');
	\Flydom\Alert::warning('Производитель добавлен');
	\Page::redirect('/brand/');
} else {
	$config['plan'] = $plan;
}

?>