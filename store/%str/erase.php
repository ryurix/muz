<?

w('clean');
$key = first_int(\Page::arg());

$plan = w('plan-erase');
w('request', $plan);

if ($plan['']['valid']) {
	$up = db_result('SELECT up FROM store WHERE i='.$key);
	if ($plan['send']['value'] == 1) {
		db_query('DELETE FROM store WHERE i='.$key);
		db_query('DELETE FROM sync WHERE store='.$key);
		\Flydom\Alert::warning('Товар удалён');
		cache_delete('sync-chunk');
		cache_delete('sync-names');
	}
	\Page::redirect('/catalog/'.$up);
} else {
	$config['plan'] = $plan;
}

?>