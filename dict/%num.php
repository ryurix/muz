<?

$q = db_query('SELECT * FROM dict WHERE i='.$config['args'][0]);

if ($row = db_fetch($q)) {

	$config['name'] = 'Слово: '.$row['name'];
	$action = count($config['args']) > 1 ? $config['args'][1] : 'edit';

	if ($action == 'edit') {
		$plan = w('plan-dict');
		$plan['']['default'] = $row;
		$fields = w('fields-dict', $row['i']);
		$plan+= $fields;
		w('request', $plan);
		if ($plan['']['valid'] && $plan['sent']['value']) {
			db_update('dict', array(
				'name'=>$plan['name']['value'],
				'code'=>$plan['code']['value'],
			), array('i'=>$row['i']));

			foreach ($fields as $k=>$v) {
				if ($v['word']) {
					db_update('word', array('value'=>$plan[$k]['value']), array('i'=>$v['word']));
				} else {
					db_insert('word', array('value'=>$plan[$k]['value'], 'dict'=>$row['i'], 'site'=>substr($k, 1)));
				}
			}
			w('cache-word');

			redirect('/dict');
		}
		$config['plan'] = $plan;
	} elseif ($action == 'erase') {
		$plan = w('plan-erase');
		w('request', $plan);

		if ($plan['']['valid']) {
			if ($plan['send']['value'] == 1) {
				db_delete('dict', array('i'=>$row['i']));
				redirect('/dict');
			} else {
				redirect('/dict/'.$row['i']);
			}
		} else {
			$config['plan'] = $plan;
			refile('erase.html');
		}
	}
} else {
	redirect('/dict');
}

?>