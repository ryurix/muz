<?php

/*
 *	Copyright flydom.ru
 *	Version 2.0.2011-01-24
 */

$menu = array();
$menu[] = array('name'=>'', 'wide'=>1, '/'=>array(

'api'=>array('name'=>'API', 'design'=>'none', '/'=>array(
	'yandex'=>array('design'=>'yandex', '/'=>array(
		'%num'=>array('virtual'=>1, '/'=>array(
			'cart'=>array('log'=>201),
			'order'=>array('/'=>array(
				'accept'=>array('log'=>205),
				'status'=>array('log'=>209),
			)),
			'stocks'=>['log'=>211],
		)),
	)),
	'goods'=>['design'=>'goods', '/'=>[
		'%num'=>['virtual'=>1, '/'=>[
			'order'=>['/'=>[
				'new'=>['log'=>501],
				'cancel'=>['log'=>508],
			]],
		]],
	]],
)),

'article'=>array('name'=>'Статьи', '/'=>array(
	'%abc'=>array('name'=>'Статья', '/'=>array(
		'edit'=>array('virtual'=>1, 'role'=>'menu', 'action'=>'правка'),
	)),
	'new'=>array('name'=>'Новая статья', 'action'=>'создать', 'role'=>'menu'),
)),

'auto'=>array('name'=>'Водитель', '/'=>array(
	'%num'=>array('name'=>'Заказ'),
)),

'basket'=>array('name'=>'Корзина', 'wide'=>1, '/'=>array(
	'buy'=>array('design'=>'none'),
	'thanks'=>array('name'=>'Благодарим за заказ!'),
	'ok'=>array('name'=>'Платеж успешен!'),
	'no'=>array('name'=>'Оплата отменена.'),
	'uniteller'=>array('name'=>'Уведомления Uniteller'),
	'appex'=>array('name'=>'Appex'),
	'print'=>array('name'=>'Коммерческое предложение', 'design'=>'print'),
	'save'=>array('design'=>'none'),
	'load'=>array('name'=>'Загрузить корзину'),
	'next'=>array('name'=>'Подтверждение заказа', 'wide'=>1),
	'naklad'=>array('name'=>'Новая накладная', 'role'=>'sklad'),
)),

'bill'=>array('name'=>'Счета оплаты банковской картой', '/'=>array(
	'%num'=>array('name'=>'Оплата через интернет', '/'=>array(
		'pay'=>array('virtual'=>1),
		'edit'=>array('virtual'=>1, 'role'=>'order'),
		'view'=>array('virtual'=>1),
	)),
)),

'block'=>array('name'=>'Блоки', 'role'=>'block', '/'=>array(
	'%abc'=>array(),
)),

'brand'=>array('name'=>'Производители', '/'=>array(
	'new'=>array('name'=>'Добавить производителя'),
	'%abc'=>array('/'=>array(
		'edit'=>array('role'=>'catalog', 'virtual'=>1, 'action'=>'правка'),
		'erase'=>array('name'=>'Удалить производителя из списка?', 'role'=>'catalog'),
	)),
)),

'cache'=>array('role'=>kv($config, 'DEBUG', 0) ? 'guest admin' : 'admin'),

'captcha' => array('/'=>array(
	'%abc'=>array('virtual'=>1),
)),

'catalog'=>array('name'=>'Каталог', 'wide'=>1, '/'=>array(
	'new'=>array('name'=>'Новый раздел', 'role'=>'catalog'),
	'%abc'=>array('wide'=>1, '/'=>array(
		'%abc'=>array('virtual'=>1, 'wide'=>1),
		'edit'=>array('role'=>'catalog', 'virtual'=>1),
		'erase'=>array('role'=>'catalog', 'name'=>'Удалить раздел?'),
		'subcat'=>array('role'=>'catalog', 'name'=>'Подкаталоги'),
	)),
)),

'city'=>array('name'=>'Справочник городов', 'role'=>'adres', '/'=>array(
	'new'=>array('name'=>'Добавить город'),
	'%num'=>array(),
)),

'dict'=>array('name'=>'Словарь', 'role'=>'admin', '/'=>array(
	'%num'=>array('/'=>array(
		'erase'=>array('virtual'=>1, 'action'=>'Удалить'),
	)),
	'site'=>array('/'=>array(
		'%num'=>array('virtual'=>1),
	)),
	'new'=>array('name'=>'Добавить слово', 'action'=>'Новое слово'),
)),

'doc'=>array('name'=>'Документы', '/'=>array(
	'%num'=>array(),
)),

'export'=>array('design'=>'none', '/'=>array(
	'store'=>array(),
	'catalog'=>array(),
)),

'filter'=>array('name'=>'Фильтры', 'role'=>'catalog', '/'=>array(
	'new'=>array('name'=>'Новый фильтр'),
	'%num'=>array(),
)),

'kkm'=>array('name'=>'Фискальные чеки', 'role'=>'kkm', '/'=>array(
	'%num'=>array(),
)),

'mark'=>array('name'=>'Метки', 'role'=>'catalog', '/'=>array(
	'new'=>array('name'=>'Новая метка', 'action'=>'создать'),
	'%num'=>array(),
)),

'menu'=>array('name'=>'Меню', 'role'=>'menu', '/'=>array(
	'edit'=>array(),
	'new'=>array('name'=>'Добавить страницу'),
)),

'my'=>array('name'=>'Мои заказы', '/'=>array(
	'user'=>array('name'=>'Анкета', 'no-title'=>1),
	'adres'=>array('name'=>'Выберите город'),
//	'pay'=>array('name'=>'Оплата'),
)),

'order'=>array('name'=>'Заказы', 'wide'=>1, 'role'=>'order', '/'=>array(
	'%num'=>array('name'=>'Заказ', '/'=>array(
		'edit'=>array('virtual'=>1, 'action'=>'Заказ'),
		'print'=>array('action'=>'Документы'),
		'link'=>array('action'=>'Связанные', 'virtual'=>1, 'wide'=>1, 'no-title'=>0),
		'map'=>array('action'=>'Карта'),
	)),
//	'link'=>array('name'=>'Связанные', 'no-title'=>1, 'wide'=>1, 'role'=>'link'),
)),

'patch'=>['name'=>'Патч', 'role'=>'admin'],

'partner'=>array('name'=>'Партнер', 'role'=>'partner'),

'portfolio'=>array('name'=>'Наши работы', 'nowrap'=>1, '/'=>array(
	'%abc'=>array('/'=>array(
		'edit'=>array('role'=>'block', 'virtual'=>1),
		'erase'=>array('rolo'=>'block'),
	)),
	'new'=>array('name'=>'Новая запись', 'role'=>'block'),
	'groups'=>array('name'=>'Группы', 'role'=>'block'),
)),

'prices'=>array('name'=>'Ценообразование', 'role'=>'import', '/'=>array(
	'%num'=>array(),
	'groups'=>array('name'=>'Группы товаров', 'action'=>'Группы товаров'),

	'plan'=>['name'=>'', '/'=>[
		'%num'=>[],
	]],
)),

'rate'=>array('name'=>'Отзывы', 'role'=>'rate', '/'=>array(
	'new'=>array('name'=>'Новый отзыв'),
	'%num'=>array(),
)),

'region'=>array('name'=>'Справочник регионов', 'role'=>'adres', '/'=>array(
	'new'=>array('name'=>'Добавить регион'),
	'%num'=>array(),
)),

'sales'=>array('name'=>'Популярные товары', '/'=>array(
	'list'=>array('name'=>'Скидки', 'role'=>'sale'),
	'new'=>array('name'=>'Новая скидка', 'role'=>'sale'),
	'%abc'=>array('role'=>'sale', '/'=>array(
		'orders'=>array(),
	)),
	'cache'=>array('name'=>'Обновление кэша', 'action'=>'Обновить кэш', 'role'=>'catalog', 'virtual'=>1),
)),

'setup'=>array('name'=>'Настройки', 'role'=>'adres catalog admin log spam', '/'=>array(
	'adres'=>array('name'=>'Адреса и доставка', 'role'=>'adres'),
	'delivery'=>array('name'=>'Время доставки', 'role'=>'adres'),
	'parse'=>array('name'=>'Импорт каталога', 'role'=>'catalog', 'lib'=>'parse'),
	'google'=>array('name'=>'Категории Google', 'role'=>'catalog'),
	'lost'=>array('name'=>'Потерянные товары', 'role'=>'catalog'),
	'spam'=>array('name'=>'Рассылка', 'role'=>'spam'),
	'geoloc'=>array('name'=>'Импорт данных геолокации', 'role'=>'admin'),
	'update-tags'=>array('name'=>'Обновление тегов', 'role'=>'admin'),
	'log'=>array('name'=>'Логи', 'role'=>'log'),
	'img-fluid'=>array('name'=>'Обновление картинок', 'role'=>'admin'),
	'yml'=>array('name'=>'Выгрузка YML', 'role'=>'catalog', '/'=>array(
		'%num'=>array(),
	)),
	'ozon'=>array('name'=>'Выгрузка Ozon', 'role'=>'catalog', '/'=>array(
		'%num'=>array(),
		'cron'=>array('name'=>'Запуск планировщика'),
	)),
	'wildberries'=>array('name'=>'Выгрузка Wildberries', 'role'=>'catalog', '/'=>array(
		'%num'=>array(),
		'cron'=>array('name'=>'Запуск планировщика'),
		'load'=>['name'=>'Загрузка номенклатуры', 'lib'=>'phpexcel'],
	)),

	'import-dyna'=>array('name'=>'Импорт товаров динатона', 'role'=>'admin'),
	'import-dyna-2'=>array('name'=>'Обработка товаров динатона', 'role'=>'admin'),

	'ip'=>array('name'=>'IP', 'role'=>'admin'),

	'reset'=>array('name'=>'RESET'),

	'sync-check'=>['name'=>'Проверка синхронизации'],
)),

'sign'=>array('name'=>'Знаки', 'role'=>'catalog', '/'=>array(
	'new'=>array('name'=>'Новый знак'),
	'%num'=>array(),
)),

'sklad'=>array('name'=>'Склады', 'role'=>'sklad', '/'=>array(
	'%num'=>array(),
	'in'=>array(),
	'out'=>array(),
	'prices'=>array(),
)),

'store'=>array('name'=>'Каталог', '/'=>array(
	'new'=>array('name'=>'Новый товар', 'role'=>'catalog'),
	'%abc'=>array('wide'=>1, '/'=>array(
		'edit'=>array('virtual'=>1, 'role'=>'catalog'),
		'qr'=>array('virtual'=>1, 'role'=>'catalog'),
		'order'=>array('virtual'=>1, 'role'=>'order'),
		'erase'=>array('name'=>'Удалить товар?', 'role'=>'catalog'),
		'sync'=>array('name'=>'Синхронизация', 'role'=>'catalog'),
		'clone'=>array('name'=>'Клонировать', 'role'=>'catalog'),
	)),
	'url'=>array('name'=>'Обновление ссылок товаров', 'role'=>'catalog'),
)),

'subcat'=>array('name'=>'Подкаталоги', 'role'=>'catalog'),

'sync'=>array('name'=>'Прайсы и синхронизация', '/'=>array(
	'export-csv'=>array('name'=>'Экспорт csv', 'role'=>'csv'),
	'import-csv'=>array('name'=>'Импорт csv', 'role'=>'csv'),
	'import-xls'=>array('name'=>'Импорт xls', 'lib'=>'phpexcel', 'role'=>'import'),
	'sync'=>array('name'=>'Синхронизация xls', 'lib'=>'phpexcel', 'role'=>'sync'),
	'sync2'=>array('design'=>'none', 'lib'=>'phpexcel', 'role'=>'sync'),
	'nosync'=>array('name'=>'Не синхронизировать', 'role'=>'sync'),
	'check'=>array('name'=>'Проверка синхронизации', 'role'=>'sync'),
	'reset'=>array('name'=>'Снять все товары?', 'role'=>'csv'),
	'double'=>array('name'=>'Поиск дубликатов', 'role'=>'csv'),
	'search'=>array('name'=>'Поиск синхронизации', 'role'=>'import'),
)),

'user'=>array('name'=>'Пользователи', 'role'=>'users', '/'=>array(
	'%num'=>array('name'=>'Пользователь', '/'=>array(
		'docs'=>array('virtual'=>1),
		'order'=>array('virtual'=>1, 'wide'=>1, 'no-title'=>0),
		'erase'=>array('virtual'=>1),
		'sales'=>array('virtual'=>1),
	)),
	'new'=>array('name'=>'Добавить пользователя'),
	'sales'=>array('name'=>'Финансы', 'role'=>'money'),
	'check'=>array('name'=>'Проверка авторизации', 'action'=>'Проверка'),
)),

'vendor'=>array('name'=>'Поставщики', 'role'=>'catalog', '/'=>array(
	'new'=>array('name'=>'Добавить поставщика', 'action'=>'+ Поставщик', 'role'=>'import'),
	'%num'=>array('name'=>'Поставщик', '/'=>array(
		'edit'=>array('action'=>'Правка', 'virtual'=>1, 'role'=>'import'),
		'view'=>array('action'=>'Смотр', 'virtual'=>1),
		'erase'=>array('name'=>'Удалить поставщика из списка?', 'role'=>'import'),
	)),
)),





'%abc'=>array('/'=>array(
	'%abc'=>array('virtual'=>1, '/'=>array(
		'%abc'=>array('virtual'=>1, '/'=>array(
			'%abc'=>array('virtual'=>1, '/'=>array(
				'%abc'=>array('virtual'=>1)
			)),
		)),
	)),
)),

'404'=>array('name'=>'Запрошенная страница не найдена!'),
'base'=>array('design'=>'none', 'role'=>kv($config, 'DEBASE', 0) ? 'guest admin' : 'admin'),
'boot'=>array('name'=>'Инициализация', 'role'=>kv($config, 'DEBUG', 0) ? 'guest admin' : 'admin'),
//'data'=>array('design'=>'none', 'role'=>kv($config, 'DEBASE', 0) ? 'guest admin' : 'admin'),
'bases'=>array('design'=>'none', 'role'=>'admin'),
'city-dialog'=>array('design'=>'none'),
'clean-store'=>array('name'=>'Очистка лишних файлов каталога', 'role'=>'admin'),
'cron'=>array(),
'google52b158a7e10b1c51'=>array('design'=>'none'),
'help'=>array('/'=>array('%abc'=>array('virtual'=>1))),
'login'=>array('name'=>'Вход' /*, 'design'=>'login'*/),
'manifest.json'=>array('design'=>'none'),
'muz'=>array('name'=>'Импорт картинок из muzmarket.pro'),
'muz-icon'=>array('name'=>'Проверка и регенерация иконок'),
'oauth'=>array('name'=>'OAuth'),
'portal'=>array(),
'robots.txt'=>array(),
'serviceworker.js'=>array('design'=>'none'),
'serviceworker-update.js'=>array('design'=>'none'),
'test'=>array('name'=>'TEST', 'lib'=>'phpexcel'),
'unsubscribe'=>array('name'=>'Вы отписались от рассылки!'),
'update'=>array('name'=>'Обновление', 'role'=>kv($config, 'DEBUG', 0) ? 'guest admin' : 'admin'),

));