<?

$plan = w('plan-sign');
w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value'] == 1) {
	$mini = '';
	if (is_file($plan['mini']['value'])) {
		$mini = '/files/sign/'.$plan['mini']['filename'];
		$file = \Config::ROOT.substr($mini, 1);
		xcopy($plan['mini']['value'], $file);
		if (!is_file($file)) {
			$mini = '';
		}
	}

	$data = array(
		'name'=>$plan['name']['value'],
		'mini'=>$mini,
		'info'=>$plan['info']['value'],
	);
	db_insert('sign', $data);
	w('cache-sign');
	\Flydom\Alert::warning('Знак добавлен');
	\Page::redirect('/sign/');
} else {
	$config['plan'] = $plan;
}

?>