<?

if (isset($_REQUEST['_win'])) {
	$config['design'] = 'help';
}

if ($config['args'][0] == 'login') {
	$config['design'] = 'none';
	$block['body'] = $block['modal-login'];
} else {
	$block['body'] = cache_get($config['args'][0], $config['args'][0]);
}

if (is_user('block')) {
	$block['modal-edit'] = '<a href="/block/'.$config['args'][0].'" type="button" class="btn btn-secondary">Правка</a>';
}