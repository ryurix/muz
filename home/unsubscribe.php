<?

$plan = array(
	'mail'=>array('type'=>'mail'),
	'code'=>array('type'=>'line'),
);

w('request', $plan);

if ($plan['']['valid']) {
	if ($plan['code']['value'] == md5($plan['mail']['value'].'-unsubscribe')) {
		db_update('user', array('spam'=>0), array('login'=>$plan['mail']['value']));
	} else {
//		redirect('/');
		alert('Ошибка отписки от рассылки.');
	}
}

?>