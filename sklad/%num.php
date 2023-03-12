<?

$q = db_query('SELECT naklad.type,naklad.i,naklad.dt,naklad.vendor,naklad.info,user.name FROM naklad,user WHERE naklad.user=user.i AND naklad.i='.$config['args'][0]);

if ($row = db_fetch($q)) {

	refile('sklad-action.html', 'sklad-action');
	w('sklad-action', $row['vendor']);

	w('ft');
	switch ($row['type']) {
		case -1: $name = 'Расходная накладная'; break;
		case 1: $name = 'Приходная накладная'; break;
		case 2: $name = 'Переоценка'; break;
		default: $name = '';
	}

	$config['name'] = $name.' от '.ft($row['dt'], 1).', '.$row['name'];
	$config['row'] = $row;
	$config['action'] = array(
		array('href'=>'/sklad?sk='.$row['vendor'], 'action'=>'Склад'),
	);
} else {
	redirect('/sklad');
}