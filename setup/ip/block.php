<?

$config['action'] = [
	['href'=>'/setup/ip', 'action'=>'IP'],
	['href'=>'/setup/ip/block', 'action'=>'Блокировка', 'here'=>1],
];

$ban = cache_load('ip-ban', []);

$plan = [
	'ip'=>['name'=>'ip', 'type'=>'line', 'id'=>'ip'],
	'send'=>[
		'type'=>'button',
		'count'=>['add', 'del', 'reset'],
		'add'=>'Забанить',
		'del'=>'Разбанить',
		'reset'=>'Очистить все сессии',
	],
];

w('request', $plan);

if ($plan['send']['value'] === 'add' && strlen($plan['ip']['value']) >= 4) {
	if (!in_array($plan['ip']['value'], $ban)) {
		$ban[] = $plan['ip']['value'];
		sort($ban, SORT_STRING);
		cache_save('ip-ban', $ban);
	}
} elseif ($plan['send']['value'] === 'del') {
	$ban = remove_role($plan['ip']['value'], $ban);
	cache_save('ip-ban', $ban);
} elseif ($plan['send']['value'] === 'reset') {
	db_query('DELETE FROM session WHERE usr=0 AND ip IN (SELECT ip FROM (SELECT ip FROM session GROUP BY ip HAVING count(*)>3) s)');
}