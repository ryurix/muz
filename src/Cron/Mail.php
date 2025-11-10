<?

namespace Cron;


// 3*60
class Mail {
	static function run($args) {
		w('mail-cron');
	}
}