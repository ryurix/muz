<?php

class User
{

static function login() { return $_SESSION['login'] ?? ''; }

static function is($role = null) {
	static::startup();

	if (empty($role)) {
		return static::id() > 0;
	}

	if (is_null(static::$roles)) {
		static::$roles = explode(' ', $_SESSION['roles'] ?? 'guest');
	}

	if (!is_array($role)) {
		$role = explode(' ', $role);
	}

	return count(array_intersect($role, static::$roles)) > 0;
}

static function startup() {
	if (static::$started) {
		return;
	}
	static::$started = true;

    if (\Page::isConsole()) {
		$_SESSION = [
			'i'=>1,
        	'roles' => ['console']
		];
        return;
    }

	@session_start();
	if (isset($_POST['_login']) && isset($_POST['_password'])) {
		$basket = isset($_SESSION['basket']) ? $_SESSION['basket'] : null;
		if (!static::try($_POST['_login'], $_POST['_password'])) {
			static::$wrongPassword = true;
			$alert = $_SESSION['alert'] ?? null;
		}
	} elseif (($_SESSION['i'] ?? 0) && isset($_REQUEST['_exit'])) {
		static::exit();
	}
	if (!isset($_SESSION['i'])) {
		\Flydom\Core\Session::new();
		static::create();
	}
	if (isset($alert) && is_array($alert)) {
		$_SESSION['alert'] = $alert;
	}
	if (isset($basket) && is_array($basket)) {
		$_SESSION['basket'] = $basket;
	}
}

static function try($login, $password) {
	static::startup();

	$login = trim($login);
	$password = trim($password);

	$timeout = 5;

	$login = addslashes($login);
	$user = \Db::fetchRow('SELECT * FROM user WHERE login="'.$login.'"');

	if (is_null($user)) {
		\Flydom\Alert::danger('Пользователь не найден!');
	} else {
		if (((time() - $user['dry']) < $timeout*60) && ($user['try'] >= 3)) {
			\Flydom\Alert::danger('Слишком много попыток ввода пароля, подождите '.($timeout - floor((time() - $user['dry'])/60)).' мин.');
		} elseif ($user['pass'] == $password) {
			static::exit(false);
			static::create($user);
			return TRUE;
		} else {
			\Flydom\Alert::danger('Пароль неверный!');
			\Db::query('UPDATE user SET try='.((time() - $user['dry']) < $timeout*60 ? 'try+' : '').'1, dry='.time().' WHERE i='.$user['i']);
			\Flydom\Log::add(\Flydom\Type\Log::PASSWORD, 0, static::ip().'|'.$login.':'.$password);
		}
	}

	return false;
}

static function exit($guest = true) {
	static::startup();

	\Flydom\Core\Session::new();
	if ($_SESSION['i'] ?? 0) {
		\Db::delete('session', ['usr'=>$_SESSION['i']]);
	} else {
		\Db::delete('session', ['usr'=>0, 'i'=>static::session32()]);
	}

	if ($guest) {
		static::create();
	}
}

static function ip(): string {
	static $ip = NULL;
	if (is_null($ip)) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

static function session32() {
	return crc32(session_id());
}

protected static function create($user = null) {
	if (is_null($user)) {
		$user = static::guest();
	} else {
		\Flydom\Log::add(\Flydom\Type\Log::LOGIN, 0, static::ip(), $user['i']);
	}

	$user['ip'] = static::ip();

	static::$roles = explode(' ', $user['roles']); //is_array($user['roles']) ? $user['roles'] : explode(' ', $user['roles']);

	$exclude = ['pass'=>0, 'try'=>0,'dry'=>0];
	$_SESSION = array_diff_key($user, $exclude);
}

protected static function guest() {
	return [
		'i'=>0,
		'roles'=>'guest',
	];
}

// * * * properties

static function id() { static::startup(); return $_SESSION['i'] ?? 0; }
protected static $roles;
static function roles($list = null) {
	if (is_null($list)) {
		return self::$roles ?? [];
	} else {
		self::$roles = is_array($list) ? $list : explode(' ', $list);
		$_SESSION['roles'] = is_array($list) ? implode(' ', $list) : $list;
	}
}

protected static $started = false;
protected static $wrongPassword = false;
static function isWrongPassword() { return static::$wrongPassword; }

} // class User