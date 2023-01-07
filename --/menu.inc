<?php

/*
 *	Copyright flydom.ru
 *	Version 2.0.2011-01-24
 */

$menu = [];
$menu[] = ['name'=>'', 'wide'=>1, '/'=>[

'api'=>['name'=>'API', 'design'=>'none', '/'=>[
	'yandex'=>['design'=>'yandex', '/'=>[
		'%num'=>['virtual'=>1, '/'=>[
			'cart'=>['log'=>201],
			'order'=>['/'=>[
				'accept'=>['log'=>205],
				'status'=>['log'=>209],
			]],
			'stocks'=>['log'=>211],
		]],
	]],
	'goods'=>['design'=>'goods', '/'=>[
		'%num'=>['virtual'=>1, '/'=>[
			'order'=>['/'=>[
				'new'=>['log'=>501],
				'cancel'=>['log'=>508],
			]],
		]],
	]],
]],

'article'=>['name'=>'Статьи', '/'=>[
	'%abc'=>['name'=>'Статья', '/'=>[
		'edit'=>['virtual'=>1, 'role'=>'menu', 'action'=>'правка'],
	]],
	'new'=>['name'=>'Новая статья', 'action'=>'создать', 'role'=>'menu'],
]],

'auto'=>['name'=>'Водитель', '/'=>[
	'%num'=>['name'=>'Заказ'],
]],

'basket'=>['name'=>'Корзина', 'wide'=>1, '/'=>[
	'buy'=>['design'=>'none'],
	'thanks'=>['name'=>'Благодарим за заказ!'],
	'ok'=>['name'=>'Платеж успешен!'],
	'no'=>['name'=>'Оплата отменена.'],
	'uniteller'=>['name'=>'Уведомления Uniteller'],
	'appex'=>['name'=>'Appex'],
	'print'=>['name'=>'Коммерческое предложение', 'design'=>'print'],
	'save'=>['design'=>'none'],
	'load'=>['name'=>'Загрузить корзину'],
	'next'=>['name'=>'Подтверждение заказа', 'wide'=>1],
	'naklad'=>['name'=>'Новая накладная', 'role'=>'sklad'],
]],

'bill'=>['name'=>'Счета оплаты банковской картой', '/'=>[
	'%num'=>['name'=>'Оплата через интернет', '/'=>[
		'pay'=>['virtual'=>1],
		'edit'=>['virtual'=>1, 'role'=>'order'],
		'view'=>['virtual'=>1],
	]],
]],

'block'=>['name'=>'Блоки', 'role'=>'block', '/'=>[
	'%abc'=>[],
]],

'brand'=>['name'=>'Производители', '/'=>[
	'new'=>['name'=>'Добавить производителя'],
	'%abc'=>['/'=>[
		'edit'=>['role'=>'catalog', 'virtual'=>1, 'action'=>'правка'],
		'erase'=>['name'=>'Удалить производителя из списка?', 'role'=>'catalog'],
	]],
]],

'cache'=>['role'=>kv($config, 'DEBUG', 0) ? 'guest admin' : 'admin'],

'captcha' => ['/'=>[
	'%abc'=>['virtual'=>1],
]],

'catalog'=>['name'=>'Каталог', 'wide'=>1, '/'=>[
	'new'=>['name'=>'Новый раздел', 'role'=>'catalog'],
	'%abc'=>['wide'=>1, '/'=>[
		'%abc'=>['virtual'=>1, 'wide'=>1],
		'edit'=>['role'=>'catalog', 'virtual'=>1],
		'erase'=>['role'=>'catalog', 'name'=>'Удалить раздел?'],
		'subcat'=>['role'=>'catalog', 'name'=>'Подкаталоги'],
	]],
]],

'city'=>['name'=>'Справочник городов', 'role'=>'adres', '/'=>[
	'new'=>['name'=>'Добавить город'],
	'%num'=>[],
]],

'dict'=>['name'=>'Словарь', 'role'=>'admin', '/'=>[
	'%num'=>['/'=>[
		'erase'=>['virtual'=>1, 'action'=>'Удалить'],
	]],
	'site'=>['/'=>[
		'%num'=>['virtual'=>1],
	]],
	'new'=>['name'=>'Добавить слово', 'action'=>'Новое слово'],
]],

'doc'=>['name'=>'Документы', '/'=>[
	'%num'=>[],
]],

'export'=>['design'=>'none', '/'=>[
	'store'=>[],
	'catalog'=>[],
]],

'filter'=>['name'=>'Фильтры', 'role'=>'catalog', '/'=>[
	'new'=>['name'=>'Новый фильтр'],
	'%num'=>[],
]],

'kkm'=>['name'=>'Фискальные чеки', 'role'=>'kkm', '/'=>[
	'%num'=>[],
]],

'mark'=>['name'=>'Метки', 'role'=>'catalog', '/'=>[
	'new'=>['name'=>'Новая метка', 'action'=>'создать'],
	'%num'=>[],
]],

'menu'=>['name'=>'Меню', 'role'=>'menu', '/'=>[
	'edit'=>[],
	'new'=>['name'=>'Добавить страницу'],
]],

'my'=>['name'=>'Мои заказы', '/'=>[
	'user'=>['name'=>'Анкета', 'no-title'=>1],
	'adres'=>['name'=>'Выберите город'],
//	'pay'=>['name'=>'Оплата'],
]],

'order'=>['name'=>'Заказы', 'wide'=>1, 'role'=>'order', '/'=>[
	'%num'=>['name'=>'Заказ', '/'=>[
		'edit'=>['virtual'=>1, 'action'=>'Заказ'],
		'print'=>['action'=>'Документы'],
		'link'=>['action'=>'Связанные', 'virtual'=>1, 'wide'=>1, 'no-title'=>0],
		'map'=>['action'=>'Карта'],
	]],
//	'link'=>['name'=>'Связанные', 'no-title'=>1, 'wide'=>1, 'role'=>'link'],
]],

'patch'=>['name'=>'Патч', 'role'=>'admin'],

'partner'=>['name'=>'Партнер', 'role'=>'partner'],

'portfolio'=>['name'=>'Наши работы', 'nowrap'=>1, '/'=>[
	'%abc'=>['/'=>[
		'edit'=>['role'=>'block', 'virtual'=>1],
		'erase'=>['rolo'=>'block'],
	]],
	'new'=>['name'=>'Новая запись', 'role'=>'block'],
	'groups'=>['name'=>'Группы', 'role'=>'block'],
]],

'prices'=>['name'=>'Ценообразование', 'role'=>'import', '/'=>[
	'%num'=>[],
	'groups'=>['name'=>'Группы товаров', 'action'=>'Группы товаров'],

	'plan'=>['name'=>'', '/'=>[
		'%num'=>[],
	]],
]],

'rate'=>['name'=>'Отзывы', 'role'=>'rate', '/'=>[
	'new'=>['name'=>'Новый отзыв'],
	'%num'=>[],
]],

'region'=>['name'=>'Справочник регионов', 'role'=>'adres', '/'=>[
	'new'=>['name'=>'Добавить регион'],
	'%num'=>[],
]],

'sales'=>['name'=>'Популярные товары', '/'=>[
	'list'=>['name'=>'Скидки', 'role'=>'sale'],
	'new'=>['name'=>'Новая скидка', 'role'=>'sale'],
	'%abc'=>['role'=>'sale', '/'=>[
		'orders'=>[],
	]],
	'cache'=>['name'=>'Обновление кэша', 'action'=>'Обновить кэш', 'role'=>'catalog', 'virtual'=>1],
]],

'setup'=>['name'=>'Настройки', 'role'=>'adres catalog admin log spam', '/'=>[
	'adres'=>['name'=>'Адреса и доставка', 'role'=>'adres'],
	'delivery'=>['name'=>'Время доставки', 'role'=>'adres'],
	'parse'=>['name'=>'Импорт каталога', 'role'=>'catalog', 'lib'=>'parse'],
	'google'=>['name'=>'Категории Google', 'role'=>'catalog'],
	'lost'=>['name'=>'Потерянные товары', 'role'=>'catalog'],
	'spam'=>['name'=>'Рассылка', 'role'=>'spam'],
	'geoloc'=>['name'=>'Импорт данных геолокации', 'role'=>'admin'],
	'update-tags'=>['name'=>'Обновление тегов', 'role'=>'admin'],
	'log'=>['name'=>'Логи', 'role'=>'log'],
	'log2'=>['name'=>'Ошибки', 'role'=>'log'],
	'img-fluid'=>['name'=>'Обновление картинок', 'role'=>'admin'],
	'yml'=>['name'=>'Выгрузка YML', 'role'=>'catalog', '/'=>[
		'%num'=>[],
	]],
	'ozon'=>['name'=>'Выгрузка Ozon', 'role'=>'catalog', '/'=>[
		'%num'=>[],
		'cron'=>['name'=>'Запуск планировщика'],
	]],
	'wildberries'=>['name'=>'Выгрузка Wildberries', 'role'=>'catalog', '/'=>[
		'%num'=>[],
		'cron'=>['name'=>'Запуск планировщика'],
		'load'=>['name'=>'Загрузка номенклатуры', 'lib'=>'phpexcel'],
	]],

	'import-dyna'=>['name'=>'Импорт товаров динатона', 'role'=>'admin'],
	'import-dyna-2'=>['name'=>'Обработка товаров динатона', 'role'=>'admin'],

	'ip'=>['name'=>'IP', 'role'=>'admin'],

	'reset'=>['name'=>'RESET'],

	'sync-check'=>['name'=>'Проверка синхронизации'],
]],

'sign'=>['name'=>'Знаки', 'role'=>'catalog', '/'=>[
	'new'=>['name'=>'Новый знак'],
	'%num'=>[],
]],

'sklad'=>['name'=>'Склады', 'role'=>'sklad', '/'=>[
	'%num'=>[],
	'in'=>[],
	'out'=>[],
	'prices'=>[],
]],

'store'=>['name'=>'Каталог', '/'=>[
	'new'=>['name'=>'Новый товар', 'role'=>'catalog'],
	'%abc'=>['wide'=>1, '/'=>[
		'edit'=>['virtual'=>1, 'role'=>'catalog'],
		'qr'=>['virtual'=>1, 'role'=>'catalog'],
		'order'=>['virtual'=>1, 'role'=>'order'],
		'erase'=>['name'=>'Удалить товар?', 'role'=>'catalog'],
		'sync'=>['name'=>'Синхронизация', 'role'=>'catalog'],
		'clone'=>['name'=>'Клонировать', 'role'=>'catalog'],
	]],
	'url'=>['name'=>'Обновление ссылок товаров', 'role'=>'catalog'],
]],

'subcat'=>['name'=>'Подкаталоги', 'role'=>'catalog'],

'sync'=>['name'=>'Прайсы и синхронизация', '/'=>[
	'export-csv'=>['name'=>'Экспорт csv', 'role'=>'csv'],
	'import-csv'=>['name'=>'Импорт csv', 'role'=>'csv'],
	'import-xls'=>['name'=>'Импорт xls', 'lib'=>'phpexcel', 'role'=>'import'],
	'sync'=>['name'=>'Синхронизация xls', 'lib'=>'phpexcel', 'role'=>'sync'],
	'sync2'=>['design'=>'none', 'lib'=>'phpexcel', 'role'=>'sync'],
	'nosync'=>['name'=>'Не синхронизировать', 'role'=>'sync'],
	'check'=>['name'=>'Проверка синхронизации', 'role'=>'sync'],
	'reset'=>['name'=>'Снять все товары?', 'role'=>'csv'],
	'double'=>['name'=>'Поиск дубликатов', 'role'=>'csv'],
	'search'=>['name'=>'Поиск синхронизации', 'role'=>'import'],
]],

'user'=>['name'=>'Пользователи', 'role'=>'users', '/'=>[
	'%num'=>['name'=>'Пользователь', '/'=>[
		'docs'=>['virtual'=>1],
		'order'=>['virtual'=>1, 'wide'=>1, 'no-title'=>0],
		'erase'=>['virtual'=>1],
		'sales'=>['virtual'=>1],
	]],
	'new'=>['name'=>'Добавить пользователя'],
	'sales'=>['name'=>'Финансы', 'role'=>'money'],
	'check'=>['name'=>'Проверка авторизации', 'action'=>'Проверка'],
]],

'vendor'=>['name'=>'Поставщики', 'role'=>'catalog', '/'=>[
	'new'=>['name'=>'Добавить поставщика', 'action'=>'+ Поставщик', 'role'=>'import'],
	'%num'=>['name'=>'Поставщик', '/'=>[
		'edit'=>['action'=>'Правка', 'virtual'=>1, 'role'=>'import'],
		'view'=>['action'=>'Смотр', 'virtual'=>1],
		'erase'=>['name'=>'Удалить поставщика из списка?', 'role'=>'import'],
	]],
]],





'%abc'=>['/'=>[
	'%abc'=>['virtual'=>1, '/'=>[
		'%abc'=>['virtual'=>1, '/'=>[
			'%abc'=>['virtual'=>1, '/'=>[
				'%abc'=>['virtual'=>1]
			]],
		]],
	]],
]],

'404'=>['name'=>'Запрошенная страница не найдена!'],
'base'=>['design'=>'none', 'role'=>kv($config, 'DEBASE', 0) ? 'guest admin' : 'admin'],
'boot'=>['name'=>'Инициализация', 'role'=>kv($config, 'DEBUG', 0) ? 'guest admin' : 'admin'],
//'data'=>['design'=>'none', 'role'=>kv($config, 'DEBASE', 0] ? 'guest admin' : 'admin'],
'bases'=>['design'=>'none', 'role'=>'admin'],
'city-dialog'=>['design'=>'none'],
'clean-store'=>['name'=>'Очистка лишних файлов каталога', 'role'=>'admin'],
'cron'=>[],
'google52b158a7e10b1c51'=>['design'=>'none'],
'help'=>['/'=>['%abc'=>['virtual'=>1]]],
'login'=>['name'=>'Вход' /*, 'design'=>'login'*/],
'manifest.json'=>['design'=>'none'],
'muz'=>['name'=>'Импорт картинок из muzmarket.pro'],
'muz-icon'=>['name'=>'Проверка и регенерация иконок'],
'oauth'=>['name'=>'OAuth'],
'portal'=>[],
'robots.txt'=>[],
'serviceworker.js'=>['design'=>'none'],
'serviceworker-update.js'=>['design'=>'none'],
'test'=>['name'=>'TEST', 'lib'=>'phpexcel'],
'unsubscribe'=>['name'=>'Вы отписались от рассылки!'],
'update'=>['name'=>'Обновление', 'role'=>kv($config, 'DEBUG', 0) ? 'guest admin' : 'admin'],

]];