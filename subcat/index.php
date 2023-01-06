<?

$get = array(
	'up'=>array('type'=>'int', 'min'=>1),
	'code'=>array('type'=>'line', 'exp'=>'[-0-9,]+', 'min'=>1),
);
w('request', $get);

if ($get['']['valid']) {

	$plan = w('plan-subcat');

	$q = db_query('SELECT * FROM subcat WHERE code="'.$get['code']['value'].'" AND up='.$get['up']['value']);
	if ($row = db_fetch($q)) {
		$plan['']['default'] = $row;
		$config['name'] = $row['name2'];
	} else {
		$row = array('i'=>0);
		$pathway = cache_load('pathway-hide');
		$config['name'] = kv($pathway, $get['up']['value'], array('name'=>''))['name'];
	}
	db_close($q);

	w('request', $plan);

	if ($plan['']['valid'] && $plan['send']['value'] == 1) {
		$data = array(
			'dt'=>now(),
			'up'=>$get['up']['value'],
			'code'=>$get['code']['value'],
			'tag0'=>$plan['tag0']['value'],
			'tag1'=>$plan['tag1']['value'],
			'tag2'=>$plan['tag2']['value'],
			'tag3'=>$plan['tag3']['value'],
			'name2'=>$plan['name2']['value'],
			'short'=>$plan['short']['value'],
			'info'=>$plan['info']['value'],
		);

		if ($row['i']) {
			db_update('subcat', $data, array('i'=>$row['i']));
			alert('Подкаталог изменен');
		} else {
			db_insert('subcat', $data);
			alert('Подкаталог создан');
		}
	}

	if ($plan['']['valid'] && $plan['send']['value'] == 2) {
		db_delete('subcat', array(
			'up'=>$get['up']['value'],
			'code'=>$get['code']['value'],
		));
	} else {
		$config['plan'] = $plan;
		refile('form.html');
	}
}

?>