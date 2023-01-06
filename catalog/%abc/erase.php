<?

w('clean');
$key = first_int($config['args'][0]);

$plan = w('plan-erase');
w('request', $plan);

if ($plan['']['valid']) {
	$up = db_result('SELECT up FROM catalog WHERE i='.$key);
	if ($plan['send']['value'] == 1) {
		// $delc = 0;
		// $dels = 0;

		$ch = cache_load('children-hide');
		if (isset($ch[$key])) {
			foreach ($ch[$key] as $i) {
				db_query('DELETE FROM catalog WHERE i='.$i);
				// $delc+= db_last();
				db_query('DELETE FROM store WHERE up='.$i);
				// $dels+= db_last();
			}
		}
		w('catalog-cache');
		// alert('Раздел, подразделы ('.$delc.') и товары ('.$dels.') удалены.');
		alert('Раздел, подразделы и товары удалены.');
	}
	redirect('/catalog/'.$up);
} else {
	$config['plan'] = $plan;
}

?>