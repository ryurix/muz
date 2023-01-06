<?

$sklad = w('list-sklad');
$sk = isset($_REQUEST['sk']) ? $_REQUEST['sk'] : '';
if (!isset($sklad[$sk])) {
	refile('menu.html');
	return;
}

refile('sklad-action.html', 'sklad-action');
w('sklad-action', $sk);

$config['name'] = 'Переоценка '.$sklad[$sk];
$config['type'] = 2;
refile('docs.html');

?>