<?

$q = db_query('SELECT naklad.type,naklad.i,naklad.dt,naklad.vendor,naklad.info,user.name FROM naklad,user WHERE naklad.user=user.i AND naklad.i='.\Page::arg());

if ($row = db_fetch($q)) {

	\Page::body('sklad-action.html', 'sklad-action');
	w('sklad-action', $row['vendor']);

	w('ft');
	switch ($row['type']) {
		case -1: $name = 'Расходная накладная'; break;
		case 1: $name = 'Приходная накладная'; break;
		case 2: $name = 'Переоценка'; break;
		default: $name = '';
	}

	\Page::name($name.' от '.ft($row['dt'], 1).', '.$row['name']);
	$config['row'] = $row;
	\Action::before('/sklad?sk='.$row['vendor'], 'Склад');
} else {
	\Page::redirect('/sklad');
}