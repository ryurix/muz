<?

$sklad = w('list-sklad');
$sk = isset($_REQUEST['sk']) ? $_REQUEST['sk'] : '';
if (!isset($sklad[$sk])) {
	\Page::body('menu');
	return;
}

\Page::body('sklad-action.html', 'sklad-action');
w('sklad-action', $sk);

\Page::name('Расходные накладные '.$sklad[$sk]);
$config['type'] = -1;
\Page::body('docs');

?>