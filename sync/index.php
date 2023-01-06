<?

$plan = array(
	''=>array('method'=>'POST'),
	'code'=>array('type'=>'line'),
	'name'=>array('type'=>'line', 'min'=>3),
	'store'=>array('type'=>'int'),
	'vendor'=>array('type'=>'int'),
);

w('request', $plan);

//	$config['design'] = 'none'; print_pre($_REQUEST);

if (strlen($plan['name']['value'].$plan['store']['value']) > 0 && is_user('sync')) {
	$config['design'] = 'none';
	if ($plan['']['valid']) {
		$block['body'] = '';
		w('u8');

		$code = u8sub($plan['code']['value'], 0, 15);
		$name = u8sub($plan['name']['value'], 0, 64);

		$q = db_query('SELECT * FROM sync WHERE vendor='.$plan['vendor']['value'].' AND store='.$plan['store']['value'].' AND name="'.addcslashes($name, '"').'"');
		if ($sync = db_fetch($q)) { // Ищем в таблице синхронизации
			db_update('sync', array(
				'code'=>$code,
				'name'=>$name,
				'dt'=>now(),
				'store'=>$plan['store']['value'],
				'vendor'=>$plan['vendor']['value'],
			), array(
				'i'=>$sync['i'],
			));
			$block['body'] = 'Изменение имеющейся синхронизации! ('.$sync['i'].')';
		} else {
			db_insert('sync', array(
				'dt'=>now(),
				'code'=>$code,
				'name'=>$name,
				'store'=>$plan['store']['value'],
				'vendor'=>$plan['vendor']['value'],
			));
			if ($plan['store']['value']) {
				$block['body'] = 'Синхронизировано.';
			} else {
				$block['body'] = 'Не синхронизировать: '.$name;
			}
		}

		db_insert('log', array(
			'type'=>7,
			'dt'=>now(),
			'user'=>$_SESSION['i'],
			'info'=>'store: '.$plan['store']['value'],
		));
	} else {
		$block['body'] = 'code="'.$plan['code']['value'].'", name="'.$plan['name']['value'].'", store='.$plan['store']['value'].', vendor='.$plan['vendor']['value'];
	}
}

?>