<?

namespace Cron;

// 7*24*60*60
class RenamePics {
	static function run($args) {
		w('rename-pics');
	}
}