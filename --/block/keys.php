<?

function key_next($key) {
	$keys = cache_reload('keys');
	$i = key_get($key) + 1;
	$keys[$key] = $i;
	cache_save('keys', $keys);
	return $i;
}

function key_get($key) {
	global $cache;
	if (isset($cache[$key])) {
		unset($cache[$key]);
	}

	$keys = cache_reload('keys');
	if (!isset($keys[$key])) {
		$keys[$key] = 0;
	}
	return $keys[$key];
}

?>