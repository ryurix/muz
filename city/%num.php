<?

$q = db_query('SELECT * FROM city WHERE i='.$config['args'][0]);
if ($row = db_fetch($q)) {
	$config['name'] = $row['name'];

	$plan = w('plan-city');
	$plan['']['default'] = $row;
	w('request', $plan);

	if ($plan['']['valid'] && $plan['send']['value'] == 1) {
		db_update('city', array(
			'hide' => $plan['hide']['value'],
			'name' => $plan['name']['value'],
			'region'=>$plan['region']['value'],
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
		), array('i'=>$row['i']));
		w('comment-log', $dummy = array(
			'theme'=>'city-'.$row['i'],
			'body'=>'Правка',
		));
		w('cache-city');
		redirect('.', 302);
	}
	$config['plan'] = $plan;

} else {
	redirect('.');
}

?>