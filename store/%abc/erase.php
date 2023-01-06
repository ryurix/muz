<?

w('clean');
$key = first_int($config['args'][0]);

$plan = w('plan-erase');
w('request', $plan);

if ($plan['']['valid']) {
	$up = db_result('SELECT up FROM store WHERE i='.$key);
	if ($plan['send']['value'] == 1) {
		db_query('DELETE FROM store WHERE i='.$key);
		db_query('DELETE FROM sync WHERE store='.$key);
		alert('Товар удалён');
		cache_delete('sync-chunk');
		cache_delete('sync-names');
	}
	redirect('/catalog/'.$up);
} else {
	$config['plan'] = $plan;
}

?>