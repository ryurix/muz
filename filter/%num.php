<?

$q = db_query('SELECT * FROM filter WHERE i="'.\Page::arg().'"');
$root = '/filter';

if ($row = db_fetch($q)) {
	\Page::name($row['name']);

	$action = \Page::arg(1, 'edit');

	if ($action == 'edit') {
		$plan = w('plan-filter');
		w('input-param');
		$row['value'] = load_param($row['i']);
		$plan['']['default'] = $row;
		w('request', $plan);

		if ($plan['']['valid'] && $plan['send']['value'] == 1) {
			$data = array(
				'name'=>$plan['name']['value'],
				'info'=>$plan['info']['value'],
				'value'=>php_encode($plan['value']['value']),
			);
			db_update('filter', $data, array('i'=>$row['i']));
			save_param($row['i'], $plan['value']['value']);
			$plan['value']['value'] = load_param($row['i']);
			w('cache-filter');
			\Flydom\Alert::warning('Фильтр изменен');
		} elseif ($plan['']['valid'] && $plan['send']['value'] == 2) {
			db_delete('filter', array(
				'i'=>$row['i']
			));
			w('cache-filter');
			\Flydom\Alert::warning('Фильтр удален');
			\Page::redirect($root);
		}
	}

	$config['plan'] = $plan;
} else {
	\Page::redirect($root);
}

?>