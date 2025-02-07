<?

$code = $config['args'][0];

$row = 0;
if ($code) {
	$q = db_query('SELECT * FROM cron WHERE typ=1 AND i='.$code);
	$row = db_fetch($q);

	if (!$row) {
		redirect('/setup/yml');
	}
}

if (!$row) {
	$row = array(
		'i'=>0,
		'typ'=>1,
		'name'=>'Новая выгрузка',
		'dt'=>now() + 24*60*60,
		'every'=>86400,
		'data'=>"'form'=>'yandex'",
	);
}

$config['name'] = $row['name'];

$data = array_decode($row['data']);
$data['name'] = $row['name'];
$data['every'] = $row['every'];

if (strlen($data['form']) <= 2) {
	switch($data['form']) {
		case 0: $data['form'] = 'yandex'; break;
		case 1: $data['form'] = 'yandex+count'; break;
		case 10: $data['form'] = 'goods'; break;
		case 20: $data['form'] = 'cdek'; break;
	}
}

$forms = array(
	'yandex'=>'Яндекс',
	'yandex+count'=>'Яндекс+количество',
	'yandex+count+fullmodel'=>'Яндекс+количество (полное наименование в model)',
	'goods'=>'Гудс',
	'cdek'=>'СДЕК',
	'dynatone'=>'Яндекс+динатон',
);

$plan = array(
	''=>array('default'=>$data),
	'name'=>array('name'=>'Название', 'type'=>'line', 'min'=>3),
	'every'=>array('name'=>'Период', 'type'=>'combo', 'values'=>array(0=>'Не запускать автоматически', 1=>'Ежедневно по расписанию', 3600=>'1 час', 28800=>'8 часов', 64800=>'18 часов', 86400=>'1 день', 259200=>'3 дня')),
	'time'=>array('name'=>'Время запуска', 'type'=>'time', 'default'=>'1'),
	'week'=>array('name'=>'Дни недели', 'type'=>'multich', 'values'=>array(1=>'пн', 2=>'вт', 3=>'ср', 4=>'чт', 5=>'пт', 6=>'сб', 7=>'вс'), 'placeholder'=>'ежедневно'),
	'form'=>array('name'=>'Формат', 'type'=>'combo', 'values'=>$forms),
	'filename'=>array('name'=>'Имя файла', 'type'=>'line', 'default'=>'yandex.xml'),
	'min'=>array('name'=>'Мин. количество', 'type'=>'int', 'default'=>0),
	'minus'=>array('name'=>'Вычет', 'type'=>'int', 'default'=>0),
	'type'=>['name'=>'Тип цены', 'type'=>'combo', 'values'=>\Type\Price::names(), 'default'=>0],
	'price'=>array('name'=>'Мин. цена', 'type'=>'int', 'default'=>500),
	'price2'=>array('name'=>'Макс. цена', 'type'=>'int', 'default'=>1000000),
	'site'=>array('name'=>'Сайт', 'type'=>'line', 'default'=>'muzmart.com'),
	'city'=>array('name'=>'Город', 'type'=>'combo', 'values'=>array(0=>'') + cache_load('city'), 'default'=>0),
	'vendor'=>array('name'=>'Поставщики', 'type'=>'multich', 'values'=>cache_load('vendor'), 'placeholder'=>'Выберите поставщиков...'),
	'send'=>array('type'=>'button', 'count'=>3, 1=>'Сохранить', 2=>'Выгрузить', 3=>'Удалить', 'confirm'=>[3=>'Удалить выгрузку?']),
);

w('request', $plan);
w('invalid', $plan);

if ($plan['send']['value'] == 3) {
	\Db::delete('cron', ['i'=>$row['i']]);
	redirect('.');
}

if ($plan['']['valid']) {

	$data = [
		'time'=>$plan['time']['value'],
		'week'=>$plan['week']['value'],
		'form'=>$plan['form']['value'],
		'filename'=>$plan['filename']['value'],
		'min'=>$plan['min']['value'],
		'minus'=>$plan['minus']['value'],
		'type'=>$plan['type']['value'],
		'price'=>$plan['price']['value'],
		'price2'=>$plan['price2']['value'],
		'site'=>$plan['site']['value'],
		'city'=>$plan['city']['value'],
		'vendor'=>$plan['vendor']['value'],
	];

	$new = [
		'typ'=>1,
		'name'=>$plan['name']['value'],
		'info'=>'<a href="/files/'.$data['filename'].'">'.$data['filename'].'</a>',
		'every'=>$plan['every']['value'],
		'data'=>array_encode($data),
	];

	$new['dt'] = \Cron\Task::next($new, $data);

	if ($plan['send']['value'] == 1) {

		if ($row['i']) {
			db_update('cron', $new, ['i'=>$row['i']]);
			alert('Выгрузка обновлена');
			redirect('/setup/yml');
		} else {
			db_insert('cron', $new);
			alert('Выгрузка создана!');
			redirect('/setup/yml');
		}
	}

	if ($plan['send']['value'] == 2) {

		$info = \Cron\Task::execute($new, $data);
		//$info.= \Cron\Task::follow($data['follow']);

		alert('Выгрузка выполнена! '.$info);
		if ($row['i']) {
			w('ft');
			db_update('cron', [
				'info'=>ft(now(), 1).' '.$info,
			], ['i'=>$row['i']]);
//			redirect('/setup/yml');
		}
	}
}