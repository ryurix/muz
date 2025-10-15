<?

$catalog = w('catalog-def');
$up = w('catalog-up', $catalog);
$plan = w('plan-catalog', $up);
$plan['up']['default'] = $catalog;
$plan['w']['default'] = 100;
w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value'] == 1) {
	$data = array(
		'up'=>$plan['up']['value'],
		'tag0'=>$plan['tag0']['value'],
		'tag1'=>$plan['tag1']['value'],
		'tag2'=>$plan['tag2']['value'],
		'tag3'=>$plan['tag3']['value'],
		'name'=>$plan['name']['value'],
		'name2'=>$plan['name2']['value'],
	//	'url'=>$plan['url']['value'],
		'short'=>$plan['short']['value'],
		'info'=>$plan['info']['value'],
		'size'=>$plan['size']['value'],
		'hide'=>$plan['hide']['value'],
		'w'=>$plan['w']['value'],
		'filter'=>$plan['filter']['value'],
		'brand'=>'',
		'google'=>$plan['google']['value'],
	);
	$icon = w('catalog-icon', $plan);
	if (strlen($icon)) {
		$data['icon'] = $icon;
	}
	db_insert('catalog', $data);

	$id = db_insert_id();
	w('clean');
	$url = $plan['url']['value'];
	$url = strlen($url) ? $url : str2url($plan['name']['value']);
	$url = $id.'-'.$url;
	db_update('catalog', array('url'=>$url), array('i'=>$id));

	w('catalog-cache');
	\Flydom\Alert::warning('Раздел '.$data['name2'].' создан!');
	\Page::redirect('/catalog/'.$url.'/edit');
} else {
	$config['plan'] = $plan;
}

?>