<?

$site = count($config['args']) ? $config['args'][0] : 0;
$q = db_query('SELECT * FROM site WHERE i='.$site);

$sites = cache_load('sitename');

if (isset($sites[$site])) {

	$config['name'] = 'Сайт: '.$sites[$site];

	$fields = w('fields-site', $site);
	$plan = $fields + array(
		'send'=>array('type'=>'submit', 'value'=>'Сохранить'),
		'sent'=>array('type'=>'sent'),
	);

	w('request', $plan);
	if ($plan['']['valid'] && $plan['sent']['value']) {
		foreach ($fields as $k=>$v) {
			if ($v['word']) {
				db_update('word', array('value'=>$plan[$k]['value']), array('i'=>$v['word']));
			} else {
				db_insert('word', array('value'=>$plan[$k]['value'], 'site'=>$site, 'dict'=>substr($k, 1)));
			}
		}
		w('cache-word');

		redirect('/dict');
	}
	$config['plan'] = $plan;

} else {
	redirect('/dict');
}

?>