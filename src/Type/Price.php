<?

namespace Type;

class Price
{

	const MAIN = 0;
	const WB = 1;
	const OZON = 2;
	const WB2 = 3;
	const OZON2 = 4;
	const YANDEX = 5;

	static function list() {
		$oClass = new \ReflectionClass(__CLASS__);
		return array_flip($oClass->getConstants());
	}

	static function count() {
		return count(self::list());
	}

	static function names() {
		return [
			self::MAIN => 'основная',
			self::WB => 'WB',
			self::OZON => 'озон',
			self::WB2 => 'WB2',
			self::OZON2 => 'озон2',
			self::YANDEX => 'yandex',
		//	self::
		];
	}

	static function name($key) {
		return kv(self::names(), $key, '');
	}

	static function get() {
		$default = self::MAIN;
		$typ = kv($_REQUEST, 'typ', $default);
		$names = self::names();
		if (!isset($names[$typ])) {
			return $default;
		}
		return $typ;
	}

} // Price