<?

$code = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';

$q = db_query('SELECT * FROM menu WHERE code="'.$code.'"');

if ($row = db_fetch($q)) {
	\Page::name($row['name']);

	$dummy = $row['code'];
	$plan = w('plan-menu', $dummy);
	$plan['']['default'] = $row;
	$code = $row['code'];
	w('u8');
	$pos = strrpos($code, '/');
	if ($pos !== false) {
		$up = u8sub($code, 0, $pos);
		if ($up && $up == $row['up']) {
			$code = u8sub($code, $pos+1);
		}
	}
	$plan['']['default']['code'] = $code;
	w('request', $plan);

	if ($plan['']['valid'] && $plan['send']['value'] == 1) {
		w('clean');
		$code = $plan['code']['value'];
		$pos = strrpos($code, '/');
		if ($pos !== false) {
			$up = u8sub($code, 0, $pos);
			if ($up && $up == $plan['up']['value']) {
				$code = u8sub($code, $pos+1);
			}
		} else {
			$code = str2url($code);
			$code = $plan['up']['value'].'/'.$code;
		}

		db_update('menu', array(
			'up'=>$plan['up']['value'],
			'code'=>$code,
			'tag0'=>$plan['tag0']['value'],
			'tag1'=>$plan['tag1']['value'],
			'tag2'=>$plan['tag2']['value'],
			'tag3'=>$plan['tag3']['value'],
			'name'=>$plan['name']['value'],
			'type'=>$plan['type']['value'],
			'hide'=>$plan['hide']['value'],
			'w'=>$plan['w']['value'],
			'body'=>$plan['body']['value'],
		), array('code'=>$row['code']));
		\Flydom\Alert::warning('Запись изменена');
		w('cache-menu');
		\Page::redirect('/menu');
	} elseif ($plan['']['valid'] && $plan['send']['value'] == 2) {
		db_delete('menu', array(
			'code'=>$row['code'],
		));
		\Flydom\Alert::warning('Запись удалена');
		w('cache-menu');
		\Page::redirect('/menu');
	}

	$config['plan'] = $plan;
} else {
	\Page::redirect('/menu');
}

?>