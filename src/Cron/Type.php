<?

namespace Cron;

class Type extends \Flydom\Cron\Type
{
	const YML = 1;

	const OZON = 10;
	const OZON_ORDER = 11;
	const OZON_GET_PRICE = 12;
	const OZON_SET_PRICE = 13;
	const OZON_GET_STOCK = 14;
	const OZON_SET_STOCK = 15;

	const WILDBERRIES = 20;
	const YANDEX = 30;
	const PRICES = 50;

	const SBER = 101;
	const SITEMAP = 102;
	const GOOGLE_MERCHANT = 103;
	const COMPLEX = 104;
	const RENAME_PICS = 105;
	const CANON = 106;
	const KKM = 107;
	const MARKET72 = 108;
	const MAIL = 109;
	const SESSION = 110;
//	const OZON_COMMON = 111;
	const LOG = 200;

	const DATA = parent::DATA + [
		self::YML => [ 'name'=>'Выгрузка YML', 'class'=>'\Cron\Yml::run', ],

		self::OZON => [ 'name'=>'Выгрузка Ozon', 'class'=>'\Cron\OzonXml::run', ],
		self::OZON_ORDER => [ 'name'=>'Заказы Ozon', 'class'=>'\Ozon\Order::run', ],
		self::OZON_GET_PRICE => ['name'=>'Загрузка цен и товаров из Ozon', 'class'=>'\Ozon\GetPrice::run', ],
		self::OZON_SET_PRICE => ['name'=>'Выгрузка цен в Ozon', 'class'=>'\Ozon\Price::run', ],
		self::OZON_GET_STOCK => ['name'=>'Загрузка остатков из Ozon', 'class'=>'\Ozon\GetStock::run', ],
		self::OZON_SET_STOCK => ['name'=>'Выгрузка остатков в Ozon', 'class'=>'\Ozon\Stock::run', ],

		self::WILDBERRIES => [ 'name'=>'Выгрузка Wildberries', 'class'=>'\Cron\Wildberries::run', ],
		self::YANDEX => [ 'name'=>'Выгрузка Яндекс', 'class'=>'\Cron\Yandex::run', ],
		self::PRICES => [ 'name'=>'Ценообразование', 'class'=>'\Cron\Prices::run', ],
		self::COMPLEX => [ 'name'=>'Составные товары', 'class'=>'\Cron\Complex::run', ],

		self::SBER => [ 'name'=>'Сбер маркет', 'class'=>'\Marketplace\Sber::cron', ],
		self::SITEMAP => [ 'name'=>'Карта сайта', 'class'=>'\Cron\Sitemap::run', ],
		self::GOOGLE_MERCHANT => [ 'name'=>'Гугл торговля', 'class'=>'\Cron\GoogleMerchant::run', ],
		self::RENAME_PICS => [ 'name'=>'Переименование картинок', 'class'=>'\Cron\RenamePics::run', ],
		self::CANON => [ 'name'=>'Канонические ссылки', 'class'=>'\Cron\Canon::run', ],
		self::KKM => [ 'name'=>'Печать чеков', 'class'=>'\Cron\Kkm::run', ],
		self::MARKET72 => [ 'name'=>'Маркет 72', 'class'=>'\Cron\Market72::run', ],
		self::MAIL => [ 'name'=>'Рассылка', 'class'=>'\Cron\Mail::run', ],
		self::SESSION => [ 'name'=>'Очистка сессий', 'class'=>'\Cron\Session::run', ],
//		self::OZON_COMMON => [ 'name'=>'Озон обработка', 'class'=>'\Ozon\Order::run', ],
		self::LOG => ['name'=>'Очистка логов', 'class'=>'\Cron\Log::run'],
	];

} // CronType