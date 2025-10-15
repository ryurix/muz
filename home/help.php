<?

if (isset($_REQUEST['_win'])) {
	\Page::design('help');
}

if (\Page::arg() == 'login') {
	\Page::design('modal-login');
} else {
	// $block['body'] = cache_get(\Page::arg(), \Page::arg());
}

/*
if (\User::is('block')) {
	$block['modal-edit'] = '<a href="/block/'.\Page::arg().'" type="button" class="btn btn-secondary">Правка</a>';
}
*/