<?

namespace Cron;


// раз в 5 дней
class Canon extends Task {
	static function run($args) {
		db_query('DELETE FROM canon WHERE dt<'.(now() - 15*24*60*60));
	}
}