<?

namespace Price;

class Type
{
//	const RRC = 0;
//	const OPT = 1;

	const RRC = 0;
	const WB = 1;
	const OZON = 2;
	const WB2 = 3;
	const OZON2 = 4;
	const YANDEX = 5;
	const OPT = 6;

	static function list() {
		$oClass = new \ReflectionClass(__CLASS__);
		return array_flip($oClass->getConstants());
	}

	static function count() {
		return count(self::list());
	}

	static function names() {
		return [
//			self::RRC => 'розничная',
//			self::OPT => 'оптовая',

			self::RRC => 'основная',
			self::WB => 'WB',
			self::OZON => 'озон',
			self::WB2 => 'WB2',
			self::OZON2 => 'озон2',
			self::YANDEX => 'yandex',
			self::OPT => 'оптовая'
		];
	}

	static function name($key) {
		return self::names()[$key] ?? $key;
	}

	static function get() {
		$default = self::RRC;
		$typ = kv($_REQUEST, 'typ', $default);
		$names = self::names();
		if (!isset($names[$typ])) {
			return $default;
		}
		return $typ;
	}

} // Price