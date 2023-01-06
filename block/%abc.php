<?

$q = db_query('SELECT * FROM block WHERE code="'.$config['args'][0].'"');
$row = db_fetch($q);

if (is_array($row)) {
	$plan = array(
		''=>array('method'=>'POST'),
		'info'=>array(),
		'send'=>array(1=>'Сохранить', 'type'=>'button', 'count'=>1),
	);

	switch($row['type']) {
		case 'line':
			$plan['info'] = array('type'=>'line');
			break;
		case 'wiki':
			$plan['info'] = array('type'=>'wiki', 'rows'=>12);
			break;
		case 'text':
			$plan['info'] = array('type'=>'text', 'rows'=>12);
			break;
		case 'rich':
			$plan['info'] = array('type'=>'rich', 'rows'=>17);
			break;
		case 'page':
			$plan['info'] = array('type'=>'rich', 'rows'=>17);
			break;
	}

	$plan['info']['name'] = '';
	$plan['info']['value'] = $row['info'];

	switch ($row['type']) {
		case 'page':
			$plan['info']['value'] = $row['info'];
			break;
		default:
			$plan['info']['value'] = cache_get($row['code']);
	}

	w('request', $plan);

	if ($plan['']['valid'] && $plan['send']['value'] > 0) {
		alert('Изменения блока сохранены');
		switch ($row['type']) {
			case 'page':
				db_update('block', array(
					'info'=>$plan['info']['value'],
				), array('code'=>$row['code']));
				break;
			default:
				cache_set($row['code'], $plan['info']['value']);
		}
	}

	$config['plan'] = $plan;
	$config['name'] = $row['name'];
} else {
	redirect('/');
}

?>