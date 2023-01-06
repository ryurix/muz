<?

if (kv($config, 'DEBUG', 0)) {
	return;
}

set_time_limit(45);

$q = db_query('SELECT * FROM cron WHERE every>0 AND dt<'.now().' ORDER BY dt LIMIT 5');
while ($row = db_fetch($q)) {
	$data = \Flydom\Cache::array_decode($row['data']);

	if ($row['every'] == 1) {
		$next = \Cron\Task::next(now(), $data);
	} else {
		$next = now() + $row['every'];
	}

	$class = \Type\Cron::class($row['typ']);

	if ($class) {
		try {
			$info = call_user_func([$class, 'run'], $data);
		} catch (Exception $ex) {
			$info = $ex->getMessage();
		}
	} else {
		$info = 'Тип задачи не опознан: '.$row['typ'];
	}

	db_update('cron', [
		'dt'=>$next,
		'info'=>$info,
	], ['i'=>$row['i']]);
}



$now = now();

$cronrun = cache_load('cron-run', 0);
if ($cronrun >= ($now - 60)) {
	return;
} else {
	cache_save('cron-run', now());
}

$next = cache_load('cron');
$save = false;

if (!$save && $next['sitemap'] < $now) {
	$next['sitemap'] = $now + 2*60*60;
//	$next['sitemap'] = $now + 5;
	$save = true;

	$next['sitemap-index']++;
	$sites = cache_load('site');

	$key = $next['sitemap-index'] % count($sites);
	$keys = array_keys($sites);
	$site = $sites[$keys[$key]];
	$suffix = '_'.trim(substr($site, 0, 4), '.');

	if ($keys[$key] == 34) {
		w('sitemap');
	} else {
		$dummy = array('site'=>$site, 'suffix'=>$suffix);
		w('sitemap', $dummy);
	}
}

/*
if (!$save && $next['yandex'] < $now) {
	$next['yandex'] = $now + 25*60;
	$save = true;
	w('yandex');
}
*/
w('yandex');

if (!$save && $next['sber-cron'] < $now) {
	$next['sber-cron'] = $now + 21*60;
	$save = true;
	w('autoload');
	\Marketplace\Sber::cron();
}

if (!$save && $next['google-merchant'] < $now) {
	$next['google-merchant'] = $now + 26*60*60;
	$save = true;
	w('google');
}

if (!$save && $next['brand'] < $now) {
	$next['brand'] = $now + 48*60*60;
	$save = true;
	w('cache-brand');
}

if (!$save && $next['prices'] < $now) {
	$next['prices'] = $now + 18*60*60;
	$next['yandex'] = $now + 15*60;
	$next['google-merchant'] = $now + 30*60;
	$save = true;

	w('prices-calc');
	$q = db_query('SELECT * FROM prices ORDER BY i');
	while ($i = db_fetch($q)) {
		prices_calc($i);
	}
	db_close($q);
}

if (!$save && $next['price2'] < $now) {
	$next['price2'] = $now + 18*60*60;
	$save = true;

	w('price2-calc');
	$q = db_query('SELECT * FROM price2 ORDER BY i');
	while ($i = db_fetch($q)) {
		price2_calc($i);
	}
	db_close($q);
}

if (!$save && $next['rename-pics'] < $now) { // переименование названий картинок
	$next['rename-pics'] = $now + 7*24*60*60;
	$save = true;
	w('rename-pics');
}

if (!$save && $next['canon'] < $now) {
	$next['canon'] = $now + 5*24*60*60;
	$save = true;
	db_query('DELETE FROM canon WHERE dt<'.(now() - 15*24*60*60));
}

if ($next['kkmserver'] < $now) { // печать фискальных чеков
	$next['kkmserver'] = $now + 2*60;
	$save = true;
	w('kkmserver');
	kkm_fix();
}

if ($next['market'] < $now) {
	$next['market'] = $now + 3*60;
	$save = true;
	w('market72');
}

if ($next['mail'] < $now) {
	$next['mail'] = $now + 3*60;
	$save = true;
	w('mail-cron');
}

if (!$save && $next['session'] < $now) {
	$next['session'] = $now + 13*60*60;
	$save = true;
	db_query('DELETE FROM session WHERE usr=0 AND dt<'.($now - 12*60*60));
}

if ($save) {
	cache_save('cron', $next);
}

w('ozon-cron');