<?

if (!is_user()) {
	$plan = array(
		''=>array('method'=>'POST'),
		'login'=>array('name'=>'Email / Телефон', 'type'=>'line', 'width'=>'100%'),
//		'_pass'=>array('name'=>'Пароль', 'type'=>'password'),
		'send'=>array('type'=>'button', 1=>'Напомнить пароль',
			'count'=>1,
			'class'=>'btn-warning'
		),
	);

	w('request', $plan);

	if ($plan['']['valid']) {
		if ($plan['send']['value'] == 1) {
			w('remind', $plan['login']['value']);
		}
	}

	$config['plan'] = $plan;
	refile('remind.html');
}

?>