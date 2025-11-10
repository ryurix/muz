<?

namespace Cron;


// раз в 2 минуты
class Kkm {
	static function run($args) {
		w('kkmserver');
		kkm_fix();
	}
}