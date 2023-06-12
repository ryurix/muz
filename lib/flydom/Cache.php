<?

/*
 *	Copyright flydom.ru
 *	Version 2.3.2020-11-24
 */

namespace Flydom;

class Cache
{

const folder = 'cache/';
protected static $cache;

static function exists($key) {
	$filename = self::folder.$key.'.php';

	if (isset(self::$cache[$key])) {
		return $filename;
	}

	return is_file($filename) ? $filename : FALSE;
}

static function clear($key, $percent = 100) {
	$f = self::exists($key);

	if ($f && $percent >= rand(1, 100)) {
		@unlink($f);
		reset($key);
	}
}

static function set($key, $value) {
	$f = fopen(self::folder.$key.'.php', 'w+');
	fwrite($f, $value);
	fflush($f);
	fclose($f);
}

static function get($key, $default = NULL) {
	$f = self::exists($key);

	if ($f) {
		return file_get_contents($f);
	} else {
		return $default;
	}
}

static function delete($key) {
	self::clear($key);
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
	self::$cache[$key] = $var;

	switch ($type) {
		case 'php': self::set($key, '<?$v='.self::php_encode($var)); break;
		case 'export': self::set($key, '<?$v='.var_export($var, true)); break;
		case 'json': self::set($key, self::json_encode($var)); break;
		case 'serialize': self::set($key, serialize($var)); break;
	//	case 'wddx': self::set($key, wddx_serialize_value($var)); break;
	}

}

static function reset($key) {
	if (isset(self::$cache[$key])) {
		unset(self::$cache[$key]);
	}
}

static function reload($key, $default = NULL, $type = 'php') {
	reset($key);
	return self::load($key, $default, $type);
}

static function load($key, $default = NULL, $type = 'php') {
	if (isset(self::$cache[$key])) {
		return self::$cache[$key];
	}

	$f = self::exists($key);
	if ($f) {
		switch ($type) {
			case 'php':
			case 'export':
				include $f;
				break;
			case 'json':
				$s = file_get_contents($f);
				$v = self::json_decode($s);
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
		self::$cache[$key] = $v;
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
			$a.= ','.self::php_encode($v);
			$i++;
		}

		if (!$is_flat) {
			$a = '';
			foreach ($var as $k => $v) {
				$a.=','.self::php_encode($k).'=>'.self::php_encode($v);
			}
		}
		return '['.substr($a, 1).']';

	} elseif (is_object($var)) {
		$vars = get_object_vars($var);
		$a = '';
		foreach ($vars as $k => $v) {
			$a.=','.self::php_encode($k).'=>'.self::php_encode($v);
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
		$is_flat = TRUE;

		$a = '';
		$i = 0;
		foreach ($var as $k => $v) {
			if ($i !== $k) {
				$is_flat = FALSE;
				break;
			}
			$a.= ','.self::php_encode($v);
			$i++;
		}

		if (!$is_flat) {
			$a = '';
			foreach ($var as $k => $v) {
				$a.=','.self::php_encode($k).'=>'.self::php_encode($v);
			}
		}
		return substr($a, 1);
	} else {
		return self::php_encode($var);
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
			$a.= ','.self::json_encode($v, $number_length, $name_string);
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
	return preg_match('/^[-]?((0|[1-9][0-9]*)\\.[0-9]*[1-9]|[1-9][0-9]*|0)$/', $var);
}

} // class Cache