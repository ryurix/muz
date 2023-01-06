<?

$plan = w('plan-rate');
w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value']==1) {
	db_insert('rate', array(
		'dt0'=>now(),
		'dt'=>$plan['dt']['value'],
		'name'=>$plan['name']['value'],
		'rate'=>$plan['rate']['value'],
		'plus'=>$plan['plus']['value'],
		'minus'=>$plan['minus']['value'],
		'body'=>$plan['body']['value'],
		'state'=>$plan['state']['value'] ? 10 : 0,
	));
	redirect('.', 302);
}

$config['plan'] = $plan;

?>