<?

namespace Flydom\Cron;

class Log extends Task {
	static function run($args = null) {
		$now = \Config::now();
		$deleted = 0;
		foreach (\Type\Log::days() as $type=>$days) {
			$dt = $now - $days*24*60*60;
			$deleted+= \Db::delete('log', ['type'=>$type, 'dt<'.$dt]);
		}
		\Db::query('OPTIMIZE TABLE log;');
		return $deleted;
	}
}