<?

namespace Type;

class Log extends \Flydom\LogType
{

static function list() {
	return parent::list() + [

		// 10-29 tovar events
		10=>'Создание товара в кабинете',
		11=>'Создание дочернего товара в кабинете',
		12=>'Создание товара через excel',
		13=>'Создание дочернего товара через excel',
		20=>'Импорт товара из Bitrix',
	//	22=>'Создание товара из Global',

	];
}

} // Log