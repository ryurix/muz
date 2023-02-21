<?

namespace Cron;

abstract class Task {

	abstract static public function run($data);

	static public function execute($cron, $data = null) {
		if (is_null($data)) {
			$data = is_array($cron['data']) ? $cron['data'] : \Flydom\Cache::array_decode($cron['data']);
		}

		$class = \Type\Cron::class($cron['typ']);

		if ($class) {
			try {
				$info = call_user_func($class, $data);
			} catch (\Exception $ex) {
				$info = $ex->getMessage();
			}
		} else {
			$info = 'Тип задачи не опознан: '.$cron['typ'];
		}

		return $info;
	}

	static public function follow($list) {

		if (!is_array($list)) {
			$list = \Flydom\Cache::array_decode($list);
		}

		if (!count($list)) {
			return '';
		}

		$info = '';
		$rows = db_fetch_all('SELECT * FROM cron WHERE i IN ('.implode(',', $list).') ORDER BY name');

		foreach ($rows as $cron) {
			sleep(2);

			$info.= ', ';
			$class = \Type\Cron::class($cron['typ']);
			if ($class) {
				try {
					$info.= call_user_func($class, array_decode($cron['data']));
				} catch (\Exception $ex) {
					$info.= $ex->getMessage();
				}
			} else {
				$info.= 'Тип задачи не опознан: '.$cron['typ'];
			}
		}
		return $info;
	}

	static public function next($cron, $data = null) {
		if (is_null($data)) {
			$data = is_array($cron['data']) ? $cron['data'] : \Flydom\Cache::array_decode($cron['data']);
		}

		if ($cron['every'] == 1) {
			return self::every(now(), $data['time'], $data['week']);
		} else {
			return now() + $cron['every'];
		}

	}

	static protected function every($now, $time, $week = []) {
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