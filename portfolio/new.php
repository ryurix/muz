<?

w('keys');
$key = key_next('portfolio');

$plan = w('plan-pf');
$path = '/files/portfolio/'.$key.'/';
$plan['']['path'] = $path;
$plan['pics']['path'] = $path;

w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value'] == 1 && strlen($plan['pics']['value'])) {
	$data = array(
		'i'=>$key,
		'up'=>$plan['up']['value'],

		'name'=>$plan['name']['value'],
		'dt'=>$plan['dt']['value'],

		'pics'=>$plan['pics']['value'],
		'info'=>$plan['info']['value'],
	);
	db_insert('pf', $data);
	\Flydom\Alert::warning('<a href="/portfolio/'.$key.'">Запись</a> добавлена');
	\Page::redirect('/portfolio#'.$plan['up']['value']);
} else {
	$config['plan'] = $plan;
}

?>