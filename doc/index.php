<?

if (!is_user('doc')) {
	redirect('/');
}

$docs = w('list-doc');
$plan = array(
	''=>array('method'=>'GET', 'type'=>'inline', 'class'=>'auto'),
	'doc'=>array('name'=>'Тип документа', 'type'=>'combo', 'values'=>array(0=>'Все') + $docs, 'class'=>'auto', 'default'=>0),
	'del'=>array('name'=>'Удалить', 'type'=>'int', 'default'=>0),
	'send'=>array('type'=>'submit', 'value'=>'<i class="fa fa-search"></i>'),
);
w('request', $plan);
$config['plan'] = $plan;

if ($plan['']['valid'] && $plan['doc']['value']) {
	$keys = cache_load('keys');
	$doc = $plan['doc']['value'];
	$key = $keys['doc'.$doc];

	if ($key == $plan['del']['value']) {
		$keys['doc'.$doc] = max(0, $key - 1);
		cache_save('keys', $keys);
		db_delete('docs', array('num'=>$key, 'type'=>$doc));
		alert('Документ '.$docs[$doc].' № '.$key.' удалён!');
	}
} elseif ($plan['del']['value'] && $plan['doc']['value'] == 0) {
	$key = $plan['del']['value'];
	if (db_delete('docs', array('i'=>$key))) {
		alert('Документ № '.$key.' удалён!');
	}
}

?>