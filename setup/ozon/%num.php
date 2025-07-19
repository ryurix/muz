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

$forms = array(
	1=>'Выгрузка цены',
	//3=>'Выгрузка цен составных товаров',
	11=>'Выгрузка количества',
	//13=>'Выгрузка количества составных товаров',
	19=>'Обнуление количества',
//	10=>'Обнуление всех',
);

$form = isset($_REQUEST['form']) && isset($forms[$_REQUEST['form']]) ? $_REQUEST['form'] : $row['form'];
$data = $row + array_decode($row['data']);

$others = array(0=>'');
$q = db_query('SELECT * FROM cron WHERE typ=10 AND i<>'.$row['i'].' ORDER BY name');
while ($i = db_fetch($q)) {
	$others[$i['i']] = $i['name'];
}

$plan = [
	''=>['default'=>$data],
	'name'=>array('name'=>'Название', 'type'=>'line', 'min'=>3),
	'every'=>['name'=>'Период', 'type'=>'combo', 'values'=>\Form\Cron::EVERY, 'default'=>0],
	'time'=>array('name'=>'Время запуска', 'type'=>'time', 'default'=>0),
	'week'=>array('name'=>'Дни недели', 'type'=>'multich', 'values'=>array(1=>'пн', 2=>'вт', 3=>'ср', 4=>'чт', 5=>'пт', 6=>'сб', 7=>'вс'), 'placeholder'=>'ежедневно'),
	'form'=>array('name'=>'Формат', 'type'=>'combo', 'values'=>$forms),

	'client'=>array('name'=>'Код клиента', 'type'=>'line', 'default'=>'', 'min'=>1),
	'api'=>array('name'=>'Ключ API', 'type'=>'line', 'default'=>'', 'min'=>1),
	'warehouse'=>['name'=>'ID Склада', 'type'=>'line', 'default'=>'', 'min'=>1],

	'price'=>['name'=>'Тип цены', 'type'=>'combo', 'values'=>\Type\Price::names(), 'default'=>0],
	'min'=>array('name'=>'Мин. количество', 'type'=>'int', 'default'=>0),
	'minus'=>array('name'=>'Вычет', 'type'=>'int', 'default'=>0),
//	'site'=>array('name'=>'Сайт', 'type'=>'line', 'default'=>'muzmart.com'),
//	'city'=>array('name'=>'Город', 'type'=>'combo', 'values'=>array(0=>'') + cache_load('city'), 'default'=>0),
	'zero'=>array('label'=>'Передавать нули', 'type'=>'checkbox', 'default'=>1),
//	'force'=>['lable'=>'Передавать всё (по умолчанию только обновления)', 'type'=>'checkbox', 'default'=>0],
	'vendor'=>array('name'=>'Поставщики', 'type'=>'multich', 'values'=>cache_load('vendor'), 'placeholder'=>'Выберите поставщиков...'),
	'follow'=>array('name'=>'Следующая', 'type'=>'multich', 'values'=>$others, 'default'=>array(), 'placeholder'=>'Выберите выгрузку...'),

	'test'=>['name'=>'Артикул для теста', 'type'=>'number'],

	'send'=>array('type'=>'button', 'count'=>3, 1=>'Сохранить', 2=>'Выгрузить', 3=>'Удалить', 'confirm'=>[3=>'Удалить выгрузку?']),
];

w('request', $plan);
w('invalid', $plan);

if ($plan['send']['value'] == 3) {
	\Db::delete('cron', ['i'=>$row['i']]);
	redirect('.');
}

if ($plan['']['valid']) {
	$new = [
		'typ'=>\Type\Cron::OZON_XML,
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
			redirect('/setup/ozon');
		} else {
			db_insert('cron', $new);
			alert('Выгрузка создана!');
			redirect('/setup/ozon');
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
//			redirect('/setup/ozon');
		}
	}
}