<?php

/*
 *	Copyright flydom.ru
 *	Version 3.4.2016-09-08
 */

// 1. Read config
ini_set('include_path', '.');
require_once '--/config.inc';
require_once '--/first.inc';
require_once '--/cache.inc';

// require_once 'cache/autoload.php';

// 2. Connect to database

if (isset($config['database'])) {
	require_once '--/database.inc';
	db_connect($config['database']);
}

// 3. Read session

require_once '--/access.inc';
access_login();

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

// 6. Page body

first_body();

// 7. Process file

include $config['root'].$config['design'].'.html';

// 8. Cron

//if (!(isset($config['DEBUG']) && $config['DEBUG'])) {
//	w('cron');
//	flush();
//}

exit();

?>