<?

/*
 *	Copyright flydom.ru
 *	Version 2.3.2020-11-24
 */

namespace Flydom;

class Page extends Singleton
{

public function isFront() { return count($this->menu) <= 1; }

protected $data = [];
protected $readonly = ['php', 'html'];
protected $default = [
	'design'=>'design',
	'front'=>'/home',
	'console'=>false,
];

static function get(): Page { return parent::get(); }

static function args($num = 0, $default = null) {
	return self::get()->args[$num] ?? $default;
}

// * * *

protected function __construct() {
	$this->query();
}

protected function query() {
	if (isset($_GET['q']) && is_string($_GET['q'])) {
		$q = $_GET['q'];
	}
	elseif ($_SERVER['REQUEST_URI'] == '/index.php') {
		self::redirect('/');
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
		self::redirect('/');
	}

	$this->query = $q;
}

function parse($menu) {
	$menu[0]['here'] = true;
	$menu[0]['href'] = '';
	$virtual = false;

	$design = $this->design;
	$name = $menu[0]['name'];
	$args = [];
	$path = [];

	$q = $this->query == '/' ? $this->front : $this->query;
	$junk = explode('/', $q);
	$junk = array_filter($junk, function($value) { return $value !== ''; });
	$junk = array_values($junk);

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
					} else if (isset($son['%str'])) {
						$args[] = $here;
						$son['%str']['code'] = $here;
						$son['%str']['here'] = TRUE;
					}
				}
			}

			$unset = array();
			foreach(array_keys($son) as $code) {
				$i = &$son[$code];
				if (isset($i['role'])) {
					if (!User::get()->test($i['role'])) {
						$unset[] = $code;
						continue;
					}
				}

				if (!isset($i['name'])) {
					$i['name'] = $pre['name'];
				}

				if (isset($i['here']) && $i['here']) {
					foreach ($i as $k=>$v) {
						if (in_array($k, ['code', 'here', 'href', 'role', 'virtual'])) {
							continue;
						}
						$this->$k = $v;
					}
					if (isset($i['virtual']) && $i['virtual']) {
						if ($code != '%num' && $code != '%str') {
							$args[] = $here;
						}
					} else {
						$path[] = $code;
					}

					$code = $i['code'];
					$menu[] = &$i;
					array_shift($junk);
				} else {
					if ($code == '%num' || $code == '%str') {
						$unset[] = $code;
						continue;
					} else {
						$i['code'] = $code;
					}
				}

				if (!isset($i['href'])) { $i['href'] = $pre['href'].'/'.$code; }
				if (isset($i['/'])) { $level2[] = &$i; }
			}
			foreach($unset as $j) {
				unset($son[$j]);
			}
		}
		$level = $level2;
	}
	$menu[0]['href'] = '/';

	$this->menu = $menu;
	$this->args = $args;
	$this->junk = $junk;
	$this->path = $path;

	$this->body($path);
}

public function body($path) {
	$file = $this->root.implode('/', $path);
	$body = is_dir($file) ? 'index' : array_pop($path);
	$path = count($path) > 0 ? implode('/', $path).'/' : trim($this->front, '/').'/';
	$file = $this->root.$path.$body;
	$this->data['php'] = is_file($file.'.php') ? $file.'.php' : $this->root.'index.php';
	$this->data['html'] = $file.'.html';
}

public static function redirect($where, $code = 302) {
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

} // class Page