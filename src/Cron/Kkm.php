<?

namespace Cron;


// раз в 2 минуты
class Kkm extends Task {
	static function run($args) {
		w('kkmserver');
		kkm_fix();
	}
}