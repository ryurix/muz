<?

w('clean');
$num = (int)first_int($config['args'][0]);

$q = db_query('SELECT * FROM brand WHERE i="'.$num.'"');
$root = '/brand';

if ($row = db_fetch($q)) {
	$config['name'] = $row['name'];

	$action = count($config['args']) > 1 ? $config['args'][1] : 'view';

	if ($action == 'view') {
		$canonical = '<link rel="canonical" href="http://'.$config['domain'].'/brand/'.$row['i'].'-'.str2url($row['name']).'">';
		if (isset($block['head'])) {
			$block['head'].= "\n".$canonical;
		} else {
			$block['head'] = $canonical;
		}

		$config['row'] = $row;
		refile('view.html');
	} elseif ($action == 'edit') {
		if (is_user('catalog')) {
			$config['action'] = array(array('href'=>'/brand/'.$row['i'], 'action'=>'смотр'));
		}

		$plan = w('plan-brand');
		$plan['']['default'] = $row;
		w('request', $plan);
		
		if ($plan['']['valid'] && $plan['send']['value'] == 1) {
			db_update('brand', array(
				'code'=>$plan['code']['value'],
				'name'=>$plan['name']['value'],
				'icon'=>$plan['icon']['value'],
				'info'=>$plan['info']['value'],
				'w'=>$plan['w']['value'],
			), array('i'=>$row['i']));
			w('cache-brand');
			alert('Запись изменена');
			redirect($root);
		} elseif ($plan['']['valid'] && $plan['send']['value'] == 2) {
			db_delete('brand', array(
				'i'=>$row['i']
			));
			w('cache-brand');
			alert('Запись удалена');
			redirect($root);
		}
		$config['plan'] = $plan;
	}	
} else {
	redirect($root);
}

?>