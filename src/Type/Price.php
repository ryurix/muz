<?

namespace Type;

class Price
{

	const MAIN = 0;

	const BULK = 1;
	const OZON = 2;

	const COUNT = 2;

	static function list() {
        $oClass = new \ReflectionClass(__CLASS__);
        return array_flip($oClass->getConstants());
    }

	static function names() {
		return [
			self::MAIN => 'основная',
			self::BULK => 'оптовая',
			self::OZON => 'озон',
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