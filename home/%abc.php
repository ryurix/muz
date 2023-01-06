<?

$href = trim($config['q'], '/');

//*
if ($href == 'info/stati') {
	redirect('/article');
//	$menu[1]['code'] = 'article';
//	$config['path'] = array('article');
//	first_body($force = 1);
}

if (substr($href, 0, 10) == 'info/stati') {
//	$menu[1]['code'] = 'article';
//	$config['path'] = array('article', '%abc');
//	$config['args'] = array(substr($href, 11));
//	first_body($force = 1);
	redirect('/article/'.substr($href, 11), 301);
}
//*/

$select = array();
$chunks = explode('/', $href);
for ($i=count($chunks); $i>0; $i--) {
	$code = '/'.implode('/', array_slice($chunks, 0, $i));
	$select[] = '(SELECT * FROM menu WHERE code="'.addslashes($code).'")';
}

$valid = 0;
$q = db_query(implode (' UNION ', $select));

if ($row = db_fetch($q)) {
	w('lightbox');
	$valid = 1;
	$config['name'] = $row['name'];
	$config['row'] = $row;
}

if (!$valid) { // 404 Not found
	redirect('/404', 404);
}

$config['gtag-event'] = array(
	'event_name'=>'page_view',
	'ecomm_pagetype'=>'other',
);

?>