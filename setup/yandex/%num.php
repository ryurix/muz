<?

$code = $config['args'][0];

$row = 0;
if ($code) {
	$q = db_query('SELECT * FROM cron WHERE typ='.\Type\Cron::YANDEX.' AND i='.$code);
	$row = db_fetch($q);

	if (!$row) {
		redirect('/setup/yandex');
	}
}

if (!$row) {
	$row = array(
		'i'=>0,
		'typ'=>\Type\Cron::YANDEX,
		'form'=>11,
		'name'=>'Новая выгрузка',
		'dt'=>now() + 24*60*60,
		'every'=>86400,
		'data'=>'',
	);
}

$config['name'] = $row['name'];

$forms = array(
	3=>'Получение заказов',
	10=>'Обнуление остатков',
	11=>'Выгрузка остатков',

);

$form = isset($_REQUEST['form']) && isset($forms[$_REQUEST['form']]) ? $_REQUEST['form'] : $row['form'];
$data = $row + array_decode($row['data']);

$others = array(0=>'');
$q = db_query('SELECT * FROM cron WHERE typ='.\Type\Cron::YANDEX.' AND i<>'.$row['i'].' ORDER BY name');
while ($i = db_fetch($q)) {
	$others[$i['i']] = $i['name'];
}

$plan = [
	''=>['default'=>$data],
	'name'=>['name'=>'Название', 'type'=>'line', 'min'=>3],
	'form'=>['name'=>'Формат', 'type'=>'combo', 'values'=>$forms, 'default'=>$form],
	'every'=>['name'=>'Период', 'type'=>'combo', 'values'=>[0=>'Не запускать автоматически', 1=>'Ежедневно по расписанию', 3600=>'1 час', 28800=>'8 часов', 64800=>'18 часов', 86400=>'1 день', 259200=>'3 дня'], 'default'=>0],
	'time'=>['name'=>'Время запуска', 'type'=>'time', 'default'=>0],
	'week'=>['name'=>'Дни недели', 'type'=>'multich', 'values'=>[1=>'пн', 2=>'вт', 3=>'ср', 4=>'чт', 5=>'пт', 6=>'сб', 7=>'вс'], 'placeholder'=>'ежедневно'],

	'key'=>['name'=>'Ключ API', 'type'=>'line', 'default'=>'', 'min'=>1],
	'token'=>['name'=>'Токен Авторизации', 'type'=>'line', 'default'=>'', 'min'=>1],
	'campaignId'=>['name'=>'ID Магазина', 'type'=>'line', 'default'=>'', 'min'=>1],

//	'price'=>['name'=>'Тип цены', 'type'=>'combo', 'values'=>\Type\Price::names(), 'default'=>0],

//	'site'=>array('name'=>'Сайт', 'type'=>'line', 'default'=>'muzmart.com'),
//	'city'=>array('name'=>'Город', 'type'=>'combo', 'values'=>array(0=>'') + cache_load('city'), 'default'=>0),
//	'zero'=>array('label'=>'Передавать нули', 'type'=>'checkbox', 'default'=>1),
//	'force'=>['lable'=>'Передавать всё (по умолчанию только обновления)', 'type'=>'checkbox', 'default'=>0],

//	'follow'=>array('name'=>'Следующая', 'type'=>'multich', 'values'=>$others, 'default'=>array(), 'placeholder'=>'Выберите выгрузку...'),
];

if ($form == 3) {
	$plan['user'] = ['name'=>'Пользователь', 'type'=>'int', 'default'=>0, 'min'=>1];
}

if ($form == 11) {
	$plan['min'] = ['name'=>'Мин. количество', 'type'=>'int', 'default'=>0];
	$plan['minus'] = ['name'=>'Вычет', 'type'=>'int', 'default'=>0];
	$plan['vendor'] = ['name'=>'Поставщики', 'type'=>'multich', 'values'=>cache_load('vendor'), 'placeholder'=>'Выберите поставщиков...'];
	$plan['test'] = ['name'=>'Артикул для теста', 'type'=>'number'];
}

// $plan['follow']= ['name'=>'Следующая', 'type'=>'int', 'placeholder'=>'Выберите выгрузку...', 'default'=>0];
$plan['send'] = ['type'=>'button', 'count'=>3, 1=>'Сохранить', 2=>'Выполнить', 3=>'Удалить', 'confirm'=>[3=>'Удалить выгрузку?']];

w('request', $plan);
w('invalid', $plan);

if ($plan['send']['value'] == 3) {
	\Db::delete('cron', ['i'=>$row['i']]);
	redirect('.');
}

if ($plan['']['valid']) {
	$new = [
		'typ'=>\Type\Cron::YANDEX,
		'form'=>$plan['form']['value'],
		'name'=>$plan['name']['value'],
		'info'=>'',
		'dt'=>now() + $plan['every']['value'],
		'every'=>$plan['every']['value'],
		'time'=>$plan['time']['value'],
		'week'=>\Flydom\Arrau::encode($plan['week']['value']),
		'follow'=>\Flydom\Arrau::encode($plan['follow']['value']),
	];

	$data = [];
	foreach ($plan as $k=>$v) {
		if (empty($k) || isset($new[$k]) || $k === 'send') { continue; }
		$data[$k] = $v['value'];
	}

	$new['data'] = array_encode($data);
	$new['dt'] = \Cron\Task::next($new);

	if ($plan['send']['value'] == 1) {

		if ($row['i']) {
			db_update('cron', $new, ['i'=>$row['i']]);
			alert('Выгрузка сохранена');
			redirect('/setup/yandex');
		} else {
			db_insert('cron', $new);
			alert('Выгрузка создана!');
//			redirect('/setup/yandex');
		}
	}

	if ($plan['send']['value'] == 2) {

		$info = \Cron\Task::execute($new, $data);
		$info.= \Cron\Task::follow($data['follow']);

		alert('Выгрузка выполнена: '.$info);
		if ($row['i']) {
			w('ft');
			db_update('cron', [
				'info'=>ft(now(), 1).' '.$info
			], ['i'=>$row['i']]);
//			redirect('/setup/yandex');
		}
	}
}