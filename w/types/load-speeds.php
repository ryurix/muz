<?

$config['cache-speeds'] = cache_load('speeds');

function get_speed_i($ven, $city = 0, $count = 1) {
	global $config;
	$speeds = $config['cache-speeds'];

	if ($count < 1 || $ven < 0) {
		$sp = 100;
	} elseif (isset($speeds[$ven][$city])) {
		$sp = $speeds[$ven][$city];
	} elseif (isset($speeds[$ven][0])) {
		$sp = $speeds[$ven][0];
	} elseif (isset($speeds[0][$city])) {
		$sp = $speeds[0][$city];
	} else {
		$sp = $speeds[0][0];
	}

	return $sp;
}

function get_speed($ven, $city = 0, $count = 1) {
	static $speed;
	if (!is_array($speed)) { $speed = w('speed'); }
	return $speed[get_speed_i($ven, $city, $count)];
}

function my_speed($ven, $speed = 0, $count = 1) {
	if ($speed == 100 || $count < 1) {
		return get_speed(-1);
	} else {
		return get_speed($ven, $_SESSION['city'], $count = 1);
	}
}

?>