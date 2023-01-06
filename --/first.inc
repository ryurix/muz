<?

/*
 *	Copyright flydom.ru
 *	Version 3.1.2018-09-07
 */

function first_query() {
	global $config;

	if (isset($_GET['q']) && is_string($_GET['q'])) {
		$q = $_GET['q'];
	}
	elseif ($_SERVER['REQUEST_URI'] == '/index.php') {
		redirect('/');
	}
	elseif (isset($_SERVER['REQUEST_URI'])) {
		$request_path = strtok($_SERVER['REQUEST_URI'], '?');
		$base_path_len = strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/'));
		$q = substr(urldecode($request_path), $base_path_len + 1);
		if ($q == basename($_SERVER['PHP_SELF'])) {
			$q = '';
		}
	}
	else {
		$q = '';
	}

	if (strlen($q) > 1 && trim($q, '/') == '') {
		redirect('/');
	}

	//$q = preg_replace("!(\s*)(?:\.html?|\.php)$!", "$1", $q);
	//$q = preg_replace("!//+!", "/", "/$q");

	return '/'.$q;
}

function first_menu($q, $roles, &$menu, &$config) {
	$menu[0]['here'] = TRUE;
	$menu[0]['href'] = '';

	$design = $config['design'];
	$name = $menu[0]['name'];
	$args = array();
	$path = array();
	$ins = array();

	$q = $q == '/' ? $config['front'] : $q;
	$junk = explode('/', $q);
	$junk = array_filter($junk, function($value) { return $value !== ''; });
	$junk = array_values($junk);

//	print_pre($junk);
//	exit();

	$level = array(&$menu[0]);
	while (count($level)) {

		$level2 = array();
		$step = count($junk) ? $junk[0] : NULL;
		foreach (array_keys($level) as $key) {
			$pre = &$level[$key];
			$son = &$pre['/'];

			$here = isset($pre['here']) ? $step : NULL;
			if ($here !== NULL && strlen($here)) {
				if (isset($son[$here])) {
					$son[$here]['code'] = $here;
					$son[$here]['here'] = TRUE;
				} else {
					if (isset($son['%num']) && preg_match('/^[0-9]+$/', $here)) {
						$args[] = $here;
						$son['%num']['code'] = $here;
						$son['%num']['here'] = TRUE;
					} else if (isset($son['%abc'])) {
						$args[] = $here;
						$son['%abc']['code'] = $here;
						$son['%abc']['here'] = TRUE;
					}
				}
			}

			$unset = array();
			foreach(array_keys($son) as $code) {
				$i = &$son[$code];
				if (isset($i['role'])) {
					$role = explode(' ', $i['role']);
					if (count(array_intersect($role, $roles)) == 0) {
						$unset[] = $code;
						continue;
					}
				}

				if (!isset($i['name'])) {
					$i['name'] = $pre['name'];
				}

				if (isset($i['here']) && $i['here']) {
					$name = $i['name'];
					if (isset($i['lib'])) { $config['libs'][] = $i['lib']; }
					if (isset($i['design'])) { $design = $i['design']; }
					if (isset($i['virtual']) && $i['virtual']) { $args[] = $here; }
					else { $path[] = $code; }

					$code = $i['code'];
					$menu[] = &$i;
					array_shift($junk);
				} else {
					if ($code == '%num' || $code == '%abc') {
						$unset[] = $code;
						continue;
					} else {
						$i['code'] = $code;
					}
				}

				if (!isset($i['href'])) {$i['href'] = $pre['href'].'/'.$code;}

				if (isset($i['in'])) {$ins[$i['in']][] = &$i;}
				if (isset($i['/'])) {$level2[] = &$i;}
			}
			foreach($unset as $j) {
				unset($son[$j]);
			}
		}
		$level = $level2;
	}
	$menu[0]['href'] = '/';

	$config['design'] = $design;
	$config['name'] = $name;
	$config['args'] = $args;
	$config['junk'] = $junk;
	$config['path'] = $path;
	$config['ins'] = $ins;
}

function first_file($path, &$file) {
	global $config;

	$file = $config['root'].implode('/', $path);
 	$body = is_dir($file) ? 'index' : array_pop($path);
	$path = count($path) > 0 ? implode('/', $path).'/' : 'home/';
	$file = $config['root'].$path.$body;

	return '$'.$path.$body.(is_file($file.'.htm') ? '.htm' : '.html');
}

function first_body($force = 0) {
	global $config, $block;

	if (!array_key_exists('body', $block) || $force) {
		$block['body'] = first_file($config['path'], $file);

		if (is_file($file.'.php')) {
			global $menu;
			include_once $file.'.php';
		}
	}
}

function first_block($path, $body = NULL) {
	global $config, $block;
	$base = $config['root'].'--/block/'.$path;
	$handle = @opendir($base);
	if ($handle === FALSE) { return; }

	while($file = readdir($handle)) {
		if ($file[0] === '.') {
			continue;
		}
		if (is_file($base.$file)) {
			$ex = strrchr($file, ".");
			$name = substr($file, 0, -strlen($ex));
			switch($ex) {
				case '.php':
					if ($body === $name) {
						include_once $base.$file;
						break;
					}
				case '.htm':
				case '.html':
					$block[$name] = '$--/block/'.$path.$file;
					break;
				case '.inc':
					include $base.$file;
					break;
			}
		}
	}
	closedir($handle);
}

function w($name, &$args = NULL, &$exts = NULL) {
	global $config, $block, $menu, $data;
	if(!array_key_exists($name, $block)) {
		return FALSE;
	}
	if (substr($block[$name], 0, 1) === '$') {
		$file = $config['root'].substr($block[$name], 1);
		if(is_file($file)) {
			$back = NULL;
			if (strpos($file, ".ph") > 0) {
				unset($block[$name]);
			}
			include $file;
			return $back;
		}
	}
	echo $block[$name];
}

function w2($name, $args = NULL, $exts = NULL) {
	return w($name, $args, $exts);
}

function client_ip() {
	static $ip = NULL;
	if (is_null($ip)) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

function redirect($where, $code = 302) {
	if ($where == 404) {
		$where = '/404';
		$code = 404;
	}
	switch ($code) {
		case 301: $info = 'Moved Permanently'; break;
		case 302: $info = 'Found'; break;
		case 303: $info = 'See Other'; break;
		case 304: $info = 'Not Modified'; break;
		case 305: $info = 'Use Proxy'; break;
		case 307: $info = 'Temporary Redirect'; break;
		case 404: $info = 'Not Found'; break;
		default: $info = ''; break;
	}
	header("HTTP/1.1 $code $info");
	header('Location: '.$where);
	exit();
}

function rebody($where = '') {
	global $config, $block;

	if ($where == '') {
		array_pop($config['path']);
	} else {
		if (strpos($block['body'], '/index.') > 0) {
			$config['path'][] = $where;
		} else {
			$config['path'][count($config['path']) - 1] = $where;
		}
	}

	first_body($force = 1);
}

function refile($filename, $blockname = 'body') {
	global $block;
	$body = $block['body'];
	$path = substr($body, 0, strrpos($body, '/'));
	$block[$blockname] = $path.'/'.$filename;
}

function move($file1, $file2, $copy = false) {
	if (file_exists($file1)) {
		if (!file_exists(dirname($file2))) {
			@mkdir(dirname($file2));
		} elseif (file_exists($file2)) {
			@unlink($file2);
		}
		if ($copy) {
			@copy($file1, $file2);
		} else {
			@rename($file1, $file2);
		}
	}
}

function xcopy($from, $to) {
	if (is_dir($from)) {
		if (!file_exists($to)) {
			@mkdir($to);
		}
		$dir = dir($from);
		while (FALSE !== ($i = $dir->read())) {
			if ($i == '.' || $i == '..') continue;
			if (is_dir($from.'/'.$i)) {
				xcopy("$from/$i", "$to/$i");
			} else {
				copy("$from/$i", "$to/$i");
			}
		}
	} else {
		if (!file_exists(dirname($to))) {
			@mkdir(dirname($to));
		}
		copy($from, $to);
	}
}

function now() {
	global $config;
	return time() + $config['GMT']*60*60;
}

function alert($text, $type = 0) {
	$i = array('text'=>$text, 'type'=>$type);
	if (isset($_SESSION['alert'])) {
		$_SESSION['alert'][] = $i;
	} else {
		$_SESSION['alert'] = array($i);
	}
}

function print_pre($var, $hide = 0) {
	echo $hide ? "<!--\n" : '<pre>';
	print_r($var);
	echo $hide ? "\n-->" : '</pre>';
}

function kv($a, $i, $default = null) {
	return isset($a[$i]) ? $a[$i] : (is_null($default) ? $i : $default);
}

?>