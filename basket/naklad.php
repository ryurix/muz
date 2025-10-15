<?

$plan = array(
	''=>array('method'=>'POST'),
	'vendor'=>array('name'=>'Склад', 'type'=>'combo', 'values'=>w('list-sklad'), 'width'=>300),
	'info'=>array('name'=>'Комментарий', 'type'=>'line'),
	'send'=>array('type'=>'button', 'count'=>2, 1=>'Приходная', 2=>'Расходная'),
);

w('request', $plan);
if ($plan['send']['value']) {
	w('invalid', $plan);
}

if ($plan['']['valid'] && $plan['send']['value']) {

	w('basket');
	$basket = basket_calc($_SESSION['basket'], '');

	if (count($basket)) {

		$type = $plan['send']['value'] == 1 ? 1 : -1;

		db_insert('naklad', array(
			'dt'=>\Config::now(),
			'user'=>$_SESSION['i'],
			'vendor'=>$plan['vendor']['value'],
			'type'=>$type,
			'info'=>$plan['info']['value'],
		));

		$naklad = db_insert_id();

		foreach ($basket as $v) {
			db_insert('nakst', array(
				'naklad'=>$naklad,
				'store'=>$v['store'],
				'count'=>$v['count'],
			));
		}
		w('naklad');
		naklad_commit($naklad, $plan['vendor']['value'], $type);
		$_SESSION['basket'] = array();

		\Flydom\Alert::warning('<a href="/sklad/'.$naklad.'">Накладная</a> создана!');

	} else {
		\Flydom\Alert::danger('Ошибка создания накладной: корзина пустая');
	}
}

$config['plan'] = $plan;
