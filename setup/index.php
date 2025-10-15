<?

if (\User::is('catalog')) {
	if (isset($_REQUEST['cache1'])) {
/*
		$next = cache_load('cron');
		$next['yandex'] = \Config::now();
		cache_save('cron', $next);
*/
		w('yandex');
		\Flydom\Alert::warning('yandex.xml обновлён!');
	}

	if (isset($_REQUEST['cache2'])) {
		w('google-merchant');
		\Flydom\Alert::warning('google-merchant.xml обновлён!');
	}

	if (isset($_REQUEST['cache3'])) {
		$next = cache_load('cron');
		$next['brand'] = \Config::now();
		cache_save('cron', $next);
		\Flydom\Alert::warning('Фильтр по брендам обновлён будет обновлён в ближайшее время');
	}

	if (isset($_REQUEST['cache4'])) {
		w('rename-pics', $dummy = 1);
	}
}

?>