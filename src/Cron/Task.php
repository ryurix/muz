<?

namespace Cron;

abstract class Task {

	abstract static public function run($data);

	static public function next($now, $time, $week = []) {
		if (is_array($time)) {
			$week = $time['week'];
			$time = $time['time'];
		}

		$dt = new \DateTime();
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
}