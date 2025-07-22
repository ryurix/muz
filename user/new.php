<?

$plan = w('plan-user');
$plan['color']['default'] = 0;
$plan['roles'] = array('type'=>'hidden', 'value'=>'');
$plan['vendor']['values'] = [0=>''] + $plan['vendor']['values'];
$plan['vendor']['default'] = 0;

w('request', $plan);
$plan['pay']['ul'] = $plan['ul']['value'];

if ($plan['']['valid'] && $plan['send']['value'] == 1) {
	w('search');
	$quick = $plan['login']['value'].$plan['name']['value'].$plan['phone']['value'];
	$quick.= $plan['adres']['value'].$plan['pay']['value'];
	$quick = search_quick($quick);

	if ($plan['vendor']['value']) {
		$cfg = php_encode(array('vendor'=>$plan['vendor']['value']));
	} else {
		$cfg = '';
	}

	$data = array(
		'quick'=>$quick,
		'dt'=>now(),
		'login'=>$plan['login']['value'],
		'pass'=>$plan['pass']['value'],
		'name'=>$plan['name']['value'],
		'phone'=>$plan['phone']['value'],
		'email'=>$plan['email']['value'],
		'city'=>$plan['city']['value'],
		'adres'=>$plan['adres']['value'],
		'pay'=>$plan['pay']['value'],
		'ul'=>$plan['pay']['ul'],
		'spam'=>$plan['spam']['value'],
		'note'=>$plan['note']['value'],
		'color'=>$plan['color']['value'],
		'dost'=>$plan['dost']['value'],
		'config'=>$cfg,
	);
	if (is_user('admin')) {
		$data['roles'] = $plan['roles']['value'];
	}
	db_insert('user', $data);
	alert('Пользователь добавлен');
	redirect('/user/');
} else {
	$config['plan'] = $plan;
//	print_pre($plan);
}

?>