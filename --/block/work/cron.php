<?

if (kv($config, 'DEBUG', 0)) {
	return;
}

set_time_limit(55);
w('ft');

cache_set('cron', ft(now(), 1));

$rows = db_fetch_all('SELECT * FROM cron WHERE every>0 AND dt<'.now().' ORDER BY dt LIMIT 5');

foreach ($rows as $cron) {

	$data = $cron + \Flydom\Arrau::decode($cron['data']);

	$info = \Cron\Task::execute($cron, $data);

	if (isset($data['follow'])) {
		$info.= \Cron\Task::follow($data['follow']);
	}

	db_update('cron', [
		'dt'=>\Cron\Task::next($cron, $data),
		'info'=>trim(ft(now(), 1).' '.$info),
	], ['i'=>$cron['i']]);
}
