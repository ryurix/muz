<?

namespace Cron;


// 3*60
class Mail extends Task {
	static function run($args) {
		w('mail-cron');
	}
}