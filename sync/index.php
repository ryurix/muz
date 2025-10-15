<?

$plan = [
	''=>['method'=>'POST'],
	'code'=>['type'=>'line'],
	'name'=>['type'=>'line', 'min'=>3],
	'store'=>['type'=>'int'],
	'vendor'=>['type'=>'int'],
];

w('request', $plan);

//	$config['design'] = 'none'; print_pre($_REQUEST);

if (strlen($plan['name']['value'].$plan['store']['value']) > 0 && \User::is('sync')) {
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
				'dt'=>\Config::now(),
				'store'=>$plan['store']['value'],
				'vendor'=>$plan['vendor']['value'],
			), array(
				'i'=>$sync['i'],
			));
			$block['body'] = 'Изменение имеющейся синхронизации! ('.$sync['i'].')';
			\Flydom\Log::add(18, $plan['store']['value'], $code);
		} else {
			db_insert('sync', array(
				'dt'=>\Config::now(),
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
			\Flydom\Log::add(17, $plan['store']['value'], $code);
		}

		db_insert('log', array(
			'type'=>17,
			'dt'=>\Config::now(),
			'user'=>$_SESSION['i'],
			'info'=>'store: '.$plan['store']['value'],
		));
	} else {
		$block['body'] = 'code="'.$plan['code']['value'].'", name="'.$plan['name']['value'].'", store='.$plan['store']['value'].', vendor='.$plan['vendor']['value'];
	}
}

?>