<?

$plan = array(
	''=>array('method'=>'POST'),
	'send'=>array('type'=>'button', 'count'=>2, 1=>'Удалить', 2=>'Отмена', 'class'=>array(1=>'btn-danger', 2=>'btn-success')),
);
w('request', $plan);

if ($plan['']['valid']) {
	if ($plan['send']['value'] == 1) {
		alert('Все товары сняты с продажи!');
		db_update('store', array(
			'price'=>0,
			'speed'=>5,
			'w'=>200,
			'count'=>'',
		), 'i>0');
	} elseif ($plan['send']['value'] == 2) {
	}
	redirect('/sync');
}
$config['plan'] = $plan;

?>