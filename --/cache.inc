<?

/*
 *	Copyright flydom.ru
 *	Version 1.8.2019-03-21
 */

function cache_exists($key) {
	global $cache;
	if (isset($cache[$key])) {
		return TRUE;
	}

	global $config;

	$filename = $config['root'].'cache/'.$key.'.php';
	return is_file($filename) ? $filename : FALSE;
}

function cache_clear($key, $percent = 100) {
	$f = cache_exists($key);

	if ($f && $percent >= rand(1, 100)) {
		@unlink($f);
		cache_reset($key);
	}
}

function  cache_set($key, $value) {
	global $config;

	$f = fopen($config['root'].'cache/'.$key.'.php', 'w+');
	fwrite($f, $value);
	fflush($f);
	fclose($f);
}

function cache_get($key, $default = NULL) {
	$f = cache_exists($key);

	if ($f) {
		return file_get_contents($f);
	} else {
		return $default;
	}
}

function cache_include($key) {
	$f = cache_exists($key);

	if ($f) {
		include $f;
	}
}

function cache_delete($key) {
	cache_clear($key);
}

/*

type:
php -- best LOAD, worst SAVE, minimum SIZE (!!!)
php2 -- experimental, not recommended
export -- good SAVE+LOAD, compatible with php, more SIZE
json -- best SAVE, second LOAD, best SAVE+LOAD (!!)
serialize -- third SAVE, good LOAD (!)
wddx -- second SAVE, second SAVE+LOAD, very large SIZE

*/

function cache_save($key, $var, $type = 'php') {
	global $cache;
	$cache[$key] = $var;

	switch ($type) {
		case 'php': cache_set($key, '<?$v='.php_encode($var).'?>'); break;
		case 'php2':
			global $config;
			$f = fopen($config['root'].'cache/'.$key.'.php', 'w+');
			fwrite($f, '<?$v=');
			php_encode2($var, $f);
			fwrite($f, '?>');
			fflush($f);
			fclose($f);
			break;
		case 'export': cache_set($key, '<?$v='.var_export($var, true).'?>'); break;
		case 'json': cache_set($key, json_encode($var)); break;
		case 'serialize': cache_set($key, serialize($var)); break;
//		case 'wddx': cache_set($key, wddx_serialize_value($var)); break;
	}

}

function cache_reset($key) {
	global $cache;
	if (isset($cache[$key])) {
		unset($cache[$key]);
	}
}

function cache_reload($key, $default = NULL, $type = 'php') {
	cache_reset($key);
	return cache_load($key, $default, $type);
}

function cache_load($key, $default = NULL, $type = 'php') {
	global $cache;
	if (isset($cache[$key])) {
		return $cache[$key];
	}

	$f = cache_exists($key);
	if ($f) {
		switch ($type) {
			case 'php':
			case 'export':
				include $f;
				break;
			case 'php2':
				$s = substr(file_get_contents($f), 2);
				eval(substr($s, 0, -2).';');
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
//			case 'wddx':
//				$s = file_get_contents($f);
//				$v = wddx_deserialize($s);
//				break;
		}
	}
	if (isset($v)) {
		$cache[$key] = $v;
	} else {
		$v = $default;
	}
	return $v;
}

function php_encode($var) {
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
			$a.= ','.php_encode($v);
			$i++;
		}

		if (!$is_flat) {
			$a = '';
			foreach ($var as $k => $v) {
				$a.=','.php_encode($k).'=>'.php_encode($v);
			}
		}
		return '['.substr($a, 1).']';
	} elseif (is_object($var)) {
		$vars = get_object_vars($var);
		$a = '';
		foreach ($vars as $k => $v) {
			$a.=','.php_encode($k).'=>'.php_encode($v);
		}
		return 'array('.substr($a, 1).')';
	} elseif (is_string($var)) {
		return '\''.addcslashes($var, "'\\").'\'';
	} elseif (is_null($var)) {
		return 'NULL';
	} elseif (is_bool($var) && !$var) {
		return '0';
	}
	return $var;
}

function php_decode($var) {
	try {
		return eval('return '.$var.';');
	} catch (ParseError $ex) {
		return $var;
	}
}

function array_encode($var) {
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
			$a.= ','.php_encode($v);
			$i++;
		}

		if (!$is_flat) {
			$a = '';
			foreach ($var as $k => $v) {
				$a.=','.php_encode($k).'=>'.php_encode($v);
			}
		}
		return substr($a, 1);
	} else {
		return php_encode($var);
	}
}

function array_decode($var, $default = array()) {
	try {
		return eval('return ['.$var.'];');
	} catch (ParseError $ex) {
		return $default;
	}
}

function php_encode2($var, $f) {
	if (is_array($var)) {
		$is_flat = TRUE;
fwrite($f, '[');
		$i = 0;
		$keys = array_keys($var);
		foreach ($keys as $k) {
			if ($i !== $k) {
				$is_flat = FALSE;
				break;
			}
			$i++;
		}

		$first = true;
		if (!$is_flat) {
			foreach ($var as $k => $v) {
				if (!$first) {
fwrite($f, ',');
				}
				$first = false;
				php_encode2($k, $f);
fwrite($f, '=>');
				php_encode2($v, $f);
			}
		} else {
			$first = true;
			foreach ($var as $v) {
				if (!$first) {
fwrite($f, ',');
				}
				$first = false;
				php_encode2($v, $f);
			}
		}
fwrite($f, ']');
		return;

	} elseif (is_object($var)) {
fwrite($f, '[');
		$vars = get_object_vars($var);
		$first = true;
		foreach ($var as $k => $v) {
			if (!$first) {
fwrite($f, ',');
			}
			$first = false;
			php_encode2($k, $f);
fwrite($f, '=>');
			php_encode2($v, $f);
		}
fwrite($f, ']');
		return;
	} elseif (is_string($var)) {
fwrite($f, '\''.addcslashes($var, "'\\").'\'');
		return;
	} elseif (is_null($var)) {
fwrite($f, 'NULL');
		return;
	} elseif (is_bool($var) && !$var) {
fwrite($f, '0');
		return;
	}
fwrite($f, $var);
	return;
}

/*

$chunk = cache_load('sync-chunk-test');

function ms() {
	return round(microtime(true)*1000);
}

$types = array('php', 'php2', 'export', 'json', 'serialize', 'wddx');

foreach ($types as $i) {
	echo '<h3>'.$i.'</h3>';
	$t = ms();
	cache_save('sync-chunk-'.$i, $chunk, $i);
	$save = ms() - $t;
	echo '<p>Save: '.$save.'</p>';
	$t = ms();
	$chunk2 = cache_load('sync-chunk-'.$i, $i);
	$load = ms() - $t;
	echo '<p>Load: '.$load.'</p>';
	$total = $save + $load;
	echo '<p>Total: '.$total.'</p>';
}

*/

?>