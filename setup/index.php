<?

if (is_user('catalog')) {
	if (isset($_REQUEST['cache1'])) {
/*
		$next = cache_load('cron');
		$next['yandex'] = now();
		cache_save('cron', $next);
*/
		w('yandex');
		alert('yandex.xml обновлён!');
	}

	if (isset($_REQUEST['cache2'])) {
		w('google-merchant');
		alert('google-merchant.xml обновлён!');
	}

	if (isset($_REQUEST['cache3'])) {
		$next = cache_load('cron');
		$next['brand'] = now();
		cache_save('cron', $next);
		alert('Фильтр по брендам обновлён будет обновлён в ближайшее время');
	}

	if (isset($_REQUEST['cache4'])) {
		w('rename-pics', $dummy = 1);
	}
}

?>