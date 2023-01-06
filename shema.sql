
-- Пользователи

create table user (
	i int auto_increment,
	dt int, -- дата регистрации
	last int, -- последний визит

	login tinytext, -- почта
	pass tinytext, -- пароль

	name tinytext, -- имя
	phone tinytext, -- телефон
	city int default 0, -- город
	adres text, -- адрес
	pay text, -- реквизиты для оплаты
	ul tinyint, -- юридическое лицо
	spam tinyint default 1, -- получать рассылку
	note tinyint default 1, -- получать на почту уведомления об изменении статуса заказа
	color tinyint, -- цвет
	dost tinytext, -- способ доставки

	roles tinytext, -- роли пользователя
	config text, -- настройки пользователя

	try tinyint, -- номер последней попытки, не более 3 попыток в 5 минут
	dry int, -- дата и время последней попытки

	key (i)
);

insert into user (login, pass, roles) values ('admin', 'nimda', 'admin users');

-- Логи

create table log (
	i int auto_increment,
	type int default 0,
	user int,
	dt int,
	info text,

	index (user),
	index (type),
	key (i)
);

-- Разделы

create table catalog (
	i int auto_increment,
	dt int, -- Дата последнего изменения
	up int, -- Вышестоящий раздел
	tag0 tinyint, -- ручные теги
	tag1 text,
	tag2 text,
	tag3 text,
	name tinytext, -- Краткое наименование раздела
	name2 tinytext, -- Полное наименование раздела
	short tinytext, -- краткий доп. текст
	info text, -- полный доп. текст
	icon tinytext, -- Иконка раздела
	hide tinyint, -- Скрыт
	w int, -- Вес
	google int, -- Код гугла

	filter text, -- Фильтр раздела по товарам
	brand text, -- бренды данного раздела

	key (i)
);

-- Теневой раздел

create table subcat (
	i int auto_increment,

	up int, -- Раздел
	code varchar(32), -- Код для ссылки

	brand int, -- Бренду
	filter int, -- Фильтр
	fvalue int, -- Значение фильтра

	tag0 tinyint, -- ручные теги
	tag1 text,
	tag2 text,
	tag3 text,
	name tinytext, -- Краткое наименование раздела
	name2 tinytext, -- Полное наименование раздела
	short tinytext, -- краткий доп. текст
	info text, -- полный доп. текст
	count int, -- количество

	index (up),
	index (code),
	key (i)
);

-- Склад

create table store (
	i int, -- Номер
	up int, -- Раздел
	grp tinyint, -- Группа товара
	code tinytext, -- Код товара (артикул)
	quick mediumtext, -- Строка быстрого поиска
	tags tinytext, -- Теги
	hide tinyint, -- Скрыт
	sign1 int default 0 not null, -- Знак 1
	sign2 int default 0 not null, -- Знак 2
	sign3 int default 0 not null, -- Знак 3
	sign4 int default 0 not null, -- Знак 4

	name tinytext, -- Наименование товара
	sync int default 0 not null, -- Дата последней синхронизации
	yandex int default 0 not null, -- Код в яндекс-маркете
	brand int, -- Производитель
	vendor int, -- Поставщик
	short tinytext, -- Характеристика
	city int default 0 not null, -- Город
	speed int default 0 not null, -- Скорость поступления

	icon tinytext, -- Иконка товара
	mini tinytext, -- Средняя картинка товара
	pic tinytext, -- Большое изображение

	pics text, -- Дополнительные картинки
	files text, -- Прицепленные файлы

	price int, -- Цена в рублях
	sale tinyint, -- Возможна скидка
	count tinytext, -- Количество на складе

	user int, -- Пользователь, внесший изменение
	dt int, -- Дата информации
	info text, -- Комментарий

	w int, -- Вес для сортировки

	filter text, -- Значения для фильтра

	rule int, -- Номер применённого правила ценообразования

	key (i)
);

-- Фильтры

create table filter (
	i int auto_increment,

	name tinytext, -- Название
	info tinytext, -- Описание
	value text, -- Значения

	key (i)
);

-- Синхронизация таблицы store

create table sync (
	i int auto_increment,
	code varchar(15), -- Артикул
	name varchar(64), -- Наименование
	store int, -- Ссылка на склад
	vendor int default 0 not null, -- Ссылка на поставщика
	dt int, -- Дата создания ссылки
	price int, -- Цена
	opt int, -- Оптовая цена
	count int default 0 not null, -- Количество на складе

	key (i),
	index (name),
	index (store)
);

-- Производитель

create table brand (
	i int auto_increment, -- key

	code varchar(32), -- Код для ссылки
	name tinytext, -- Наименование
	icon tinytext, -- Иконка, 120*120
	info text, -- страница с информацией
	w int, -- Вес

	key (i)
);

-- Поставщик

create table vendor (
	i int auto_increment, -- key

	name tinytext, -- Наименование
	w int, -- Вес

	price tinytext, -- Процент изменения цены
	city int default 0 not null, -- Город
	speed int, -- Скорость поступления

	ccode tinytext, -- Номер колонки с артикулом
	cname tinytext, -- Номер колонки с наименованием
	ctype tinyint, -- Номер колонки с моделью
	cbrand tinytext, -- Номер колонки с производителем
	ccount tinytext, -- Номер колонки с количеством
	сprice tinytext, -- Номер колонки с ценой
	copt tinytext, -- Номер колонки с оптовой ценой

	min int, -- мин цена

	curr tinyint, -- Валюта, 0 - рубли, 1 - доллар, 2 - евро

	info text, -- Дополнительная информация

	key (i)
);

-- Знаки

create table sign (
	i int auto_increment, -- key

	name tinytext, -- Наименование
	mini tinytext, -- Картинка
	info text, -- Описание

	key (i)
);

-- Заявки

create table orst (
	i int auto_increment, -- key
	dt int, -- Дата создания
	last int, -- Дата последнего изменения
	user int, -- Пользователь
	staff int, -- Продавец
	state tinyint, -- Стадия заявки

	adres text, -- Адрес поставки

	vendor int default 0, -- Поставщик
	store int, -- Ссылка на товар
	name text, -- Наименование
	price int, -- Цена
	count int, -- Количество

	pay tinyint default 0, -- Способ оплаты
	money int default 0, -- Оплачено
	pay2 tinyint default 0, -- Способ оплаты 2
	money2 int default 0, -- Оплачено 2

	money0 int default 0, -- Оплата за доставку

	bill int, -- ссылка на оплату

	sale varchar(12), -- Код скидки

	info text, -- Дополнительно
	note text, -- Замечания для менеджеров

	docs text, -- прикреплённые документы для менеджеров
	files text, -- прикреплённые файлы для всех

	key (i)
);

-- Счета на оплату

create table bill (
	i int auto_increment, -- key
	type int default 0, -- Тип счета, 0 -- оплата, 1 -- аванс
	orst text, -- Коды заявок
	user int, -- Покупатель
	staff int, -- Менеджер создавший отчет

	dt1 int, -- Дата создания счета
	dt2 int, -- Дата обновления счета
	total decimal(11,2), -- Сумма счета

	state int, -- Статус счета
	status tinytext, -- Статус счета текстом
	info text, -- Описание состояния счета

	key (i)
);

-- Справочник городов

create table city (
	i int auto_increment,

	hide tinyint default 0, -- если 1, то город скрыт
	region int, -- ключ региона
	name tinytext, -- наименование города
	w int, -- вес города (для сортировки)

	phone tinytext, -- телефоны филиала
	mail tinytext, -- электронный адрес филиала
	adres text, -- адреса
	sign tinytext, -- короткий адрес в подписи документов

	key (i)
);

-- Справочник регионов

create table region (
	i int auto_increment, -- ключ региона

	name tinytext, -- наименование региона
	w int, -- вес, для сортировки

	key (i)
);

-- Скорость доставки

create table speeds (
	cire1 int, -- откуда
	cire2 int, -- куда
	speed int default 0, -- время доставки

	index (cire1, cire2)
);

-- Ценообразование

create table prices (
	i int, -- номер
	up int, -- каталог
	grp tinytext, -- группа товара
	brand tinytext, -- бренд
	vendor int, -- поставщик
	count tinyint, -- наличие

	price tinyint, -- действие с ценой
	sale tinyint, -- скидка
	days int, -- дата синхронизации не старше

	key (i)
);

-- Скидки

create table sale (
	code varchar(12), -- Код скидки
	name tinytext, -- Описание
	dt int, -- Создана
	user int, -- Создана пользователем
	dt2 int, -- Дата окончания скидки
	perc int, -- Процент скидки
	partner int, -- Партнер

	up tinytext, -- Разделы
	brand tinytext, -- Бренды

	key (code)
);

-- Документы

create table docs (
	i int auto_increment, -- Код

	orst int, -- Заказ
	staff int, -- Менеджер

	store tinytext, -- Товары в формате |код1|код2|код3|код4|
	user int, -- Пользователь

	type tinyint, -- Тип
	num int, -- Номер
	dt int, -- Дата

	name tinytext, -- Название
	total decimal(11,2), -- Сумма документа
	data text, -- Данные

	key (i)
);

-- Портфолио

create table pf (
	i int, -- Номер
	up tinyint, -- Раздел

	name tinytext, -- Название
	dt tinytext, -- Дата

	pics text, -- Картинки
	info text, -- Описание

	key (i)
);

-- Меню

create table menu (
	code varchar(250), -- код

	tags tinytext, -- Теги
	name tinytext, -- Наименование
	up varchar(250), -- предок
	type tinyint default 0, -- тип: 0 -- обычный, 1 -- раздел
	hide tinyint default 0, -- скрыт
	body text, -- Тело

	w int default 100, -- вес, для сортировки

	index(up),
	key (code)
);

-- Блоки

create table block (
	code varchar(30), -- ключ

	type varchar(15), -- тип блока
	name tinytext, -- наименование блока
	info text, -- данные блока

	key (code)
);

-- Комментарии

create table comment (
	i int auto_increment, -- ключ комментария
	theme varchar(15), -- тема комментария, которому он принадлежит

	type tinyint default 0, -- тип комментария, 0: произвольный
	user int, -- ключ пользователя
	dt int, -- дата создания
	body text, -- текст комментария

	index (theme),
	key (i)
);

-- Рассылка

create table mail (
	i int auto_increment, -- ключ
	dt int, -- дата последнего обновления
	user int, -- адресат

	info text, -- текст

	key (i)
);

-- Таблица скоростей

create table speed2 (
	cire int, -- город или регион
	vendor int, -- поставщик
	speed int, -- скорость

	index (cire, vendor)
);

-- Приходные/расходные накладные

create table naklad (
	i int auto_increment, -- ключ

	dt int, -- дата создания/проведения
	user int, -- автор

	vendor int, -- склад
	type tinyint, -- тип накладной

	info text, -- комментарий

	key (i)
);

-- Товары в накладной

create table nakst (
	i int auto_increment, -- ключ

	naklad int, -- накладная

	store int, -- товар
	count int, -- количество

	key (i)
);

-- Сайты

create table site (
	i int auto_increment,
	name tinytext, -- Название сайта
	url tinytext, -- адрес

	key (i)
);

-- Словарь

create table dict (
	i int auto_increment,
	name tinytext, -- Название
	code tinytext, -- код

	key (i)
);

-- Слова

create table word (
	i int auto_increment,
	site int, -- сайт
	dict int, -- словарь

	value text, -- значение

	key (i)
);