<?

w('autoload');

$typ = \Price\Type::get();

\Page::breadcrumb(['/prices?typ='.$typ=>'Ценообразование']);

$plan = w('plan-prices');

w('clean');
$row = 0;
$code = \Page::arg();
if ($code) {
	$q = db_query('SELECT * FROM prices WHERE i='.$code.' AND typ='.$typ);
	if ($row = db_fetch($q)) {
		$row['code'] = $row['i'];
		$row['grp'] = strlen($row['grp']) ? explode(',', $row['grp']) : [];
		$row['brand'] = strlen($row['brand']) ? explode(',', $row['brand']) : [];
		$row['vendor'] = strlen($row['vendor']) ? explode(',', $row['vendor']) : [];
		$plan['send']['count'] = 2;
	}
	db_close($q);
	\Page::name('Правило #'.$row['i']);
}

if (!$row) {
	$row = [
		'code'=>0,
		'grp'=>[],
		'brand'=>[],
		'vendor'=>[],
		'up'=>0,
		'count'=>0,
		'price'=>0,
		'sale'=>0,
//		'days'=>0,
		'info'=>'',
		'fin'=>0
	];
	\Page::name('Новое правило');
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
			'vendor'=>implode(',', $plan['vendor']['value']),
			'up'=>$plan['up']['value'],
			'count'=>$plan['count']['value'],
			'price'=>$plan['price']['value'],
			'sale'=>$plan['sale']['value'],
//			'days'=>$plan['days']['value'],
			'info'=>$plan['info']['value'],
			'pmin'=>$plan['pmin']['value'],
			'pmax'=>$plan['pmax']['value'],
			'fin'=>$plan['fin']['value'],
		];

		$exists = $code ? db_result('SELECT COUNT(*) FROM prices WHERE i='.$code.' AND typ='.$typ) : 0;
		$exists2 = db_result('SELECT COUNT(*) FROM prices WHERE i='.$data['i'].' AND typ='.$typ);

		if ($exists2 && $data['i'] != $code) {
			\Flydom\Alert::danger('Правило № '.$data['i'].' уже существует!');
			$plan['code']['iv'] = 1;
		} else {
			if ($exists) {
				db_update('prices', $data, ['i'=>$code, 'typ'=>$typ]);
				\Flydom\Alert::warning('Правило обновлено');
			} else {
				$data['typ'] = $typ;
				db_insert('prices', $data);
				\Flydom\Alert::warning('Правило добавлено');
			}
			\Page::redirect('/prices?typ='.$typ);
		}
	}

	if ($plan['send']['value'] == 2) {
		$where = \Cron\Prices::where([
			'grp'=>$plan['grp']['value'],
			'brand'=>$plan['brand']['value'],
			'vendor'=>$plan['vendor']['value'],
			'up'=>$plan['up']['value'],
			'count'=>$plan['count']['value'],
//			'days'=>$plan['days']['value'],
			'pmin'=>$plan['pmin']['value'],
			'pmax'=>$plan['pmax']['value'],
			'typ'=>$typ,
		]);

		$select = 'SELECT COUNT(*) FROM store,sync WHERE store.i=sync.store';
		$count = db_result($select.$where);
		\Flydom\Alert::warning('Правилу соответствуют '.$count.' записей в таблице синхронизации.');
	}

	if ($plan['send']['value'] == 3) {
		db_delete('prices', [
			'i'=>$plan['code']['value'],
			'typ'=>$typ,
		]);
		\Flydom\Alert::warning('Правило удалено');
		\Page::redirect('/prices?typ='.$typ);
	}
}