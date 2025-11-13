<?

$code = \Page::arg();
\Cron\Form::types(\Cron\Type::names(10, 19));

$row = \Cron\Form::load($code);
if (empty($row)) {
	if ($code) {
		\Flydom\Alert::danger('Задача не найдена: ' + $code);
		\Page::redirect('/setup/ozon');
	}

	$row = array(
		'i'=>0,
		'typ'=>\Cron\Type::OZON,
		'name'=>'Новая выгрузка Ozon',
		'dt'=>\Config::now() + 24*60*60,
		'every'=>86400,
		'week'=>[],
		'follow'=>[],
	);
}

$follow = \Db::fetchMap('SELECT i,name FROM cron WHERE typ='.\Cron\Type::OZON.' AND i<>'.$code.' ORDER BY name');
$cabinet = \Db::fetchMap('SELECT usr,name FROM cabinet WHERE typ='.\Cron\Type::OZON.' ORDER BY name');

$plan = ['usr'=>new \Flydom\Input\Select('Кабинет', $cabinet)];

$type = \Flydom\Clean::firstUint($_REQUEST['typ'] ?? $row['typ']);
switch ($type) {
	case \Cron\Type::OZON_SET_PRICE:
		$plan['test'] = new \Flydom\Input\Line('Тестовый артикул');
		break;
	case \Cron\Type::OZON_SET_STOCK:
		$plan['test'] = new \Flydom\Input\Line('Тестовый артикул');
		$plan['upd'] = new \Flydom\Input\Checkbox('Только обновления');
		$plan['zero'] = new \Flydom\Input\Checkbox('Обнулить');
		$plan['min'] = new \Flydom\Input\Integer('Минимум');
		$plan['minus'] = new \Flydom\Input\Integer('Вычет');
		$plan['vendor'] = new \Flydom\Input\Multiselect('Поставщики', \Flydom\Cache::get('vendor'));
		break;
}

\Cron\Form::start($plan, $row);

\Page::name(\Cron\Form::name());

\Cron\Form::process('/setup/ozon');

/*

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
	'every'=>['name'=>'Период', 'type'=>'combo', 'values'=>\Cron\Form::EVERY, 'default'=>0],
	'time'=>array('name'=>'Время запуска', 'type'=>'time', 'default'=>0),
	'week'=>array('name'=>'Дни недели', 'type'=>'multich', 'values'=>array(1=>'пн', 2=>'вт', 3=>'ср', 4=>'чт', 5=>'пт', 6=>'сб', 7=>'вс'), 'placeholder'=>'ежедневно'),
	'form'=>array('name'=>'Формат', 'type'=>'combo', 'values'=>$forms),

	'client'=>array('name'=>'Код клиента', 'type'=>'line', 'default'=>'', 'min'=>1),
	'api'=>array('name'=>'Ключ API', 'type'=>'line', 'default'=>'', 'min'=>1),
	'warehouse'=>['name'=>'ID Склада', 'type'=>'line', 'default'=>'', 'min'=>1],

	'price'=>['name'=>'Тип цены', 'type'=>'combo', 'values'=> \Price\Type::names(), 'default'=>0],
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
	\Page::redirect('.');
}

if ($plan['']['valid']) {
	$new = [
		'typ'=>\Cron\Type::OZON,
		'form'=>$plan['form']['value'],
		'name'=>$plan['name']['value'],
		'info'=>'',
		'dt'=>\Config::now() + $plan['every']['value'],
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
	$new['dt'] = \Flydom\Cron\Task::next($new);

	if ($plan['send']['value'] == 1) {

		if ($row['i']) {
			db_update('cron', $new, ['i'=>$row['i']]);
			\Flydom\Alert::warning('Выгрузка сохранена');
			\Page::redirect('/setup/ozon');
		} else {
			db_insert('cron', $new);
			\Flydom\Alert::warning('Выгрузка создана!');
			\Page::redirect('/setup/ozon');
		}
	}

	if ($plan['send']['value'] == 2) {

		$info = \Flydom\Cron\Task::execute($new, $data);
		$info.= \Flydom\Cron\Task::follow($data['follow']);

		\Flydom\Alert::warning('Выгрузка выполнена: '.$info);
		if ($row['i']) {
			w('ft');
			db_update('cron', [
				'info'=>ft(\Config::now(), 1).' '.$info
			], ['i'=>$row['i']]);
//			\Page::redirect('/setup/ozon');
		}
	}
}

//*/