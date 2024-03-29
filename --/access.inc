<?php

/*
 *	Copyright flydom.ru
 *	Version 2.6.2019-01-20
 */

function is_user($role = '') {
	if (!isset($_SESSION['i']) || $_SESSION['i'] == 0) {return FALSE;}
	return $role == '' ? TRUE : in_array($role, $_SESSION['roles']);
}

function is_staff() {
	return is_user() && count($_SESSION['roles']);
}

function access_login() {
	global $config, $user;

	@session_start();
	if (isset($_POST['_login']) && isset($_POST['_pass'])) {
		$basket = isset($_SESSION['basket']) ? $_SESSION['basket'] : NULL;
		if (!access_try($_POST['_login'], $_POST['_pass'])) {
			$config['password_forgotten'] = TRUE;
			$alert = isset($_SESSION['alert']) ? $_SESSION['alert'] : NULL;
		}
	} elseif (kv($_SESSION, 'i', 0) && isset($_REQUEST['-exit'])) {
		access_exit();
		access_create($user[0]);
	}
	if (!isset($_SESSION['i'])) {
		$config['new-session'] = 1;
		access_create($user[0]);
	}
	if (isset($alert) && is_array($alert)) {
		$_SESSION['alert'] = $alert;
	}
	if (isset($basket) && is_array($basket)) {
		$_SESSION['basket'] = $basket;
	}
}

function access_exit() {
	global $config;
	$config['new-session'] = 1;
	if (kv($_SESSION, 'i', 0)) {
		db_delete('session', array('usr'=>$_SESSION['i']));
	} else {
		db_delete('session', array('usr'=>0, 'i'=>crc32(session_id())));
	}
}

function access_create($user, $keep = 1) {
	if ($user['i']) {
		db_insert('log', array(
			'type'=>1,
			'dt'=>now(),
			'user'=>$user['i'],
			'info'=>$user['login'].' '.client_ip(),
		));
	}

	$user['ip'] = client_ip();

	$exclude = array('pass'=>0, 'try'=>0, 'dry'=>0, 'quick'=>0);
	$_SESSION = array_diff_key($user, $exclude);
}

function access_try($login, $pass) {
	global $config;
//	$pass = md5($pass);

	$login = trim($login);
	$pass = trim($pass);

	$timeout = 5;
	if (isset($config['database'])) {
		$where = array();
		$email = preg_replace('@[^0-9a-zA-Z_\-\.\+\^!#\@\$%&*+\/\=\?\`\|\{\}~\']+@', '', $login);
		$email = strtolower($email);
		if (strlen($email) && strpos($email, '@')) {
			$where[]= 'email="'.$email.'"';
		}
		$phone = preg_replace('@[^0-9]+@', '', $login);
		if (strlen($phone) > 10) {
			if (substr($phone, 0, 1) == '8') {
				$phone = '7'.substr($phone, 1);
			}
			$where[] = 'phone="'.$phone.'"';
		}
		$login = addslashes($login);
		if (count($where) == 0) {
			$where[] = '1=0';
		}
		$q = db_query('SELECT * FROM user WHERE '.implode(' OR ', $where));

		if ($i = db_fetch($q)) {
			if (((now() - $i['dry']) < $timeout*60) && ($i['try'] >= 3)) {
				alert('Слишком много попыток ввода пароля, подождите '.($timeout - floor((now() - $i['dry'])/60)).' мин.', 'danger');
			} elseif ($i['pass'] == $pass) {
				$i['roles'] = strlen($i['roles']) ? explode(" ", $i['roles']) : array();
				access_exit();
				access_create($i);
				return TRUE;
			} else {
				alert('Пароль неверный!', 'danger');
				db_query('UPDATE user SET try='.((now() - $i['dry']) < $timeout*60 ? 'try+' : '').'1, dry='.now().' WHERE i='.$i['i']);
			}
		} else {
			alert('Пользователь не найден!', 'danger');
		}

		if (strlen($pass)) {
			db_insert('log', array('user'=>-1, 'dt'=>now(), 'info'=>client_ip().'@'.$login.':'.$pass));
		}
	}

	return FALSE;
}

function remove_role($role, $roles) {
	$a = array();
	foreach($roles as $k=>$v) {
		if ($v !== $role) {
			$a[$k] = $v;
		}
	}
	return $a;
}

//	****************************************************************************

function access_open($path, $name) {
	return true;
}

function access_close() {
	return true;
}

function access_read($key) {
	$q = db_query('SELECT * FROM session WHERE i='.crc32($key));
	while ($i = db_fetch($q)) {
		if ($i['id'] == $key) {
			return $i['data'];
		}
	}
	return '';
}

function access_write($key, $data) {
	global $config;
	if (kv($config, 'new-session', 0)) {
		if ($config['new-session'] < 100) {
			$ip = client_ip();
			$uid = $_SESSION['i'];

			if (!$uid) {
				$ban = cache_load('ip-ban', []);

				$parts = explode('.', $ip);
				$parts[3] = '*';
				$one = implode('.', $parts);
				$parts[2] = '*';
				$two = implode('.', $parts);

				if (in_array($ip, $ban) || in_array($one, $ban) || in_array($two, $ban)) {
					// header('HTTP/1.0 403 Forbidden', true, 403);
					exit;
				}
			}

			db_insert('session', array(
				'i'=>crc32($key),
				'id'=>$key,
				'ip'=>$ip,
				'dt'=>now(),
				'data'=>$data,
				'usr'=>$uid,
			));
		}
	} else {
		db_update('session', array(
			'dt'=>now(),
			'data'=>$data,
		), array(
			'i'=>crc32($key),
			'id'=>$key,
			'usr'=>$_SESSION['i'],
		));
	}
	return true;
}

function access_destroy($sess) {
	return true;
}

function access_gc($life) {
	return 0;
}

session_set_save_handler('access_open', 'access_close', 'access_read', 'access_write', 'access_destroy', 'access_gc');


?>