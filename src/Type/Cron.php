<?

namespace Type;

class Cron extends \Flydom\Type\Cron
{
	const YML = 1;
	const OZON_XML = 10;
	const OZON_ORDER = 12;
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
	const OZON = 111;
	const LOG = 200;

	const DATA = parent::DATA + [
		self::YML => [ 'name'=>'Выгрузка YML', 'class'=>'\Cron\Yml::run', ],
		self::OZON_XML => [ 'name'=>'Выгрузка Ozon', 'class'=>'\Cron\OzonXml::run', ],
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
		self::OZON => [ 'name'=>'Озон обработка заказов', 'class'=>'\Cron\Ozon::run', ],
		self::LOG => ['name'=>'Очистка логов', 'class'=>'\Flydom\Cron\Log::run'],
	];

} // CronType