<?

/*
 *	Copyright flydom.ru
 *	Version 2.3.2020-11-24
 */

namespace Flydom;

class Cache
{

const folder = 'cache/';

static function exists($key) {
	global $cache, $config;

	$filename = self::folder.$key.'.php';

	if (isset($cache[$key])) {
		return $filename;
	}

	return is_file($filename) ? $filename : FALSE;
}

static function clear($key, $percent = 100) {
	$f = Cache::exists($key);

	if ($f && $percent >= rand(1, 100)) {
		@unlink($f);
		reset($key);
	}
}

static function set($key, $value) {
	global $config;

	$f = fopen(Cache::folder.$key.'.php', 'w+');
	fwrite($f, $value);
	fflush($f);
	fclose($f);
}

static function get($key, $default = NULL) {
	$f = Cache::exists($key);

	if ($f) {
		return file_get_contents($f);
	} else {
		return $default;
	}
}

static function delete($key) {
	Cache::clear($key);
}

/*

type:
php -- best LOAD, worst SAVE, minimum SIZE (!!!)
export -- good SAVE+LOAD, compatible with php, more SIZE
json -- second LOAD, best SAVE, best SAVE+LOAD (!!)
serialize -- good LOAD, third SAVE (!)
wddx -- second SAVE, second SAVE+LOAD, very large SIZE

*/

static function save($key, $var, $type = 'php') {
	global $cache;
	$cache[$key] = $var;

	switch ($type) {
		case 'php': self::set($key, '<?$v='.Cache::php_encode($var)); break;
		case 'export': self::set($key, '<?$v='.var_export($var, true)); break;
		case 'json': self::set($key, json_encode($var)); break;
		case 'serialize': self::set($key, serialize($var)); break;
	//	case 'wddx': self::set($key, wddx_serialize_value($var)); break;
	}

}

static function reset($key) {
	global $cache;
	if (isset($cache[$key])) {
		unset($cache[$key]);
	}
}

static function reload($key, $default = NULL, $type = 'php') {
	reset($key);
	return self::load($key, $default, $type);
}

static function load($key, $default = NULL, $type = 'php') {
	global $cache;
	if (isset($cache[$key])) {
		return $cache[$key];
	}

	$f = Cache::exists($key);
	if ($f) {
		switch ($type) {
			case 'php':
			case 'export':
				include $f;
				break;
			case 'json':
				$s = file_get_contents($f);
				$v = json_decode($s);
				if (is_object($v)) {
					$v = get_object_vars($v);
				}
				break;
			case 'serialize':
				$s = file_get_contents($f);
				$v = unserialize($s);
				break;
			//case 'wddx':
			//	$s = file_get_contents($f);
			//	$v = wddx_deserialize($s);
			//	break;
		}
	}
	if (isset($v)) {
		$cache[$key] = $v;
	} else {
		$v = $default;
	}
	return $v;
}

static function php_encode($var) {
	if (is_array($var)) {
		$count = count($var);
		if ($count == 0) { return '[]'; }

		$is_flat = TRUE;

		$a = '';
		$i = 0;
		foreach ($var as $k => $v) {
			if ($i !== $k) {
				$is_flat = FALSE;
				break;
			}
			$a.= ','.Cache::php_encode($v);
			$i++;
		}

		if (!$is_flat) {
			$a = '';
			foreach ($var as $k => $v) {
				$a.=','.Cache::php_encode($k).'=>'.Cache::php_encode($v);
			}
		}
		return '['.substr($a, 1).']';

	} elseif (is_object($var)) {
		$vars = get_object_vars($var);
		$a = '';
		foreach ($vars as $k => $v) {
			$a.=','.Cache::php_encode($k).'=>'.Cache::php_encode($v);
		}
		return '['.substr($a, 1).']';
	} elseif (is_string($var)) {
		return '\''.addcslashes($var, "'\\").'\'';
	} elseif (is_null($var)) {
		return 'NULL';
	} elseif (is_bool($var) && !$var) {
		return '0';
	}
	return $var;
}

static function php_decode($var) {
	try {
		return eval('return '.$var.';');
	} catch (\ParseError $ex) {
		return $var;
	}
}

static function array_encode($var) {
	if (is_array($var)) {
		$count = count($var);
		$is_flat = TRUE;

		$a = '';
		$i = 0;
		foreach ($var as $k => $v) {
			if ($i !== $k) {
				$is_flat = FALSE;
				break;
			}
			$a.= ','.Cache::php_encode($v);
			$i++;
		}

		if (!$is_flat) {
			$a = '';
			foreach ($var as $k => $v) {
				$a.=','.Cache::php_encode($k).'=>'.Cache::php_encode($v);
			}
		}
		return substr($a, 1);
	} else {
		return Cache::php_encode($var);
	}
}

static function array_decode($var) {
	try {
		return eval('return ['.$var.'];');
	} catch (\ParseError $ex) {
		return $var;
	}
}

static function json_encode($var, $number_length = 10, $name_string = true) {
	if (is_array($var)) {
		$count = count($var);
		if ($count == 0) { return '[]'; }

		$is_flat = TRUE;

		$a = '';
		$i = 0;
		foreach ($var as $k => $v) {
			if ($i !== $k) {
				$is_flat = FALSE;
				break;
			}
			$a.= ','.self::json_encode($v, $number_length, $name_string, );
			$i++;
		}

		if ($is_flat) {
			return '['.substr($a, 1).']';
		} else {
			$a = '';
			foreach ($var as $k => $v) {
				$a.=','.(!$name_string && self::is_name($k) ? $k : '"'.addcslashes($k, '"\\').'"')
				.':'.self::json_encode($v, $number_length, $name_string);
			}
			return '{'.substr($a, 1).'}';
		}

	} elseif (is_object($var)) {
		$vars = get_object_vars($var);
		$a = '';
		foreach ($vars as $k => $v) {
			$a.=','.(!$name_string && self::is_name($k) ? $k : '"'.addcslashes($k, '"\\').'"')
			.':'.self::json_encode($v, $number_length, $name_string);
		}
		return '{'.substr($a, 1).'}';
	} elseif (self::is_number($var, $number_length)) {
		return $var;
	} elseif (is_null($var)) {
		return 'null';
	} elseif (is_bool($var)) {
		return $var ? 'true' : 'false';
	}
	return '"'.addcslashes($var, '"\\').'"';
}

static function json_decode($json) {
	return json_decode($json, true, 512, JSON_BIGINT_AS_STRING);
}

static function is_name($var) {
	return preg_match('?^[a-zA-Z_][a-zA-Z0-9_]*$?', $var);
}

static function is_number($var, $number_length = 10) {
	if (strlen($var) > $number_length) {
		return false;
	}
	return preg_match('/^[-]?((0|[1-9][0-9]*)\\.[0-9]*[1-9]|[1-9][0-9]*)$/', $var);
}

} // class Cache