<?

$plan = w('plan-mark');
w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value']==1) {

	db_insert('mark', array(
		'name'=>$plan['name']['value'],
		'info'=>$plan['info']['value'],
		'color'=>$plan['color']['value'],
		'w'=>$plan['w']['value'],
	));

	w('cache-mark');
	\Page::redirect('.');
}

$config['plan'] = $plan;

?>