<?php

namespace Cabinet;

class Form extends \Flydom\Form\Box
{
	static function type($value = null) { return self::getSet('typ', $value); }
	static function name() { return self::getSet('name'); }
	static function user() { return self::getSet('usr'); }
	static function margin() { return self::getSet('margin'); }
	static function profit() { return self::getSet('profit'); }
	static function vat() { return self::getSet('vat'); }
	static function send() { return self::getSet('send'); }

	static function start($default = []) {
		if (isset($_REQUEST['typ'])) {
			self::type($_REQUEST['typ']);
		} elseif (isset($default['typ'])) {
			self::type($default['typ']);
		} else {
			self::type(\Cron\Type::OZON);
		}

		parent::start($default);
	}


	protected static function plan()
	{
		self::setClass('auto');

		$plan = [
			'typ' => new \Flydom\Input\Select(['name'=>'Маркетплейс', 'values'=>\Cabinet\Type::name(), 'class'=>'auto'], self::type()),
			'name' => new \Flydom\Input\Line('Название'),
			'usr' => new \Flydom\Input\Integer('ID пользователя'),
			'margin'=>new \Flydom\Input\Integer('Прибыль, %'),
			'profit'=>new \Flydom\Input\Integer('Прибыль, руб'),
		];

		switch (self::type()) {
			case \Cron\Type::YANDEX:
				$plan['key'] = new \Flydom\Input\Line('Ключ API');
				$plan['token'] = new \Flydom\Input\Line('Токен Авторизации');
				$plan['campaign'] = new \Flydom\Input\Line('ID Магазина');
				$plan['vat'] = new \Flydom\Input\Line('НДС');
				break;
			case \Cron\Type::OZON:
				$plan['client'] = new \Flydom\Input\Line('Код клиента');
				$plan['key'] = new \Flydom\Input\Line('Ключ API');
				$plan['warehouse'] = new \Flydom\Input\Line('ID Склада');
				$plan['vat'] = new \Flydom\Input\Line('НДС');
				break;
			case \Cron\Type::WILDBERRIES:
				$plan['token'] = new \Flydom\Input\Line('Токен');
				$plan['store'] = new \Flydom\Input\Integer('ID склада');
				$plan['auth'] = new \Flydom\Input\Line('Авторизация');
				break;
		}

		$plan['send'] = new \Flydom\Input\Button('', ['send'=>'Сохранить', 'delete'=>['name'=>'Удалить', 'confirm'=>'Удалить кабинет?']]);
		return $plan;
	}
}