<?

if ($_SERVER['REQUEST_URI'] != '/robots.txt' && !(kv($config, 'DEBUG', 0))) {
	if((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") && strpos($_SERVER['SERVER_NAME'] ?? '', 'muzmart') !== FALSE) {
		$redirect = 'https://' . ($_SERVER['HTTP_HOST'] ?? $config['site']) . rtrim($_SERVER['REQUEST_URI'], '/');
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	}
}