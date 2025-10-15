<?

$plan = w('plan-region');
w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value']==1) {
	$region = $plan['name']['value'];

	db_insert('region', array(
		'name' => $region,
		'w' => $plan['w']['value'],
	));
	\Page::redirect('.', 302);
}

$config['plan'] = $plan;

?>