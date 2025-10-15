<?

$config['wide'] = 1;
$config['full'] = 1;

if (count($_GET)) {
	$config['canonical'] = '/article';
}

w('clean');
$up = first_int(kv($_REQUEST, 'catalog', '0'));

$pw = cache_load('pathway'.(\User::is('catalog') ? '-hide' : ''));

if ($up && isset($pw[$up]) && kv($_REQUEST, 'catalog') == $pw[$up]['url']) {
	// do nothing
	$config['breadcrumb'] = array('/article'=>'Все статьи');
} else {
	$up = 0;
}

$config['up'] = $up;

// * * * catalog

$sub = cache_load('catalog'.(\User::is('catalog') ? '-hide' : ''));
if ($up) {
	$sub = $sub['/'];
	foreach ($pw[$up]['pre'] as $i) {
		$sub = $sub[$i]['/'];
	}
	$sub = $sub[$up];
}

if (isset($sub['/'])) {
	$sub = array_keys($sub['/']);
} else {
	$sub = array();
}

$counts = array();
$children = array();
$children_all = cache_load('children-hide');
foreach ($sub as $i) {
	$counts[$i] = 0;
	$children[$i] = $children_all[$i];
}

$config['sub'] = $sub;

// * * * articles

$where = array();

if (!\User::is('menu')) {
	$where[] = 'hide=0';
}

if ($up) {
	$where[] = '(up IN ('.implode(',', $children_all[$up]).') OR up2 IN ('.implode(',', $children_all[$up]).'))';
}

if (count($where) == 0) {
	$where[] = '1=1';
}

$rows = array();
$q = db_query('SELECT * FROM article WHERE '.implode(' AND ', $where).' ORDER BY w,name');
while ($i = db_fetch($q)) {
	$rows[$i['i']] = $i;

	foreach ($children as $k=>$v) {
		if (in_array($i['up'], $v) || in_array($i['up2'], $v)) {
			$counts[$k]++;
		}
	}
}

$limit = 36;
$max = ceil(count($rows) / $limit);

$page = 1;
if (isset($_REQUEST['page'])) {
	w('clean');
	$page = clean_09($_REQUEST['page']);
	$page = $page < $max ? $page : $max;
}

$config['pager'][0] = array(
	'max'=>$max,
	'page'=>$page,
);

if (count($rows) > $limit) {
	$rows = array_slice($rows, $limit*($page - 1), $limit, true);
}

$config['rows'] = $rows;
$config['counts'] = $counts;

?>