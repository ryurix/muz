<?

$code = $config['args'][0];

$row = 0;
if ($code) {
	$q = db_query('SELECT * FROM cron WHERE typ>=100 AND i='.$code);
	$row = db_fetch($q);

	if (!$row) {
		redirect('/plan');
	}
}

if (!$row) {
	$row = array(
		'i'=>0,
		'typ'=>0,
		'name'=>'Новая задача',
		'dt'=>now() + 24*60*60,
		'every'=>86400,
		'data'=>'',
	);
}

$config['name'] = $row['name'];

$data = array_decode($row['data']);
$data['name'] = $row['name'];
$data['every'] = $row['every'];
$data['typ'] = $row['typ'];

$types = \Type\Cron::names(100);

$others = array(0=>'');
$q = db_query('SELECT * FROM cron WHERE typ>=100 AND i<>'.$row['i'].' ORDER BY name');
while ($i = db_fetch($q)) {
	$others[$i['i']] = $i['name'];
}

$plan = [
	''=>array('default'=>$data),
	'name'=>array('name'=>'Название', 'type'=>'line', 'min'=>3),
	'every'=>array('name'=>'Период', 'type'=>'combo', 'values'=>\Form\Cron::EVERY, 'default'=>0),
	'time'=>array('name'=>'Время запуска', 'type'=>'time', 'default'=>0),
	'week'=>array('name'=>'Дни недели', 'type'=>'multich', 'values'=>array(1=>'пн', 2=>'вт', 3=>'ср', 4=>'чт', 5=>'пт', 6=>'сб', 7=>'вс'), 'placeholder'=>'ежедневно'),
	'typ'=>['name'=>'Тип', 'type'=>'combo', 'values'=>$types],

	'follow'=>array('name'=>'Следующая', 'type'=>'multich', 'values'=>$others, 'default'=>array(), 'placeholder'=>'Выберите задачу...'),

	'send'=>array('type'=>'button', 'count'=>2, 1=>'Сохранить', 2=>'Выполнить'),
];

w('request', $plan);
w('invalid', $plan);

$config['plan'] = $plan;

if ($plan['']['valid']) {

	$data = array(
		'time'=>$plan['time']['value'],
		'week'=>$plan['week']['value'],
		'follow'=>$plan['follow']['value'],
	);

	$new = [
		'typ'=>$plan['typ']['value'],
		'name'=>$plan['name']['value'],
		'info'=>'',
		'every'=>$plan['every']['value'],
		'data'=>array_encode($data),
	];

	$new['dt'] = \Cron\Task::next($new, $data);

	if ($plan['send']['value'] == 1)
	{
		if ($row['i']) {
			db_update('cron', $new, array('i'=>$row['i']));
			alert('Задача сохранена');
			redirect('/plan');
		} else {
			db_insert('cron', $new);
			alert('Задача создана!');
			redirect('/plan');
		}
	}

	if ($plan['send']['value'] == 2)
	{
		$info = \Cron\Task::execute($new);

		alert('Задача выполнена: '.$info);
		if ($row['i']) {
			w('ft');
			db_update('cron', [
				'info'=>ft(now(), 1).' '.$info,
			], ['i'=>$row['i']]);
		}
	}
}