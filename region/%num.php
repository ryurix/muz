<?

$q = db_query('SELECT * FROM region WHERE i='.\Page::arg());
if ($row = db_fetch($q)) {
	\Page::name($row['name'];

	$plan = w('plan-region');
	$plan['']['default'] = $row;
	w('request', $plan);

	if ($plan['']['valid'] && $plan['send']['value'] == 1) {
		db_update('region', array(
			'name' => $plan['name']['value'],
			'w' => $plan['w']['value'],
		), array('i'=>$row['i']));
		w('comment-log', $dummy = array(
			'theme'=>'region-'.$row['i'],
			'body'=>'Правка',
		));
		\Page::redirect('.', 302);
	}
	$config['plan'] = $plan;

} else {
	\Page::redirect('.');
}

?>