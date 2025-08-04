<?php

namespace Order;

class State extends \Flydom\Type\Base
{
	const DATA = [
		0=>['Черновик', 'В&nbsp;обработке', 'gray'],
		1=>['В обработке', 'В&nbsp;обработке', 'error'],
		2=>['Обработан', 'В&nbsp;обработке', 'lime'],
		3=>['Ожидание оплаты', 'Ожидание&nbsp;оплаты', 'warning'],
		7=>['Подтвержден', 'В&nbsp;работе', 'reddy'],

		10=>['Счет запрошен', 'В&nbsp;работе', 'query'],
		13=>['Оплачено поставщику', 'В&nbsp;работе', 'birusa'],
		15=>['Отправлено поставщиком', 'В&nbsp;пути', 'purple'],

		20=>['Готов к выдаче', 'Готов&nbsp;к&nbsp;выдаче', 'green'],
		23=>['Отправлено клиенту', 'В&nbsp;пути', 'info'],
		25=>['Доставка на дом', 'Доставка клиенту домой', 'success'],
		27=>['Собран', 'Собран', 'white'],

		30=>['Закрыт', 'Закрыт', 'white'],
		35=>['Отменен', 'Отменен', 'brown'],
	];

	static function name($key, $default = null) {
		return self::data(0, $key, $default);
	}

	static function names() {
		return self::data(0);
	}

	static function user($key, $default = null) {
		return self::data(1, $key, $default);
	}

	static function users() {
		return self::data(1);
	}

	static function color($key, $default = null) {
		return self::data(2, $key, $default);
	}

	static function colors() {
		return self::data(2);
	}
}