<?php

namespace Cabinet;

class Form extends \Flydom\Form\Box
{
	static $started = false;

	protected static $default_type = \Type\Cron::YANDEX;

	static function type($value = null)
	{
		if (self::$started) {
			return self::getSet('type', $value, self::$default_type);
		}

		if (is_null($value)) {
			return self::$default_type;
		}

		self::$default_type = $value;
	}

	static function name() { return self::getSet('name'); }
	static function user() { return self::getSet('usr'); }
	static function send() { return self::getSet('send'); }

	protected static function plan()
	{
		self::setClass('auto');

		$plan = [
			'type' => new \Flydom\Input\Select(['name'=>'Тип', 'values'=>\Cabinet\Type::name(), 'class'=>'auto'], self::type()),
			'name' => new \Flydom\Input\Line('Название'),
			'usr' => new \Flydom\Input\Integer('ID пользователя'),
		];

		switch (self::type()) {
			case \Type\Cron::YANDEX:
				$plan['key'] = new \Flydom\Input\Line('Ключ API');
				$plan['token'] = new \Flydom\Input\Line('Токен Авторизации');
				$plan['campaign'] = new \Flydom\Input\Line('ID Магазина');
				$plan['vat'] = new \Flydom\Input\Line('НДС');
				break;
			case \Type\Cron::OZON:
				$plan['client'] = new \Flydom\Input\Line('Код клиента');
				$plan['api'] = new \Flydom\Input\Line('Ключ API');
				$plan['warehouse'] = new \Flydom\Input\Line('ID Склада');
				$plan['vat'] = new \Flydom\Input\Line('НДС');
				break;
			case \Type\Cron::WILDBERRIES:
				$plan['token'] = new \Flydom\Input\Line('Токен');
				$plan['store'] = new \Flydom\Input\Integer('ID склада');
				$plan['auth'] = new \Flydom\Input\Line('Авторизация');
				break;
		}

		$plan['send'] = new \Flydom\Input\Button('', ['save'=>'Сохранить', 'delete'=>['name'=>'Удалить', 'confirm'=>'Удалить кабинет?']]);
		return $plan;
	}
}