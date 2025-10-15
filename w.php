<?

function first_block($path, $body = NULL) {
	global $block;
	$base = \Config::ROOT.'w/'.$path;
	if (!is_dir($base)) { return; }
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
					$block[$name] = '$w/'.$path.$file;
					break;
				case '.inc':
					include $base.$file;
					break;
			}
		}
	}
	closedir($handle);
}

$block = [];
first_block('');

$libs = ['include', 'forms', 'types', 'input', 'mail', 'work', 'catalog', 'f-doc', 'design', 'include2'];
foreach ($libs as $i) {
	first_block($i.'/');
}

foreach (\User::roles() as $i) {
	first_block($i.'/');
}

function w($name, &$args = NULL, &$exts = NULL) {
	global $config, $block, $menu, $data;
	if(!array_key_exists($name, $block)) {
		return FALSE;
	}
	if (substr($block[$name], 0, 1) === '$') {
		$file = \Config::ROOT.substr($block[$name], 1);
		if(is_file($file)) {
			if (strpos($file, ".ph") > 0) {
				unset($block[$name]);
			}
			$back2 = include $file;
			return (is_null($back2) || $back2 === 1) && isset($back) ? $back : $back2;
		}
	}
	echo $block[$name];
}

function w2($name, $args = NULL, $exts = NULL) {
	return w($name, $args, $exts);
}

function print_pre($var, $hide = 0) {
	echo $hide ? "<!--\n" : '<pre>';
	print_r($var);
	echo $hide ? "\n-->" : '</pre>';
}

function kv($a, $i, $default = null) {
	return isset($a[$i]) ? $a[$i] : (is_null($default) ? $i : $default);
}