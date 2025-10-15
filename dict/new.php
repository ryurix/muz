<?

$plan = w('plan-dict');
w('request', $plan);

\Action::before('/dict', 'Словарь');

if ($plan['']['valid'] && $plan['sent']['value']) {
	$data = array(
		'name'=>$plan['name']['value'],
		'code'=>$plan['code']['value'],
	);
	db_insert('dict', $data);
	\Flydom\Alert::warning('Слово добавлено');
	\Page::redirect('/dict/');
} else {
	$config['plan'] = $plan;
}

?>