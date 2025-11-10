<?

if (kv($config, 'DEBUG', 0)) {
	header("Content-Type: text/plain");
	echo 'User-agent: *
Disallow: /';
	exit();
} else {
	header("Content-Type: text/plain");
	//echo file_get_contents(\Config::ROOT.'robots'.$config['dict-site'].'.txt');
	echo \Page::cityRobots();
	exit();
}

?>