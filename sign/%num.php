<?

$q = db_query('SELECT * FROM sign WHERE i="'.\Page::arg().'"');
$root = '/sign';

if ($row = db_fetch($q)) {
	\Page::name($row['name']);

	$action = \Page::arg(1, 'edit');

	if ($action == 'edit') {
		$plan = w('plan-sign');
		$plan['']['default'] = $row;
		w('request', $plan);

		if ($plan['']['valid'] && $plan['send']['value'] == 1) {
			$data = array(
				'name'=>$plan['name']['value'],
				'info'=>$plan['info']['value'],
			);

			$mini = '';
			if (is_file($plan['mini']['value'])) {
				$mini = '/files/sign/'.$plan['mini']['filename'];
				$file = \Config::ROOT.substr($mini, 1);
				xcopy($plan['mini']['value'], $file);
				if (!is_file($file)) {
					$mini = '';
				}
			}
			if (strlen($mini)) {
				$data['mini'] = $mini;
			}

			db_update('sign', $data, array('i'=>$row['i']));
			w('cache-sign');
			\Flydom\Alert::warning('Знак изменен');
			\Page::redirect($root);
		} elseif ($plan['']['valid'] && $plan['send']['value'] == 2) {
			db_delete('sign', array(
				'i'=>$row['i']
			));
			w('cache-sign');
			\Flydom\Alert::warning('Знак удален');
			\Page::redirect($root);
		}
	}

	$config['plan'] = $plan;
} else {
	\Page::redirect($root);
}

?>