<?

w('clean');
$num = (int)first_int(\Page::arg());

$q = db_query('SELECT * FROM brand WHERE i="'.$num.'"');
$root = '/brand';

if ($row = db_fetch($q)) {
	\Page::name($row['name']);

	$action = \Page::arg(1, 'view');

	if ($action == 'view') {
		$canonical = '<link rel="canonical" href="http://'.$config['domain'].'/brand/'.$row['i'].'-'.str2url($row['name']).'">';
		if (isset($block['head'])) {
			$block['head'].= "\n".$canonical;
		} else {
			$block['head'] = $canonical;
		}

		$config['row'] = $row;
		\Page::body('view');
	} elseif ($action == 'edit') {
		if (\User::is('catalog')) {
			\Action::before('/brand/'.$row['i'], 'смотр');
		}

		$plan = w('plan-brand');
		$plan['']['default'] = $row;
		w('request', $plan);

		if ($plan['']['valid'] && $plan['send']['value'] == 1) {
			db_update('brand', array(
				'code'=>$plan['code']['value'],
				'name'=>$plan['name']['value'],
				'icon'=>$plan['icon']['value'],
				'info'=>$plan['info']['value'],
				'w'=>$plan['w']['value'],
			), array('i'=>$row['i']));
			w('cache-brand');
			\Flydom\Alert::warning('Запись изменена');
			\Page::redirect($root);
		} elseif ($plan['']['valid'] && $plan['send']['value'] == 2) {
			db_delete('brand', array(
				'i'=>$row['i']
			));
			w('cache-brand');
			\Flydom\Alert::warning('Запись удалена');
			\Page::redirect($root);
		}
		$config['plan'] = $plan;
	}
} else {
	\Page::redirect($root);
}

?>