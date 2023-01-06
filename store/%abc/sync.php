<?

w('clean');
$num = first_int($config['args'][0]);
$q = db_query('SELECT * FROM store WHERE i="'.$num.'"');

if ($row = db_fetch($q)) {
	$config['row'] = $row;
} else {
	redirect('..');
}

w('store-action', $row);

if (is_user('sync')) {
	if (isset($_REQUEST['code'])) {
		$code = clean_int($_REQUEST['code']);
		if ($code > 0) {
			db_delete('sync', [
				'i'=>$code,
				'store'=>$row['i'],
			]);
		}
	}
}