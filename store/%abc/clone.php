<?

w('clean');
$q = db_query('SELECT * FROM store WHERE i='.first_int($config['args'][0]));
if ($row = db_fetch($q)) {
	w('store-action', $row);
} else {
	alert('Товар не найден!');
	redirect('..');
}

$plan = array(
	'send'=>array('type'=>'button', 'count'=>1, 1=>'Клонировать', 'class'=>'btn-warning'),
);

w('request', $plan);

if ($plan['send']['value'] == 1) {
	w('keys');
	$i = key_next('store');

	xcopy($config['root'].'files/store/'.$row['i'].'/', $config['root'].'files/store/'.$i.'/');
	
	$row['url'] = str_replace($row['i'].'-', $i.'-', $row['url']);
	$row['name'] = $row['name'].' (клон)';
	$row['dt'] = now();
	$row['icon'] = str_replace('/'.$row['i'].'/', '/'.$i.'/', $row['icon']);
	$row['mini'] = str_replace('/'.$row['i'].'/', '/'.$i.'/', $row['mini']);
	$row['pic'] =  str_replace('/'.$row['i'].'/', '/'.$i.'/', $row['pic']);
	$row['pics'] = str_replace('/'.$row['i'].'/', '/'.$i.'/', $row['pics']);
	$row['files']= str_replace('/'.$row['i'].'/', '/'.$i.'/', $row['files']);
	$row['i'] = $i;

	db_insert('store', $row);

	redirect('/store/'.$row['url']);
}

$config['plan'] = $plan;

?>