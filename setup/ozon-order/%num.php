<?

$code = $config['args'][0];

$row = 0;
if ($code) {
	$q = db_query('SELECT * FROM cron WHERE typ=10 AND i='.$code);
	$row = db_fetch($q);

	if (!$row) {
		redirect('/setup/ozon');
	}
}

if (!$row) {
	$row = array(
		'i'=>0,
		'typ'=>10,
		'name'=>'Новая выгрузка',
		'dt'=>now() + 24*60*60,
		'every'=>86400,
		'data'=>'',
	);
}

$config['name'] = $row['name'];

$data = array_decode($row['data']);
$data['name'] = $row['name'];
$data['every'] = $row['every'];

$forms = array(
	1=>'Выгрузка цены',
	11=>'Выгрузка количества',
	12=>'Обнуление количества',
//	10=>'Обнуление всех',
);

$others = array(0=>'');
$q = db_query('SELECT * FROM cron WHERE typ=10 AND i<>'.$row['i'].' ORDER BY name');
while ($i = db_fetch($q)) {
	$others[$i['i']] = $i['name'];
}

$plan = array(
	''=>array('default'=>$data),
	'name'=>array('name'=>'Название', 'type'=>'line', 'min'=>3),
	'every'=>array('name'=>'Период', 'type'=>'combo', 'values'=>array(0=>'Не запускать автоматически', 1=>'Ежедневно по расписанию', 3600=>'1 час', 28800=>'8 часов', 64800=>'18 часов', 86400=>'1 день', 259200=>'3 дня'), 'default'=>0),
	'time'=>array('name'=>'Время запуска', 'type'=>'time', 'default'=>0),
	'week'=>array('name'=>'Дни недели', 'type'=>'multich', 'values'=>array(1=>'пн', 2=>'вт', 3=>'ср', 4=>'чт', 5=>'пт', 6=>'сб', 7=>'вс'), 'placeholder'=>'ежедневно'),
	'form'=>array('name'=>'Формат', 'type'=>'combo', 'values'=>$forms),

	'client'=>array('name'=>'Код клиента', 'type'=>'line', 'default'=>'', 'min'=>1),
	'api'=>array('name'=>'Ключ API', 'type'=>'line', 'default'=>'', 'min'=>1),

	'min'=>array('name'=>'Мин. количество', 'type'=>'int', 'default'=>0),
	'minus'=>array('name'=>'Вычет', 'type'=>'int', 'default'=>0),
//	'site'=>array('name'=>'Сайт', 'type'=>'line', 'default'=>'muzmart.com'),
//	'city'=>array('name'=>'Город', 'type'=>'combo', 'values'=>array(0=>'') + cache_load('city'), 'default'=>0),
	'zero'=>array('label'=>'Передавать нули', 'type'=>'checkbox', 'default'=>1),
	'vendor'=>array('name'=>'Поставщики', 'type'=>'multich', 'values'=>cache_load('vendor'), 'placeholder'=>'Выберите поставщиков...'),
	'follow'=>array('name'=>'Следующая', 'type'=>'multich', 'values'=>$others, 'default'=>array(), 'placeholder'=>'Выберите выгрузку...'),
	'test'=>['name'=>'Артикул для теста', 'type'=>'int'],

	'send'=>array('type'=>'button', 'count'=>2, 1=>'Сохранить', 2=>'Выгрузить'),
);

w('request', $plan);
w('invalid', $plan);

$config['plan'] = $plan;

if ($plan['']['valid']) {
	$data = array(
		'time'=>$plan['time']['value'],
		'week'=>$plan['week']['value'],
		'form'=>$plan['form']['value'],
		'client'=>$plan['client']['value'],
		'api'=>$plan['api']['value'],
		'min'=>$plan['min']['value'],
		'minus'=>$plan['minus']['value'],
		'zero'=>$plan['zero']['value'],
		'vendor'=>$plan['vendor']['value'],
		'follow'=>$plan['follow']['value'],
	);

	if ($plan['send']['value'] == 1) {
		$new = array(
			'typ'=>10,
			'name'=>$plan['name']['value'],
			'info'=>'',
			'dt'=>now() + $plan['every']['value'],
			'every'=>$plan['every']['value'],
			'data'=>array_encode($data),
		);

		if ($new['every'] == 1) {
			w('cron-tools');
			$new['dt'] = cron_next(now(), $data);
		}

		if ($row['i']) {
			db_update('cron', $new, array('i'=>$row['i']));
			alert('Выгрузка сохранена');
			redirect('/setup/ozon');
		} else {
			db_insert('cron', $new);
			alert('Выгрузка создана!');
			redirect('/setup/ozon');
		}
	}

	if ($plan['send']['value'] == 2) {
		$data['alert'] = 1;
		$count = w('ozon', $data);
		alert('Выгрузка выполнена! Выгружено товаров: '.$count);
		if ($row['i']) {
			w('ft');
			db_update('cron', array(
				'info'=>'('.$count.') '.ft(now(), 1),
			), array('i'=>$row['i']));
//			redirect('/setup/ozon');
		}
	}
}

?>