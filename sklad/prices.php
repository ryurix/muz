<?

$sklad = w('list-sklad');
$sk = isset($_REQUEST['sk']) ? $_REQUEST['sk'] : '';
if (!isset($sklad[$sk])) {
	\Page::body('menu.html');
	return;
}

\Page::body('sklad-action', 'sklad-action');
w('sklad-action', $sk);

\Page::name('Переоценка '.$sklad[$sk]);
$config['type'] = 2;
\Page::body('docs');

?>