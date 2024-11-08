<?

$code = $config['args'][0];

$row = 0;
if ($code) {
	$q = db_query('SELECT * FROM cron WHERE typ=20 AND i='.$code);
	$row = db_fetch($q);

	if (!$row) {
		redirect('/setup/wildberries');
	}
}

if (!$row) {
	$row = array(
		'i'=>0,
		'typ'=>20,
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
	1=>'Выгрузка количества',
	3=>'Выгрузка цен',
	10=>'Обнуление количества',
	20=>'Получение заказов',
    25=>'Отмена заказов',
);

$others = array(0=>'');
$q = db_query('SELECT * FROM cron WHERE typ=20 AND i<>'.$row['i'].' ORDER BY name');
while ($i = db_fetch($q)) {
	$others[$i['i']] = $i['name'];
}

$clients = [];
foreach ($config['wildberries'] as $k=>$v) {
	$clients[$k] = $v['name'];
}

$plan = [
	''=>array('default'=>$data),
	'name'=>array('name'=>'Название', 'type'=>'line', 'min'=>3),
	'every'=>array('name'=>'Период', 'type'=>'combo', 'values'=>[
		0=>'Не запускать автоматически',
		1=>'Ежедневно по расписанию',
		300=>'5 мин',
		600=>'10 мин',
		1800=>'30 мин',
		3600=>'1 час',
		28800=>'8 часов',
		64800=>'18 часов',
		86400=>'1 день',
		259200=>'3 дня'
	], 'default'=>0),

	'time'=>array('name'=>'Время запуска', 'type'=>'time', 'default'=>0),
	'week'=>array('name'=>'Дни недели', 'type'=>'multich', 'values'=>array(1=>'пн', 2=>'вт', 3=>'ср', 4=>'чт', 5=>'пт', 6=>'сб', 7=>'вс'), 'placeholder'=>'ежедневно'),
	'form'=>array('name'=>'Формат', 'type'=>'combo', 'values'=>$forms),
	'client'=>array('name'=>'Клиент', 'type'=>'combo', 'default'=>'16838', 'values'=>$clients),
	'type'=>['name'=>'Тип цены', 'type'=>'combo', 'values'=>\Type\Price::names(), 'default'=>0],
];

if (isset($data['form']) && $data['form'] < 20) {
	$plan+= [
		'price'=>array('name'=>'Мин. цена', 'type'=>'int', 'default'=>0),
		'min'=>array('name'=>'Мин. количество', 'type'=>'int', 'default'=>0),
		'minus'=>array('name'=>'Вычет', 'type'=>'int', 'default'=>0),
		'force'=>array('name'=>'Выгрузить', 'type'=>'combo', 'default'=>0, 'values'=>[
			0=>'Обновления',
			100=>'Всё',
			1=>'Только найденные у поставщиков',
		]),

	//	'site'=>array('name'=>'Сайт', 'type'=>'line', 'default'=>'muzmart.com'),
	//	'city'=>array('name'=>'Город', 'type'=>'combo', 'values'=>array(0=>'') + cache_load('city'), 'default'=>0),
	//	'zero'=>array('label'=>'Передавать нули', 'type'=>'checkbox', 'default'=>1),

		'vendor'=>['name'=>'Поставщики', 'type'=>'multich', 'values'=>cache_load('vendor'), 'placeholder'=>'Выберите поставщиков...', 'default'=>[]],
	];
}

$plan+= [
	'follow'=>array('name'=>'Следующая', 'type'=>'multich', 'values'=>$others, 'default'=>array(), 'placeholder'=>'Выберите выгрузку...'),

	'send'=>array('type'=>'button', 'count'=>3, 1=>'Сохранить', 2=>'Выгрузить', 3=>'Удалить', 'confirm'=>[3=>'Удалить выгрузку?']),
	'exclude'=>['name'=>'Исключения', 'type'=>'text', 'help'=>'список штрихкодов через пробел'],
	'test'=>['name'=>'Тест', 'type'=>'line'],
];

$plan = w('request', $plan);

if ($plan['send']['value'] == 3) {
	\Db::delete('cron', ['i'=>$row['i']]);
	redirect('.');
}

$plan = w('invalid', $plan);

if ($plan['form']['value'] == 3) { // Для выгрузки цен отключаем лишние поля
	w('input-hidden');
	$plan['min']['type'] = 'hidden';
	$plan['minus']['type'] = 'hidden';
	$plan['vendor']['type'] = 'hidden';
	$plan['vendor']['value'] = array_encode($plan['vendor']['value']);
	unset($plan['force']['values'][1]);
}

if ($plan['']['valid']) {
	$data = array(
		'time'=>$plan['time']['value'],
		'week'=>$plan['week']['value'],
		'form'=>$plan['form']['value'],
		'client'=>$plan['client']['value'],
		'type'=>$plan['type']['value'],

		'price'=>isset($plan['price']) ? $plan['price']['value'] : 0,
		'min'=>isset($plan['min']) ? $plan['min']['value'] : 0,
		'minus'=>isset($plan['minus']) ? $plan['minus']['value'] : 0,
		'force'=>isset($plan['force']) ? $plan['force']['value'] : 0,
		'vendor'=>isset($plan['vendor']) ? $plan['vendor']['value'] : 0,

		'follow'=>$plan['follow']['value'],
		'exclude'=>$plan['exclude']['value'],
		'test'=>$plan['test']['value'],
	);

	$new = array(
		'typ'=>20,
		'name'=>$plan['name']['value'],
		'info'=>'',
		'every'=>$plan['every']['value'],
		'data'=>array_encode($data),
	);

	$new['dt'] = \Cron\Task::next($new, $data);

	if ($plan['send']['value'] == 1) {
		if ($row['i']) {
			db_update('cron', $new, ['i'=>$row['i']]);
			alert('Выгрузка сохранена');
			redirect('/setup/wildberries');
		} else {
			db_insert('cron', $new);
			alert('Выгрузка создана!');
			redirect('/setup/wildberries');
		}
	}

	if ($plan['send']['value'] == 2) {

		$info = \Cron\Task::execute($new, $data);
		$info.= \Cron\Task::follow($data['follow']);

		alert('Выгрузка выполнена! '.$info);
		if ($row['i']) {
			w('ft');
			db_update('cron', [
				'info'=>ft(now(), 1).' '.$info,
			], ['i'=>$row['i']]);
//			redirect('/setup/wildberries');
		}
	}
}