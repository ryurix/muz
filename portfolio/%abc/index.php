<?

w('lightbox.js');

w('clean');
$key = first_int($config['args'][0]);
$q = db_query('SELECT * FROM pf WHERE i="'.$key.'"');

if ($row = db_fetch($q)) {
	//$ups = cache_load('portfolio');
	//$config['breadcrumb'] = array('/portfolio#pf'.$row['up'] => $ups[$row['up']]);
	$config['breadcrumb'] = array('/portfolio' => 'Портфолио');

	w('pf-action', $row['i']);

	$name = $row['name'];
	$config['name'] = $name;

	$action = count($config['args']) > 1 ? $config['args'][1] : 'view';

	if ($action == 'view') {
		$config['row'] = $row;
	} elseif ($action == 'edit') {
		$plan = w('plan-pf');
		$plan['']['default'] = $row;
		$path = '/files/portfolio/'.$row['i'].'/';
		$plan['pics']['path'] = $path;
		w('request', $plan);

		if ($plan['']['valid']) {
			if ($plan['send']['value'] == 1) {
				$data = array(
					'up'=>$plan['up']['value'],

					'name'=>$plan['name']['value'],
					'dt'=>$plan['dt']['value'],

					'pics'=>$plan['pics']['value'],
					'info'=>$plan['info']['value'],
				);
				db_update('pf', $data, array('i'=>$key));
				alert('<a href="/portfolio/'.$key.'">Запись</a> изменена');
				redirect('/portfolio/'.$row['i']);
				//print_pre($data);
			}
		}
		$config['plan'] = $plan;
		refile('../new.html');
	}
} else {
	redirect('/portfolio');
}

?>