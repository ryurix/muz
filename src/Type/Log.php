<?

namespace Type;

class Log extends \Flydom\Type\Log
{

const DATA = parent::DATA + [
	10=>['name'=>'Создание товара в кабинете', 'days'=>365],
	11=>['name'=>'Создание дочернего товара в кабинете', 'days'=>365],
	12=>['name'=>'Создание товара через excel', 'days'=>365],
	13=>['name'=>'Создание дочернего товара через excel', 'days'=>365],
	20=>['name'=>'Импорт товара из Bitrix', 'days'=>365],
];

} // Log