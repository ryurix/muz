<?

namespace Cron;

// 7*24*60*60
class RenamePics extends Task {
	static function run($args) {
		w('rename-pics');
	}
}