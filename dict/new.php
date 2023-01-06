<?

$plan = w('plan-dict');
w('request', $plan);

$config['action'] = array(
	array('href'=>'/dict', 'action'=>'Словарь'),
);

if ($plan['']['valid'] && $plan['sent']['value']) {
	$data = array(
		'name'=>$plan['name']['value'],
		'code'=>$plan['code']['value'],
	);
	db_insert('dict', $data);
	alert('Слово добавлено');
	redirect('/dict/');
} else {
	$config['plan'] = $plan;
}

?>