<?

w('clean');
$num = first_int(\Page::arg());
$q = db_query('SELECT * FROM store WHERE i="'.$num.'"');

if ($row = db_fetch($q)) {
	$config['row'] = $row;
} else {
	\Page::redirect('..');
}

w('store-action', $row);

if (\User::is('sync_count')) {
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