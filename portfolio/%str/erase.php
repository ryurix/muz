<?

$key = \Page::arg();

$plan = w('plan-erase');
w('request', $plan);

if ($plan['']['valid']) {
	$up = db_result('SELECT pics FROM store WHERE i='.$key);
	if ($plan['send']['value'] == 1) {
		db_query('DELETE FROM pf WHERE i='.$key);
		\Flydom\Alert::warning('Запись удалена');
	}
	\Page::redirect('/portfolio');
} else {
	$config['plan'] = $plan;
}

?>