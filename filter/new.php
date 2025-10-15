<?

$plan = w('plan-filter');

w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value'] == 1) {
	$data = array(
		'name'=>$plan['name']['value'],
		'info'=>$plan['info']['value'],
		'value'=>php_encode($plan['value']['value']),
	);

	db_insert('filter', $data);
	save_param(db_insert_id(), $plan['value']['value']);
	w('cache-filter');
	\Flydom\Alert::warning('Фильтр добавлен');
	\Page::redirect('/filter');
} else {
	$config['plan'] = $plan;
}

?>