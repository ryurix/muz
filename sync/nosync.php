<?

if (isset($_REQUEST['code'])) {
	w('clean');
	$store = 0;
	$code = clean_int($_REQUEST['code']);
	if ($code > 0) {
		db_delete('sync', array(
			'i'=>$code,
			'store'=>$store,
		));
	}
}

?>