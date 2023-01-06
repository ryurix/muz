<?

w('autoload');

$typ = \Type\Price::get();

$config['breadcrumb'] = [
	'/prices?typ='.$typ=>'Ценообразование'
];

$plan = w('plan-prices');

w('clean');
$row = 0;
$code = $config['args'][0];
if ($code) {
	$q = db_query('SELECT * FROM prices WHERE i='.$code.' AND typ='.$typ);
	if ($row = db_fetch($q)) {
		$row['code'] = $row['i'];
		$row['grp'] = strlen($row['grp']) ? explode(',', $row['grp']) : array();
		$row['brand'] = strlen($row['brand']) ? explode(',', $row['brand']) : array();
		$plan['send']['count'] = 4;
	}
	db_close($q);
	$config['name'] = 'Правило #'.$row['i'];
}

if (!$row) {
	$row = array(
		'code'=>0,
		'grp'=>array(),
		'brand'=>array(),
		'vendor'=>-1,
		'up'=>0,
		'count'=>0,
		'price'=>0,
		'sale'=>0,
		'days'=>0,
	);
	$config['name'] = 'Новое правило';
}

$plan['']['default'] = $row;
w('request', $plan);

if (!$plan['code']['value']) {
	$plan['code']['iv'] = 1;
}

if ($plan['']['valid'] && $plan['send']['value'] && $plan['code']['value']) {

	if ($plan['send']['value'] == 1) {
		$data = [
			'i'=>$plan['code']['value'],
			'grp'=>implode(',', $plan['grp']['value']),
			'brand'=>implode(',', $plan['brand']['value']),
			'vendor'=>$plan['vendor']['value'],
			'up'=>$plan['up']['value'],
			'count'=>$plan['count']['value'],
			'price'=>$plan['price']['value'],
			'sale'=>$plan['sale']['value'],
			'days'=>$plan['days']['value'],
			'pmin'=>$plan['pmin']['value'],
			'pmax'=>$plan['pmax']['value'],
		];

		$exists = $code ? db_result('SELECT COUNT(*) FROM prices WHERE i='.$code.' AND typ='.$typ) : 0;
		$exists2 = db_result('SELECT COUNT(*) FROM prices WHERE i='.$data['i'].' AND typ='.$typ);

		if ($exists2 && $data['i'] != $code) {
			alert('Правило № '.$data['i'].' уже существует!', 'error');
			$plan['code']['iv'] = 1;
		} else {
			if ($exists) {
				db_update('prices', $data, ['i'=>$code, 'typ'=>$typ]);
				alert('Правило обновлено');
			} else {
				$data['typ'] = $typ;
				db_insert('prices', $data);
				alert('Правило добавлено');
			}
			redirect('/prices?typ='.$typ);
		}
	}

	if ($plan['send']['value'] == 2) {
		w('prices-calc');
		$where = prices_where([
			'grp'=>$plan['grp']['value'],
			'brand'=>$plan['brand']['value'],
			'vendor'=>$plan['vendor']['value'],
			'up'=>$plan['up']['value'],
			'count'=>$plan['count']['value'],
			'days'=>$plan['days']['value'],
			'pmin'=>$plan['pmin']['value'],
			'pmax'=>$plan['pmax']['value'],
			'typ'=>$typ,
		]);

		$select = 'SELECT COUNT(*) FROM store,sync WHERE store.i=sync.store';
		$count = db_result($select.$where);
		alert('Правилу соответствуют '.$count.' записей в таблице синхронизации.');
	}

	if ($plan['send']['value'] == 3) {
		w('prices-calc');
		$count = prices_calc($rule = [
			'i'=>$plan['code']['value'],
			'up'=>$plan['up']['value'],
			'grp'=>$plan['grp']['value'],
			'brand'=>$plan['brand']['value'],
			'vendor'=>$plan['vendor']['value'],
			'count'=>$plan['count']['value'],
			'price'=>$plan['price']['value'],
			'sale'=>$plan['sale']['value'],
			'days'=>$plan['days']['value'],
			'pmin'=>$plan['pmin']['value'],
			'pmax'=>$plan['pmax']['value'],
			'typ'=>$typ,
		]);
		alert('Правило выполнено: '.$count);
	}

	if ($plan['send']['value'] == 4) {
		db_delete('prices', [
			'i'=>$plan['code']['value'],
			'typ'=>$typ,
		]);
		alert('Правило удалено');
		redirect('/prices?typ='.$typ);
	}
}

$config['plan'] = $plan;