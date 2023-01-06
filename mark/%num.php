<?

$q = db_query('SELECT * FROM mark WHERE i='.$config['args'][0]);
if ($row = db_fetch($q)) {
	$config['name'] = $row['name'];

	$plan = w('plan-mark');
	$plan['']['default'] = $row;
	w('request', $plan);

	if ($plan['']['valid'] && $plan['send']['value'] == 1) {
		db_update('mark', array(
			'name'=>$plan['name']['value'],
			'info'=>$plan['info']['value'],
			'color'=>$plan['color']['value'],
			'w'=>$plan['w']['value'],
		), array('i'=>$row['i']));

		w('cache-mark');
		redirect('.');
	}

	if ($plan['']['valid'] && $plan['send']['value'] == 2) {
		db_delete('mark', array('i'=>$row['i']));

		w('cache-mark');
		redirect('.');
	}

	$config['plan'] = $plan;
} else {
	redirect('.');
}

?>