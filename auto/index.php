<?

if (!is_user()) {
	$pass = kv($_REQUEST, 'password', '');

	$q = db_query('SELECT * FROM user WHERE pass="'.addslashes($pass).'" AND roles LIKE "%auto%"');
	if ($user = db_fetch($q)) {
		access_try($user['login'], $user['pass']);
	}
}

if (!is_user()) {
	$config['design'] = 'mobile';
	rebody('login');
}

if (is_user('auto')) {
	$config['design'] = 'mobile';
}

?>