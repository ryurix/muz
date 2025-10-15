<?

$q = db_query('SELECT * FROM vendor WHERE i="'.\Page::arg().'"');
$root = '/vendor';

if ($row = db_fetch($q)) {
	\Page::name($row['name']);
	\Action::before('/vendor', 'Список');

	$action = \Page::arg(1, 'view');

	if ($action == 'edit') {
		$plan = w('plan-vendor');
		$plan['']['default'] = $row;
		w('request', $plan);

		if ($plan['']['valid'] && $plan['send']['value'] == 1) {
			db_update('vendor', array(
				'name'=>$plan['name']['value'],
				'typ'=>$plan['typ']['value'],
				'up'=>$plan['up']['value'],
				'w'=>$plan['w']['value'],
				'days'=>$plan['days']['value'],
				'price'=>$plan['price']['value'],
				'prmin'=>$plan['prmin']['value'],
				'city'=>$plan['city']['value'],
//				'speed'=>$plan['speed']['value'],
				'ccode'=>$plan['ccode']['value'],
				'cname'=>$plan['cname']['value'],
				'ctype'=>$plan['ctype']['value'],
				'cbrand'=>$plan['cbrand']['value'],
				'ccount'=>$plan['ccount']['value'],
				'cprice'=>$plan['cprice']['value'],
				'copt'=>$plan['copt']['value'],
				'curr'=>$plan['curr']['value'],
				'short'=>$plan['short']['value'],
				'info'=>$plan['info']['value'],
			), array('i'=>$row['i']));
			\Flydom\Alert::warning('Запись изменена');
			w('cache-vendor');
			\Page::redirect($root);
		} elseif ($plan['']['valid'] && $plan['send']['value'] == 2) {
			db_delete('vendor', array(
				'i'=>$row['i']
			));
			\Flydom\Alert::warning('Запись удалена');
			w('cache-vendor');
			\Page::redirect($root);
		}
		$config['plan'] = $plan;
	} elseif ($action == 'view') {
		\Page::name($row['name']);
		$block['body'] = $row['info'];
	}
} else {
	\Page::redirect($root);
}

?>