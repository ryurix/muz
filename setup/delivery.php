<?

$vendor = cache_load('vendor');
$speed = w('speed');
$plan = array(
//	''=>array('method'=>'POST'),
	'1'=>array('name'=>'Откуда', 'type'=>'combo', 'values'=>$vendor),
	'2'=>array('name'=>'Куда', 'type'=>'cire'),
	'sp'=>array('name'=>'Скорость', 'type'=>'combo', 'values'=>$speed),
	'a'=>array('type'=>'button', 'count'=>2, 1=>'Сохранить', 2=>'Обновить кэш'),
);

w('request', $plan);

if ($plan['']['valid'] && $plan['a']['value'] == 1) {
	db_delete('speeds', array(
		'vendor'=>$plan['1']['value'],
		'cire'=>$plan['2']['value'],
	));
	db_insert('speeds', array(
		'vendor'=>$plan['1']['value'],
		'cire'=>$plan['2']['value'],
		'speed'=>$plan['sp']['value'],
	));
	w('cache-speeds');
}

if (isset($_GET['e1']) && isset($_GET['e2'])) {
	w('clean');
	db_delete('speeds', array(
		'vendor'=>clean_int($_GET['e1']),
		'cire'=>clean_int($_GET['e2']),
	));
	w('cache-speeds');
}

if ($plan['a']['value'] == 2) {
	w('cache-speeds');
}

$config['plan'] = $plan;

?>