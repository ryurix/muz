<?

$config['action'] = array(array('href'=>'/rate/new', 'action'=>'Добавить отзыв'));

w('user-config');

$default = array(
	'dt01'=>now() - 7*24*60*60,
	'dt02'=>now(),
	'dt1'=>now() - 7*24*60*60,
	'dt2'=>now(),
	'usr'=>'',
	'state'=>-1,
	'store'=>'',
);

$plan = array(
	''=>array('method'=>'POST', 'default'=>get_user_config('rates', array())),
	'dt01'=>array('name'=>'Дата создания от', 'type'=>'date', 'width'=>90, 'class'=>'auto'),
	'dt02'=>array('name'=>'Дата создания до', 'type'=>'date', 'width'=>90, 'class'=>'auto'),
	'dt1'=>array('name'=>'Дата отзыва от', 'type'=>'date', 'width'=>90, 'class'=>'auto'),
	'dt2'=>array('name'=>'Дата отзыва до', 'type'=>'date', 'width'=>90, 'class'=>'auto'),
	'usr'=>array('name'=>'Пользователь', 'type'=>'line', 'placeholder'=>'Пользователь', 'class'=>'auto2'),
	'state'=>array('name'=>'Статус', 'type'=>'combo', 'values'=>array(-1=>'Все', 0=>'Новые', 10=>'Проверенные'), 'class'=>'auto'),
	'store'=>array('name'=>'Товар', 'type'=>'line', 'placeholder'=>'Наименование', 'class'=>'auto2'),
	'send'=>array('type'=>'button', 'count'=>2, 1=>'Фильтровать', 2=>'Сбросить'),
);

set_array($plan, 'default', $default);

w('request', $plan);

$values = w('values', $plan);

set_user_config('rates', $values);

if ($plan['send']['value'] == 2) {
	set_user_config('rates', $default);
	set_array($plan, 'value', $default);
}

save_user_config();

$config['plan'] = $plan;

?>