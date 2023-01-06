<?

function cron_next($now, $time, $week = array()) {
	if (is_array($time)) {
		$week = $time['week'];
		$time = $time['time'];
	}

	$dt = new DateTime();
	$dt->setTimestamp($now);
	$dt->setTime(0, 0);
	$next = $dt->getTimestamp() + (date('H', $time))*60*60 + date('i', $time)*60;
	if ($next <= $now) {
		$next+= 24*60*60;
	}

	if (count($week)) {
		$day = date('N', $next);
		while (!in_array($day, $week)) {
			$next+= 24*60*60;
			$day = date('N', $next);
		}
	}

	return $next;
}