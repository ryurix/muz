<?

function get_user_config($name, $default) {
	if (isset($_SESSION['config']) && isset($_SESSION['config'][$name])) {
		return $_SESSION['config'][$name];
	}
	return $default;
}

function set_user_config($name, $value) {
	if (!isset($_SESSION['config']) || !is_array($_SESSION['config'])) {
		$_SESSION['config'] = [];
	}

	if (isset($_SESSION['config'][$name])) {
		if (is_array($value) || is_object($value)) {
			if (php_encode($_SESSION['config'][$name]) != php_encode($value)) {
				$_SESSION['config'][$name] = $value;
				save_user_config();
			}
		} else {
			if ($_SESSION['config'][$name] != $value) {
				$_SESSION['config'][$name] = $value;
				save_user_config();
			}
		}
	} else {
		$_SESSION['config'][$name] = $value;
		save_user_config();
	}
}

function load_user_config() {
	if (isset($_SESSION['config']) && !is_array($_SESSION['config'])) {
		$_SESSION['config'] = array_decode($_SESSION['config']);
	}
}

function save_user_config() {
	if ($_SESSION['i'] ?? 0) {
		db_update('user', array('config'=>array_encode($_SESSION['config'])), array('i'=>$_SESSION['i']));
	}
}

load_user_config();

function set_array(&$plan, $field, $values) {
	$keys = array_keys($plan);
	foreach ($keys as $k) {
		if (isset($values[$k])) {
			$plan[$k][$field] = $values[$k];
		}
	}
}