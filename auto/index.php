<?

if (!\User::is()) {
	$pass = kv($_REQUEST, 'password', '');

	$q = db_query('SELECT * FROM user WHERE pass="'.addslashes($pass).'" AND roles LIKE "%auto%"');
	if ($user = db_fetch($q)) {
		\User::try($user['login'], $user['pass']);
	}
}

if (!\User::is()) {
	\Page::design('mobile');
	\Page::body('login');
}

if (\User::is('auto')) {
	\Page::design('mobile');
}