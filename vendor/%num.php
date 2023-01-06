<?

$q = db_query('SELECT * FROM vendor WHERE i="'.$config['args'][0].'"');
$root = '/vendor';

if ($row = db_fetch($q)) {
	$config['name'] = $row['name'];
	$config['action'] = array(
		array('action'=>'Список', 'href'=>'/vendor'),
	);

	$action = count($config['args']) > 1 ? $config['args'][1] : 'view';

	if ($action == 'edit') {
		$plan = w('plan-vendor');
		$plan['']['default'] = $row;
		w('request', $plan);
		
		if ($plan['']['valid'] && $plan['send']['value'] == 1) {
			db_update('vendor', array(
				'name'=>$plan['name']['value'],
				'typ'=>$plan['typ']['value'],
				'up'=>$plan['up']['value'],
				'w'=>$plan['w']['value'],
				'price'=>$plan['price']['value'],
				'prmin'=>$plan['prmin']['value'],
				'city'=>$plan['city']['value'],
//				'speed'=>$plan['speed']['value'],
				'ccode'=>$plan['ccode']['value'],
				'cname'=>$plan['cname']['value'],
				'ctype'=>$plan['ctype']['value'],
				'cbrand'=>$plan['cbrand']['value'],
				'ccount'=>$plan['ccount']['value'],
				'cprice'=>$plan['cprice']['value'],
				'copt'=>$plan['copt']['value'],
				'curr'=>$plan['curr']['value'],
				'short'=>$plan['short']['value'],
				'info'=>$plan['info']['value'],
			), array('i'=>$row['i']));
			alert('Запись изменена');
			w('cache-vendor');
			redirect($root);
		} elseif ($plan['']['valid'] && $plan['send']['value'] == 2) {
			db_delete('vendor', array(
				'i'=>$row['i']
			));
			alert('Запись удалена');
			w('cache-vendor');
			redirect($root);
		}
		$config['plan'] = $plan;
	} elseif ($action == 'view') {
		$config['name'] = $row['name'];
		$block['body'] = $row['info'];
	}	
} else {
	redirect($root);
}

?>