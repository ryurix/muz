<?

$code = $config['args'][0];

$row = 0;
if ($code) {
	$q = db_query('SELECT * FROM cron WHERE typ='.\Type\Cron::PRICES.' AND i='.$code);
	$row = db_fetch($q);

	if (!$row) {
		redirect('/prices/plan');
	}
}

if (!$row) {
	$row = [
		'i'=>0,
		'typ'=>10,
		'name'=>'Новая задача',
		'dt'=>now() + 24*60*60,
		'every'=>86400,
		'data'=>'',
	];
}

$config['name'] = $row['name'];

$data = array_decode($row['data']);
$data['name'] = $row['name'];
$data['every'] = $row['every'];

$others = array(0=>'');
$q = db_query('SELECT * FROM cron WHERE typ='.\Type\Cron::PRICES.' AND i<>'.$row['i'].' ORDER BY name');
while ($i = db_fetch($q)) {
	$others[$i['i']] = $i['name'];
}

$prices = \Type\Price::names();

$plan = array(
	''=>['default'=>$data],
	'name'=>['name'=>'Название', 'type'=>'line', 'min'=>3],
	'every'=>['name'=>'Период', 'type'=>'combo', 'values'=>array(0=>'Не запускать автоматически', 1=>'Ежедневно по расписанию', 3600=>'1 час', 28800=>'8 часов', 64800=>'18 часов', 86400=>'1 день', 259200=>'3 дня'), 'default'=>0],
	'time'=>['name'=>'Время запуска', 'type'=>'time', 'default'=>0],
	'week'=>['name'=>'Дни недели', 'type'=>'multich', 'values'=>[1=>'пн', 2=>'вт', 3=>'ср', 4=>'чт', 5=>'пт', 6=>'сб', 7=>'вс'], 'placeholder'=>'ежедневно'],

	'price'=>['name'=>'Тип цены', 'type'=>'combo', 'values'=>$prices],

	'send'=>['type'=>'button', 'count'=>2, 1=>'Сохранить', 2=>'Выполнить'],
);

w('request', $plan);
w('invalid', $plan);

$config['plan'] = $plan;

if ($plan['']['valid']) {
	$data = [
		'time'=>$plan['time']['value'],
		'week'=>$plan['week']['value'],
		'price'=>$plan['price']['value'],
	];

	$new = [
		'typ'=>\Type\Cron::PRICES,
		'name'=>$plan['name']['value'],
		'every'=>$plan['every']['value'],
		'info'=>'',
		'dt'=>now() + $plan['every']['value'],
		'data'=>array_encode($data),
	];

	$new['dt'] = \Cron\Task::next($new, $data);

	if ($plan['send']['value'] == 1) {

		if ($row['i']) {
			db_update('cron', $new, ['i'=>$row['i']]);
			alert('Задача сохранена');
			redirect('/prices/plan');
		} else {
			db_insert('cron', $new);
			alert('Задача создана!');
			redirect('/prices/plan');
		}
	}

	if ($plan['send']['value'] == 2) {

		$info = \Cron\Task::execute($new, $data);
		//$info.= \Cron\Task::follow($data['follow']);

		alert('Задача выполнена: '.$info);
		if ($row['i']) {
			w('ft');
			db_update('cron', [
				'info'=>ft(now(), 1).' '.$info,
			], ['i'=>$row['i']]);
//			redirect('/setup/ozon');
		}
	}
}