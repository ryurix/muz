<?

$ups = w('catalog-all');

$plan = array(
	''=>array('method'=>'POST'),
	'up'=>array('name'=>'Раздел', 'type'=>'combo2', 'values'=>$ups, 'width'=>400),
	'send'=>array('type'=>'button', 'count'=>1, 1=>'Перенести', 'class'=>'btn-warning'),
);

w('request', $plan);

$config['plan'] = $plan;

if ($plan['']['valid'] && $plan['send']['value']) {
//	db_query('UPDATE store SET up='.$plan['up']['value'].' WHERE 0=(SELECT COUNT(*) FROM catalog WHERE i=store.up)');

	$ups[-1] = '';	
	db_query('UPDATE catalog SET up='.$plan['up']['value'].' WHERE up NOT IN ('.implode(',', array_keys($ups)).')');

	w('catalog-cache');
	$ups = w('catalog-all');

	db_query('UPDATE store SET up='.$plan['up']['value'].' WHERE up NOT IN ('.implode(',', array_keys($ups)).')');

	db_query('DELETE FROM sync WHERE store>0 AND 0=(select count(*) from store where sync.store=store.i)');
}

?>