<?

if (is_user()) {
	if ((now() - $_SESSION['last']) > 60*60) {
		$_SESSION['last'] = now();
		db_update('user', array('last'=>now()), array('i'=>$_SESSION['i']));
	}
}

?>