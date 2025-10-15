<?

if (\User::is()) {
	if ((\Config::now() - $_SESSION['last']) > 60*60) {
		$_SESSION['last'] = \Config::now();
		db_update('user', array('last'=>\Config::now()), array('i'=>$_SESSION['i']));
	}
}