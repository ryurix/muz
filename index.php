<?php

/*
 *	Copyright flydom.ru
 *	Version 3.4.2016-09-08
 */

// 1. Read config
ini_set('include_path', '.');
require_once 'src/autoload.php';
session_set_save_handler(new \Flydom\Core\Session(), true);
require_once 'config.php';
require_once 'src/menu.php';

require_once '--/config.inc';
// require_once '--/first.inc';
require_once '--/cache.inc';

/*
// IP Ban
$ip = \User::ip();
$ban = cache_load('ip-ban', []);

$parts = explode('.', $ip);
$parts[3] = '*';
$one = implode('.', $parts);
$parts[2] = '*';
$two = implode('.', $parts);

if (in_array($ip, $ban) || in_array($one, $ban) || in_array($two, $ban)) {
	// header('HTTP/1.0 403 Forbidden', true, 403);
	exit;
}
*/

require_once 'lib/flydom/Core/log2.php';

// Legacy
require_once '--/database.inc';
db_connect(\Config::DATABASE);
include_once 'w.php';

include_once \Page::php();
include_once 'src/design/'.\Page::html();

// 3. Read session

// require_once '--/access.inc';
// access_login();

/*

// 4. Search for menu

$config['q'] = first_query();
require_once '--/menu.inc';
first_menu($config['q'], $_SESSION['roles'], $menu, $config);

// 5. List blocks

first_block('');
foreach ($_SESSION['roles'] as $role) {
	first_block($role.'/');
}

foreach ((array) $config['libs'] as $lib) {
	first_block($lib.'/');
	foreach ($_SESSION['roles'] as $role) {
		first_block($lib.'/'.$role.'/');
	}
}

// 6. Find page body, include php

include_once first_body();

// 7. Process design file

include \Config::ROOT.'src/design/'.$config['design'].'.html';

// 8. Cron

//if (!(isset($config['DEBUG']) && $config['DEBUG'])) {
//	w('cron');
//	flush();
//}

exit();

//*/