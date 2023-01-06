<?

$plan = w('plan-city');
$plan['hide']['default'] = 0;
$plan['w']['default'] = 100;
w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value']==1) {
	$city = $plan['name']['value'];
	if (db_result('SELECT COUNT(*) FROM city WHERE LOWER(name)="'.strtolower($city).'"')) {
		alert('Город с названием "'.$city.'" уже есть в базе!');
	} else {
		db_insert('city', array(
			'hide' => 0,
			'region'=>$plan['region']['value'],
			'name' => $city,
			'w' => $plan['w']['value'],
			'phone'=>$plan['phone']['value'],
			'mail'=>$plan['mail']['value'],
			'adres'=>$plan['adres']['value'],
			'sign'=>$plan['sign']['value'],
			'site'=>$plan['site']['value'],
			'robots'=>$plan['robots']['value'],
			'metrics'=>$plan['metrics']['value'],
			'metrico'=>$plan['metrico']['value'],
			'head'=>$plan['head']['value'],
		));
		w('cache-city');
		redirect('.', 302);
	}
}

$config['plan'] = $plan;

?>