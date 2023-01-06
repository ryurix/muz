<?

$sklad = w('list-sklad');
$sk = isset($_REQUEST['sk']) ? $_REQUEST['sk'] : '';
if (!isset($sklad[$sk])) {
	refile('menu.html');
	return;
}

refile('sklad-action.html', 'sklad-action');
w('sklad-action', $sk);

$config['name'] = 'Расходные накладные '.$sklad[$sk];
$config['type'] = -1;
refile('docs.html');

?>