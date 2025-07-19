<?php

namespace Cabinet;

class Type extends \Flydom\Type\Base
{
	const DATA = [
		\Type\Cron::YANDEX => ['Яндекс', 'yandex'],
		\Type\Cron::OZON => ['Озон', 'ozon'],
		\Type\Cron::WILDBERRIES => ['Wildberries', 'wildberries']
	];

	static function name($code = null, $default = null) {
		return parent::data(0, $code, $default);
	}

	static function setup($code = null, $default = null) {
		return parent::data(1, $code, $default);
	}
}