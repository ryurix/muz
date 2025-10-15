<?

w('chosen.js');

$symbols = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
$code = '';
for ($i=0; $i<8; $i++) {
	$code.= substr($symbols, rand(0, 33), 1);
}

$plan = w('plan-sale');
$plan['code']['type'] = 'line';
$plan['code']['default'] = $code;
$plan['up']['default'] = array(0);
$plan['brand']['default'] = array(0);
w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value'] == 1) {
	w('u8');
	w('clean');
	$data = array(
		'code'=>u8upper(clean_alpha($plan['code']['value'])),
		'name'=>$plan['name']['value'],
		'dt'=>\Config::now(),
		'user'=>$_SESSION['i'],
		'dt2'=>$plan['dt2']['value'],
		'perc'=>$plan['perc']['value'],
		'partner'=>$plan['partner']['value'],
		'up'=>implode(',', $plan['up']['value']),
		'brand'=>implode(',', $plan['brand']['value']),
	);
	db_insert('sale', $data);
	\Flydom\Alert::warning('Скидка добавлена');
	\Page::redirect('/sales/list');
} else {
	$config['plan'] = $plan;
}

?>