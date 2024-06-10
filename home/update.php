<?

/* not send */
$exclude = ['/^custom$/', 'files', 'cache', 'design/logo.png', 'config.php'
	, 'design/img', 'doc/template', 'design/brands', 'design/catalog', 'design/images'
	,'/^config.inc$/', '/^cgi-bin$/', '/^user.anonymous.json$'
	, '/[^\\/]+\\[/', '/^_.*$/', '/^\\.htaccess$/' , '/^https.*$/'
	, '/^https.inc$/', '/\\.cw\\.dat$/', '/\\.cw127\\.php$/', '/\\.mtx\\.php$/', '/\\.sql\\.gz/'
];

/* not get */
$not_get = ['/^custom$/', 'files', 'cache', 'design/logo.png', 'config.php'
	, 'design/img', 'doc/template', 'design/brands', 'design/catalog', 'design/images'
	,'/^config.inc$/', '/^cgi-bin$/'
	, '/[^\\/]+\\[/', '/^_.*$/', '/^\\.htaccess$/' , '/^https.*$/'
	, '/^https.inc$/', '/\\.cw\\.dat$/', '/\\.cw127\\.php$/', '/\\.mtx\\.php$/', '/\\.sql\\.gz/'
];

$upd_pass = 'MusicMart01';

$DEBUG = !isset($_REQUEST['yes']);

if (!function_exists('scan_dir')) {

function scan_dir($root, $path, $exclude, &$a) {
	$scan = scandir($root.$path);
	foreach ($scan as $v) {
		if (mb_substr($v, 0, 1) != '.') {
			$fullname = $path.'/'.$v;

			$skip = FALSE;
			foreach ($exclude as $ex) {
				if (substr($ex, 0, 1) == '/') {
					if (preg_match($ex, $v)) {
						$skip = TRUE;
					}
				} else {
					if ($fullname == '/'.$ex) {
						$skip = TRUE;
					}
				}
			}
			if (!$skip) {
				if (is_dir($root.$fullname)) {
					$a[$fullname] = -1;
					scan_dir($root, $fullname, $exclude, $a);
				}
				if (is_file($root.$fullname)) {
					$a[$fullname] = filesize($root.$fullname);
				}
			}
		}
	}

	return $a;
}

// * * * * *

function upd_scan($url, $pass, $exclude) {
	$post = array(
		'pass'=>$pass,
		'cmd'=>'scan',
		'arg'=>php_encode($exclude),
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$result = curl_exec($ch);
	curl_close($ch);

	if (isset($_REQUEST['debug'])) {
		var_dump($result);
	}

	return eval('return '.$result.';');
}

function upd_del($url, $pass, $filename) {
	$post = array(
		'pass'=>$pass,
		'cmd'=>'del',
		'arg'=>$filename,
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function upd_file($url, $pass, $filename) {
	global $config;

	$fullname = rtrim($config['root'], '/').$filename;
	if (function_exists('curl_file_create')) {
		$f = curl_file_create($fullname);
	} else {
		$f = '@'.$fullname;
	}

	$post = array(
		'pass'=>$pass,
		'cmd'=>'file',
		'arg'=>$filename,
		'file'=>$f,
	);
//*
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$result = curl_exec($ch);
	curl_close($ch);
//*/
	return $result;
}

function upd_dir($url, $pass, $filename) {
	$post = array(
		'pass'=>$pass,
		'cmd'=>'dir',
		'arg'=>$filename,
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function get_dir($url, $pass, $filename) {
	global $config;
	$f = $config['root'].$filename;
	if (!is_dir($f)) {
		mkdir($f);
	}
	return $filename;
}

/*
function get_file($url, $pass, $filename) {
	global $config;
	$post = array(
		'pass'=>$pass,
		'cmd'=>'get',
		'arg'=>$filename,
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$result = curl_exec($ch);
	curl_close($ch);
	file_put_contents($config['root'].$filename, $result);
	return $filename;
}
//*/

function get_file($url, $pass, $filename) {
	global $config;

	$post = array(
		'pass'=>$pass,
		'cmd'=>'get',
		'arg'=>$filename,
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$f = fopen($config['root'].$filename, 'w+');
	curl_setopt($ch, CURLOPT_FILE, $f);
	curl_exec($ch);
	curl_close($ch);
	fclose($f);

	return $filename;
}

function get_del($url, $pass, $filename) {
	global $config;
	$filename = $config['root'].$filename;
	if (is_dir($filename)) {
		rmdir($filename);
	} else {
		unlink($filename);
	}
	return $filename;
}

} // if function_exists

// * * * * *

//*

$plan = [
	'file'=>['type'=>'file0'],
	'cmd'=>['type'=>'line'],
	'arg'=>['type'=>'line'],
	'pass'=>['type'=>'line'],
];

$plan = w('request', $plan);

if ($plan['pass']['value'] == $upd_pass) {
	$config['design'] = 'none';

	if ($plan['cmd']['value'] == 'scan') {
		$ex = eval('return '.$plan['arg']['value'].';');
		$a = [];
		scan_dir(rtrim($config['root'], '/'), '', $ex, $a);

		if (isset($_REQUEST['cache'])) {
			file_put_contents($config['root'].'files/scan.txt', php_encode($a));
		} else {
			echo php_encode($a);
		}
		exit;
	}

	if ($plan['cmd']['value'] == 'del') {
		$filename = $config['root'].$plan['arg']['value'];
		if (is_dir($filename)) {
/**/		rmdir($filename);
		} else {
			if (is_file($filename)) {
/**/			unlink($filename);
			}
		}
		echo 'del: '.$plan['arg']['value'];
	}

	if ($plan['cmd']['value'] == 'file') {
		if (is_file($plan['file']['value'])) {
/**/		xcopy($plan['file']['value'], $config['root'].$plan['arg']['value']);
			echo 'file: '.$plan['arg']['value'];
		} else {
			echo 'file not found';
		}
	}

	if ($plan['cmd']['value'] == 'dir') {
/**/	mkdir($config['root'].$plan['arg']['value']);
		echo 'dir: '.$plan['arg']['value'];
	}

	if ($plan['cmd']['value'] == 'get') {
		$filename = $config['root'].$plan['arg']['value'];
		if (is_file($filename)) {
			header('Content-Type: "application/octet-stream"');
			header('Content-Disposition: attachment; filename="'.basename($filename).'"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');

			$f = file_get_contents($filename);
			echo $f;
			exit;
		}
	}

	$body = '';
}

// */

// * * * * *

//*

if (isset($config['update-url']) && isset($_REQUEST['clone'])) { // клонируем

	$report = array('<div class="alert alert-success">Update <a href="'.$config['update-url'].'">'.$config['update-url'].'</a></div>');

	set_time_limit(0);

	$tut = array();
	scan_dir(rtrim($config['root'], '/'), '', $not_get, $tut);

	if (cache_exists('clone') && isset($_REQUEST['cache'])) {
		$tam = cache_load('clone');
	} else {
		$tam = upd_scan($config['update-url'].'/update', $upd_pass, $not_get);
		cache_save('clone', $tam);
	}

	// Загрузка новых файлов
	$miss = array_diff_key($tam, $tut);
	foreach ($miss as $k=>$v) {
		if ($v < 0) {
			$s = $DEBUG ? $k : get_dir($config['update-url'].'/update', $upd_pass, $k);
			$report[] = '<div class="alert alert-success">Директория создана: '.$s.'</div>';
		} else {
			$s = $DEBUG ? $k : get_file($config['update-url'].'/update', $upd_pass, $k);
			$report[] = '<div class="alert alert-success">Получен: '.$s.'</div>';
		}
	}

	// Обновление изменённых файлов
	$same = array_intersect_key($tam, $tut);
	foreach ($same as $k=>$v) {
		if ($tut[$k] != $v) {
			$s = $DEBUG ? $k : get_file($config['update-url'].'/update', $upd_pass, $k);
			$report[] = '<div class="alert alert-warning">Обновлён '.$s.'</div>';
		}
	}

	// Очистка лишних файлов
	$more = array_diff_key($tut, $tam);
	foreach ($more as $k=>$v) {
		$s = $DEBUG ? $k : get_del($config['update-url'].'/update', $upd_pass, $k);
		$report[] = '<div class="alert alert-danger">Удалён '.$k.'</div>';
	}

	$body = implode("\n", $report);

} elseif (isset($config['update-url']) && isset($_REQUEST['send'])) {
	cache_save('update-on', TRUE);

	$report = array('<div class="alert alert-success">Update <a href="'.$config['update-url'].'">'.$config['update-url'].'</a></div>');

	set_time_limit(0);

	$tut = array();
	scan_dir(rtrim($config['root'], '/'), '', $exclude, $tut);
	$tam = upd_scan($config['update-url'].'/update', $upd_pass, $not_get);

	// Загрузка новых файлов
	$miss = array_diff_key($tut, $tam);
	foreach ($miss as $k=>$v) {
		if ($v < 0) {
			$s = $DEBUG ? $k : upd_dir($config['update-url'].'/update', $upd_pass, $k);
			$report[] = '<div class="alert alert-success">Директория создана: '.$s.'</div>';
		} else {
			$s = $DEBUG ? $k : upd_file($config['update-url'].'/update', $upd_pass, $k);
			$report[] = '<div class="alert alert-success">Загружен: '.$s.'</div>';
		}
	}

	// Обновление изменённых файлов
	$same = array_intersect_key($tut, $tam);
	foreach ($same as $k=>$v) {
		if ($tam[$k] != $v) {
			$s = $DEBUG ? $k : upd_file($config['update-url'].'/update', $upd_pass, $k);
			$report[] = '<div class="alert alert-warning">Обновлён '.$s.'</div>';
		}
	}

	// Очистка лишних файлов
	$more = array_diff_key($tam, $tut);
	foreach ($more as $k=>$v) {
		$s = $DEBUG ? $k : upd_del($config['update-url'].'/update', $upd_pass, $k);
		$report[] = '<div class="alert alert-danger">Удалён '.$k.'</div>';
	}
///*/
	$body = implode("\n", $report);
}