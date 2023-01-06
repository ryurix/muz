<?

namespace Type;

class Cron extends \Flydom\Type
{
	const YML = 1;
	const OZON_XML = 10;
	const OZON_ORDER = 12;
	const WILDBERRIES = 20;
	const PRICES = 50;

	protected const DATA = [

		self::YML => [
			'name'=>'Выгрузка YML',
			'class'=>'\Cron\Yml',
		],
		self::OZON_XML => [
			'name'=>'Выгрузка Ozon',
			'class'=>'\Cron\Ozon',
		],
		self::WILDBERRIES => [
			'name'=>'Выгрузка Wildberries',
			'class'=>'\Cron\Wildberries',
		],
		self::PRICES => [
			'name'=>'Ценообразование',
			'class'=>'\Cron\Prices',
		],
	];

	static function name($code = null, $default = null) {
		return parent::data_value(self::DATA, 'name', $code, $default);
	}

	static function class($code = null, $default = null) {
		return parent::data_value(self::DATA, 'class', $code, $default);
	}

} // CronType