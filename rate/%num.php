<?

$q = db_query('SELECT rate.*,store.name sname,store.model,store.url,store.brand FROM rate LEFT JOIN store ON rate.store=store.i WHERE rate.i='.$config['args'][0]);
if ($row = db_fetch($q)) {
	$config['name'] = $row['name'];

	$plan = w('plan-rate');
	$plan['send']['count'] = 2;
	$plan['']['default'] = $row;
	w('request', $plan);
	if ($plan['']['valid'] && $plan['send']['value'] == 1) {
		db_update('rate', array(
			'name'=>$plan['name']['value'],
			'rate'=>$plan['rate']['value'],
			'plus'=>$plan['plus']['value'],
			'minus'=>$plan['minus']['value'],
			'body'=>$plan['body']['value'],
			'state'=>$plan['state']['value'],
			'dt'=>$plan['dt']['value'],
		), array('i'=>$row['i']));
		alert('Отзыв сохранён!', 'success');
		redirect('.', 302);
	}
	if ($plan['']['valid'] && $plan['send']['value'] == 2) {
		db_delete('rate', array('i'=>$row['i']));
		alert('Отзыв удалён!');
		redirect('.', 302);
	}
	$config['plan'] = $plan;

} else {
	redirect('.');
}

?>