<?

$plan = array(
	''=>array('method'=>'POST'),
	'_login'=>array('name'=>'Имя', 'type'=>'line'),
	'_pass'=>array('name'=>'Пароль', 'type'=>'password', 'class'=>'input form-control'),
	'send'=>array('type'=>'button', 1=>'Вход', 2=>(is_user() ? 'Выход' : 'Напомнить пароль'),
		'count'=>2,
		'class'=>array(1=>'btn-success', 2=>'btn-warning')
	),
);

w('request', $plan);

if ($plan['']['valid']) {
	if ($plan['send']['value'] == 2) {
		if (is_user()) {
			global $user;
			access_create($user[0], 0);
			$plan['send']['count'] = 1;
		} else {
			w('remind', $plan['_login']['value']);
		}
	}
}

if (is_user()) {
	$config['name'] = $_SESSION['name'];
}

$config['plan'] = $plan;

?>