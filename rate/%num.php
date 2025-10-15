<?

$q = db_query('SELECT rate.*,store.name sname,store.model,store.url,store.brand FROM rate LEFT JOIN store ON rate.store=store.i WHERE rate.i='.\Page::arg());
if ($row = db_fetch($q)) {
	\Page::name($row['name']);

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
		\Flydom\Alert::success('Отзыв сохранён!', 'success');
		\Page::redirect('.', 302);
	}
	if ($plan['']['valid'] && $plan['send']['value'] == 2) {
		db_delete('rate', array('i'=>$row['i']));
		\Flydom\Alert::warning('Отзыв удалён!');
		\Page::redirect('.', 302);
	}
	$config['plan'] = $plan;

} else {
	\Page::redirect('.');
}

?>