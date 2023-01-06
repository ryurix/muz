<?

$key = $config['args'][0];

$plan = w('plan-erase');
w('request', $plan);

if ($plan['']['valid']) {
	$up = db_result('SELECT pics FROM store WHERE i='.$key);
	if ($plan['send']['value'] == 1) {
		db_query('DELETE FROM pf WHERE i='.$key);
		alert('Запись удалена');
	}
	redirect('/portfolio');
} else {
	$config['plan'] = $plan;
}

?>