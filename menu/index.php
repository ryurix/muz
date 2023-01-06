<?

$config['action'] = array(
	array('href'=>'/menu/new', 'action'=>'<i class="fa fa-plus"></i> Добавить')
);


$rows = array();

$select = 'SELECT * FROM menu ORDER BY w,name';
$q = db_query($select);

$rows = array();
while ($i = db_fetch($q)) {
	$rows[] = $i;
}
db_close($q);

$type = w('type-menu-icon');

$tree = array();

function menu_children(&$rows, $up) {
	$children = array();
	foreach ($rows as $k=>$i) {
		if (strcmp($i['up'], $up) == 0) {
			$children[] = $i;
			unset($rows[$k]);

			$children = array_merge($children, menu_children($rows, $i['code']));
		}
	}
	return $children;
}

$tree = menu_children($rows, '');

$lost = array();
foreach ($rows as $i) {
	$code = $i['code'];
	$lost[] = '<li><i class="fa '.$type[$i['type']].'"></i> <a href="/menu/edit?url='.$code.'">'.$i['w'].'</a> '.$code.' <a href="'.$code.'">'.$i['name'].'</a>';
}

$ups = array('');
$pre = array('up'=>'', 'code'=>'');

$rows = array('<ul>');
foreach ($tree as $i) {

	if (strcmp($i['up'], $pre['up']) != 0) {
		
		if (strcmp($i['up'], $pre['code']) == 0) {
			$rows[] = '<ul>';
			$ups[] = $i['up'];
		} else {

			while (strcmp($i['up'], $ups[count($ups) - 1]) != 0) {
				array_pop($ups);
				$rows[] = '</ul>';
			}
		}
	}

	$pre = $i;

	$code = $i['code'];
	$rows[] = '<li><i class="fa '.$type[$i['type']].'"></i> <a href="/menu/edit?url='.$code.'">'.$i['w'].'</a> '.$code.' <a href="'.$code.'">'.$i['name'].'</a>'.($i['hide'] ? ' <i class="fa fa-eye-slash"></i>' : '');
}

foreach ($ups as $i) {
	$rows[] = '</ul>';
}

$config['rows'] = $rows;
$config['lost'] = $lost;

?>