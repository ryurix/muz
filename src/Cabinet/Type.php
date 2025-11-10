<?php

namespace Cabinet;

class Type extends \Flydom\Type\Base
{

	const OZON = \Cron\Type::OZON;
	const WILDBERRIES = \Cron\Type::WILDBERRIES;
	const YANDEX = \Cron\Type::YANDEX;

	const DATA = [
		self::OZON => ['Озон', 'ozon'],
		self::WILDBERRIES => ['Wildberries', 'wildberries'],
		self::YANDEX => ['Яндекс', 'yandex'],
	];

	static function name($code = null, $default = null) {
		return parent::data(0, $code, $default);
	}

	static function setup($code = null, $default = null) {
		return parent::data(1, $code, $default);
	}
}