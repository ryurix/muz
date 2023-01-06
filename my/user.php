<?

if (!is_user()) {
	redirect('/');
}

if (is_user('order')) {
	$config['action'] = array(array(
		'action'=>'Финансы',
		'href'=>'/user/'.$_SESSION['i'].'/sales',
	));
}

$q = db_query('SELECT * FROM user WHERE i='.$_SESSION['i']);
$row = db_fetch($q);
$row['pass'] = '';
db_close($q);

$plan = w('plan-user');
//$plan['login']['readonly'] = 1;
unset($plan['roles']);
unset($plan['vendor']);

if (strlen($row['pay']) > 2) {
	w('clean');
	if (is_php_encoded($row['pay'])) {
		$row = $row + php_decode($row['pay']);
	} else {
		$row = $row + array_decode($row['pay']);
	}
}

$plan['']['default'] = $row;

w('request', $plan);
$plan['pay']['ul'] = $plan['ul']['value'];

if (strlen($plan['phone']['value'])) {
	$plan['phone']['placeholder'] = htmlspecialchars($plan['phone']['value']);
}

if ($plan['phone']['value'] != $row['phone'] && strlen($plan['phone']['value'])) {
	w('clean');
	if (db_result('SELECT COUNT(*) FROM user WHERE phone="'.$plan['phone']['value'].'" AND i<>'.$row['i'])) {
		$plan['phone']['iv'] = 1;
		$plan['phone']['valid'] = 0;
		$plan['']['valid'] = 0;
	}
}

if ($plan['email']['value'] != $row['email'] && strlen($plan['email']['value'])) {
	w('clean');
	if (db_result('SELECT COUNT(*) FROM user WHERE email="'.clean_mail($plan['email']['value']).'" AND i<>'.$row['i'])) {
		$plan['email']['iv'] = 1;
		$plan['email']['valid'] = 0;
		$plan['']['valid'] = 0;
	}
}

if ($plan['']['valid'] && $plan['send']['value'] == 1) {
	w('u8');

	$name = u8capitalize($plan['name']['value']);

	$pay = array(
		'ptype'=>$plan['ptype']['value'],
		'uname'=>$plan['uname']['value'],
		'head'=>$plan['head']['value'],
		'uadr'=>$plan['uadr']['value'],
		'fadr'=>$plan['fadr']['value'],
		'inn'=>$plan['inn']['value'],
		'kpp'=>$plan['kpp']['value'],
		'okpo'=>$plan['okpo']['value'],
		'bank'=>$plan['bank']['value'],
		'bik'=>$plan['bik']['value'],
		'bras'=>$plan['bras']['value'],
		'bkor'=>$plan['bkor']['value'],
	);

	w('clean');
	$data = array(
		'quick'=>'',
		'name'=>$name,
		'email'=>clean_mail($plan['email']['value']),
		'phone'=>clean_phone($plan['phone']['value']),
		'city'=>$plan['city']['value'],
		'adres'=>$plan['adres']['value'],
		'pay'=>php_encode($pay),
		'ul'=>$plan['ul']['value'],
		'dost'=>$plan['dost']['value'],
//		'note'=>$plan['note']['value'],
		'spam'=>$plan['spam']['value'],
	);

	foreach ($data as $k=>$v) {
		$_SESSION[$k] = $v;
	}

	if (strlen($plan['pass1']['value']) > 0 && $plan['pass1']['value'] == $plan['pass2']['value']) {
		$data['pass'] = $plan['pass1']['value'];
		alert('Пароль изменён!');
		$plan['pass1']['value'] = '';
		$plan['pass2']['value'] = '';
	}

	db_update('user', $data, array(
		'i'=>$_SESSION['i'],
	));

	w('cache-user', $_SESSION['i']);
	alert('Анкета сохранена!');
}

$config['plan'] = $plan;

?>