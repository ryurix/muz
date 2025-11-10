<?

if (\Config::DEBUG) {
	return;
}

set_time_limit(550);

cache_set('cron', \Flydom\Time::dateTime(\Config::now()));

$rows = db_fetch_all('SELECT * FROM cron WHERE every>0 AND dt<'.\Config::now().' ORDER BY dt LIMIT 1');

foreach ($rows as $cron) {

	$data = $cron + \Flydom\Arrau::decode($cron['data']);

	$info = \Flydom\Cron\Task::execute($cron, $data);

	if (isset($data['follow'])) {
		$info.= \Flydom\Cron\Task::follow($data['follow']);
	}

	db_update('cron', [
		'dt'=>\Flydom\Cron\Task::next($cron, $data),
		'last'=>\Config::now(),
		'info'=>trim($info),
	], ['i'=>$cron['i']]);
}
