<?

w('chosen.js');

if (!function_exists('geoloc')) {
	function geoloc($ip) {
		$ip = explode('.', $ip);
		if (count($ip) < 4) {
			return 0;
		}
		$ip = $ip[0]*256*256*256 + $ip[1]*256*256 + $ip[2]*256 + $ip[3];

		$geoloc = cache_load('geoloc');
		foreach ($geoloc as $k=>$v) {
			if ($k <= $ip) {
				if ($v[1] >= $ip) {
					return $v[0];
				}
			}
		}
		return 0;
	}
}

// Город по домену
$sites = cache_load('site');
$site = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] ?? '';
$site = array_search($site, $sites);
if (!$site) { $site = 34; } // По умолчанию Москва
if ($site != 34 || isset($_REQUEST['msk'])) { $_SESSION['cire'] = $site; } // Перенаправление по домену

// Перенаправление
if (isset($_REQUEST['_city'])) {
	w('clean');
	$city = clean_int($_REQUEST['_city']);
	if ($city) {
		if (\User::is()) {
			db_update('user', array(
				'city'=>$city
			), array('i'=>$_SESSION['i']));
		}

		$ip = $_SESSION['ip'];
		$loc = geoloc($ip);
		if (!$loc && $city) {
			$geoloc2 = cache_load('geoloc2');
			if (!isset($geoloc2[$ip])) {
				$geoloc2[$ip] = $city;
				cache_save('geoloc2', $geoloc2);
			}
		}

		$_SESSION['cire'] = $city; // Принудительное перенаправление
	}
}

// Геолокация
if ($site == 34 && !isset($_SESSION['cire'])) {
	$ip = $_SESSION['ip'];

	$city = geoloc($ip);
	if (!$city) {
		$geoloc2 = cache_load('geoloc2');

		if (isset($geoloc2[$ip])) {
			$city = $geoloc2[$ip];
		}
	}

	if (!$city) {
		$city = 34; // По умолчанию Москва
	}

	$_SESSION['cire'] = $city; // Перенаправление по геолокации
}


// Перенаправление


if (\Config::DEBUG) {
	if (!isset($_SESSION['cire']) || strlen($_SESSION['cire']) == 0) {
		$_SESSION['cire'] = $site;
	}
} else {
	$city = $_SESSION['cire'];
	if ($city != $site)
	{
		if (isset($sites[$city])) {
			\Page::redirect('https://'.$sites[$city].\Page::url().($city == 34 ? '?msk' : ''));
		} elseif ($site != 34) {
			\Page::redirect('https://'.$sites[34].\Page::url().'?_city='.$city);
		}
	}
}

\Page::site($site);
\Page::domain($sites[$site]);
