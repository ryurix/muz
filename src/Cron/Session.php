<?

namespace Cron;

//13*60*60
class Session extends Task {
	static function run($args) {
		db_query('DELETE FROM session WHERE usr=0 AND dt<'.(\Config::now() - 12*60*60));
	}
}