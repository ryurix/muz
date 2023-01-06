<?

$plan = array(
	''=>array('method'=>'POST'),
	'file'=>array('type'=>'file'),
	'send'=>array('type'=>'button', 'count'=>1, 1=>'Загрузить'),
);

w('request', $plan);

if ($plan['send']['value'] == 1 && strlen($plan['file']['value'])) {

	w('u8');
	w('clean');

	$basket = array();

	$f = u8fopen($plan['file']['value']);
	$i = fgetcsv($f, 1000, ';');
	while (($i = fgetcsv($f, 1000, ';')) !== FALSE) {
		if (!isset($i[1])) { continue; }
		$code = clean_09(substr($i[0], 1));
		$count = clean_09($i[3]);

		if ($code && $count) {
			$basket[$code] = array('c'=>$count, 'i'=>'');
		}
	}
	fclose($f);

	$_SESSION['basket'] = $basket;
//	print_pre($basket);
	redirect('/basket');
}

$config['plan'] = $plan;

?>