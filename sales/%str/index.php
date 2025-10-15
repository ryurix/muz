<?

$q = db_query('SELECT * FROM sale WHERE code="'.addslashes(\Page::arg()).'"');

if ($row = db_fetch($q)) {
	\Page::name($row['name']);

	$plan = w('plan-sale');
	$plan['send']['count'] = 2;
	$row['up'] = strlen($row['up']) ? explode(',', $row['up']) : array();
	$row['brand'] = strlen($row['brand']) ? explode(',', $row['brand']) : array();
	$plan['']['default'] = $row;
	w('request', $plan);
	if ($plan['']['valid'] && $plan['send']['value'] == 1) {
		db_update('sale', array(
			'name'=>$plan['name']['value'],
			'dt2'=>$plan['dt2']['value'],
			'perc'=>$plan['perc']['value'],
			'partner'=>$plan['partner']['value'],
			'up'=>implode(',', $plan['up']['value']),
			'brand'=>implode(',', $plan['brand']['value']),
		), array('code'=>$row['code']));
	} elseif ($plan['']['valid'] && $plan['send']['value'] == 2) {
		db_delete('sale', array(
			'code'=>$row['code'],
		));
		\Flydom\Alert::warning('Скидка удалена');
		\Page::redirect('/sales/list');
	}
	$config['plan'] = $plan;
} else {
	\Page::redirect('/sales');
}

?>