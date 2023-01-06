
-- ������������

create table user (
	i int auto_increment,
	dt int, -- ���� �����������
	last int, -- ��������� �����

	login tinytext, -- �����
	pass tinytext, -- ������

	name tinytext, -- ���
	phone tinytext, -- �������
	city int default 0, -- �����
	adres text, -- �����
	pay text, -- ��������� ��� ������
	ul tinyint, -- ����������� ����
	spam tinyint default 1, -- �������� ��������
	note tinyint default 1, -- �������� �� ����� ����������� �� ��������� ������� ������
	color tinyint, -- ����
	dost tinytext, -- ������ ��������

	roles tinytext, -- ���� ������������
	config text, -- ��������� ������������

	try tinyint, -- ����� ��������� �������, �� ����� 3 ������� � 5 �����
	dry int, -- ���� � ����� ��������� �������

	key (i)
);

insert into user (login, pass, roles) values ('admin', 'nimda', 'admin users');

-- ����

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

-- �������

create table catalog (
	i int auto_increment,
	dt int, -- ���� ���������� ���������
	up int, -- ����������� ������
	tag0 tinyint, -- ������ ����
	tag1 text,
	tag2 text,
	tag3 text,
	name tinytext, -- ������� ������������ �������
	name2 tinytext, -- ������ ������������ �������
	short tinytext, -- ������� ���. �����
	info text, -- ������ ���. �����
	icon tinytext, -- ������ �������
	hide tinyint, -- �����
	w int, -- ���
	google int, -- ��� �����

	filter text, -- ������ ������� �� �������
	brand text, -- ������ ������� �������

	key (i)
);

-- ������� ������

create table subcat (
	i int auto_increment,

	up int, -- ������
	code varchar(32), -- ��� ��� ������

	brand int, -- ������
	filter int, -- ������
	fvalue int, -- �������� �������

	tag0 tinyint, -- ������ ����
	tag1 text,
	tag2 text,
	tag3 text,
	name tinytext, -- ������� ������������ �������
	name2 tinytext, -- ������ ������������ �������
	short tinytext, -- ������� ���. �����
	info text, -- ������ ���. �����
	count int, -- ����������

	index (up),
	index (code),
	key (i)
);

-- �����

create table store (
	i int, -- �����
	up int, -- ������
	grp tinyint, -- ������ ������
	code tinytext, -- ��� ������ (�������)
	quick mediumtext, -- ������ �������� ������
	tags tinytext, -- ����
	hide tinyint, -- �����
	sign1 int default 0 not null, -- ���� 1
	sign2 int default 0 not null, -- ���� 2
	sign3 int default 0 not null, -- ���� 3
	sign4 int default 0 not null, -- ���� 4

	name tinytext, -- ������������ ������
	sync int default 0 not null, -- ���� ��������� �������������
	yandex int default 0 not null, -- ��� � ������-�������
	brand int, -- �������������
	vendor int, -- ���������
	short tinytext, -- ��������������
	city int default 0 not null, -- �����
	speed int default 0 not null, -- �������� �����������

	icon tinytext, -- ������ ������
	mini tinytext, -- ������� �������� ������
	pic tinytext, -- ������� �����������

	pics text, -- �������������� ��������
	files text, -- ������������ �����

	price int, -- ���� � ������
	sale tinyint, -- �������� ������
	count tinytext, -- ���������� �� ������

	user int, -- ������������, ������� ���������
	dt int, -- ���� ����������
	info text, -- �����������

	w int, -- ��� ��� ����������

	filter text, -- �������� ��� �������

	rule int, -- ����� ����������� ������� ���������������

	key (i)
);

-- �������

create table filter (
	i int auto_increment,

	name tinytext, -- ��������
	info tinytext, -- ��������
	value text, -- ��������

	key (i)
);

-- ������������� ������� store

create table sync (
	i int auto_increment,
	code varchar(15), -- �������
	name varchar(64), -- ������������
	store int, -- ������ �� �����
	vendor int default 0 not null, -- ������ �� ����������
	dt int, -- ���� �������� ������
	price int, -- ����
	opt int, -- ������� ����
	count int default 0 not null, -- ���������� �� ������

	key (i),
	index (name),
	index (store)
);

-- �������������

create table brand (
	i int auto_increment, -- key

	code varchar(32), -- ��� ��� ������
	name tinytext, -- ������������
	icon tinytext, -- ������, 120*120
	info text, -- �������� � �����������
	w int, -- ���

	key (i)
);

-- ���������

create table vendor (
	i int auto_increment, -- key

	name tinytext, -- ������������
	w int, -- ���

	price tinytext, -- ������� ��������� ����
	city int default 0 not null, -- �����
	speed int, -- �������� �����������

	ccode tinytext, -- ����� ������� � ���������
	cname tinytext, -- ����� ������� � �������������
	ctype tinyint, -- ����� ������� � �������
	cbrand tinytext, -- ����� ������� � ��������������
	ccount tinytext, -- ����� ������� � �����������
	�price tinytext, -- ����� ������� � �����
	copt tinytext, -- ����� ������� � ������� �����

	min int, -- ��� ����

	curr tinyint, -- ������, 0 - �����, 1 - ������, 2 - ����

	info text, -- �������������� ����������

	key (i)
);

-- �����

create table sign (
	i int auto_increment, -- key

	name tinytext, -- ������������
	mini tinytext, -- ��������
	info text, -- ��������

	key (i)
);

-- ������

create table orst (
	i int auto_increment, -- key
	dt int, -- ���� ��������
	last int, -- ���� ���������� ���������
	user int, -- ������������
	staff int, -- ��������
	state tinyint, -- ������ ������

	adres text, -- ����� ��������

	vendor int default 0, -- ���������
	store int, -- ������ �� �����
	name text, -- ������������
	price int, -- ����
	count int, -- ����������

	pay tinyint default 0, -- ������ ������
	money int default 0, -- ��������
	pay2 tinyint default 0, -- ������ ������ 2
	money2 int default 0, -- �������� 2

	money0 int default 0, -- ������ �� ��������

	bill int, -- ������ �� ������

	sale varchar(12), -- ��� ������

	info text, -- �������������
	note text, -- ��������� ��� ����������

	docs text, -- ������������ ��������� ��� ����������
	files text, -- ������������ ����� ��� ����

	key (i)
);

-- ����� �� ������

create table bill (
	i int auto_increment, -- key
	type int default 0, -- ��� �����, 0 -- ������, 1 -- �����
	orst text, -- ���� ������
	user int, -- ����������
	staff int, -- �������� ��������� �����

	dt1 int, -- ���� �������� �����
	dt2 int, -- ���� ���������� �����
	total decimal(11,2), -- ����� �����

	state int, -- ������ �����
	status tinytext, -- ������ ����� �������
	info text, -- �������� ��������� �����

	key (i)
);

-- ���������� �������

create table city (
	i int auto_increment,

	hide tinyint default 0, -- ���� 1, �� ����� �����
	region int, -- ���� �������
	name tinytext, -- ������������ ������
	w int, -- ��� ������ (��� ����������)

	phone tinytext, -- �������� �������
	mail tinytext, -- ����������� ����� �������
	adres text, -- ������
	sign tinytext, -- �������� ����� � ������� ����������

	key (i)
);

-- ���������� ��������

create table region (
	i int auto_increment, -- ���� �������

	name tinytext, -- ������������ �������
	w int, -- ���, ��� ����������

	key (i)
);

-- �������� ��������

create table speeds (
	cire1 int, -- ������
	cire2 int, -- ����
	speed int default 0, -- ����� ��������

	index (cire1, cire2)
);

-- ���������������

create table prices (
	i int, -- �����
	up int, -- �������
	grp tinytext, -- ������ ������
	brand tinytext, -- �����
	vendor int, -- ���������
	count tinyint, -- �������

	price tinyint, -- �������� � �����
	sale tinyint, -- ������
	days int, -- ���� ������������� �� ������

	key (i)
);

-- ������

create table sale (
	code varchar(12), -- ��� ������
	name tinytext, -- ��������
	dt int, -- �������
	user int, -- ������� �������������
	dt2 int, -- ���� ��������� ������
	perc int, -- ������� ������
	partner int, -- �������

	up tinytext, -- �������
	brand tinytext, -- ������

	key (code)
);

-- ���������

create table docs (
	i int auto_increment, -- ���

	orst int, -- �����
	staff int, -- ��������

	store tinytext, -- ������ � ������� |���1|���2|���3|���4|
	user int, -- ������������

	type tinyint, -- ���
	num int, -- �����
	dt int, -- ����

	name tinytext, -- ��������
	total decimal(11,2), -- ����� ���������
	data text, -- ������

	key (i)
);

-- ���������

create table pf (
	i int, -- �����
	up tinyint, -- ������

	name tinytext, -- ��������
	dt tinytext, -- ����

	pics text, -- ��������
	info text, -- ��������

	key (i)
);

-- ����

create table menu (
	code varchar(250), -- ���

	tags tinytext, -- ����
	name tinytext, -- ������������
	up varchar(250), -- ������
	type tinyint default 0, -- ���: 0 -- �������, 1 -- ������
	hide tinyint default 0, -- �����
	body text, -- ����

	w int default 100, -- ���, ��� ����������

	index(up),
	key (code)
);

-- �����

create table block (
	code varchar(30), -- ����

	type varchar(15), -- ��� �����
	name tinytext, -- ������������ �����
	info text, -- ������ �����

	key (code)
);

-- �����������

create table comment (
	i int auto_increment, -- ���� �����������
	theme varchar(15), -- ���� �����������, �������� �� �����������

	type tinyint default 0, -- ��� �����������, 0: ������������
	user int, -- ���� ������������
	dt int, -- ���� ��������
	body text, -- ����� �����������

	index (theme),
	key (i)
);

-- ��������

create table mail (
	i int auto_increment, -- ����
	dt int, -- ���� ���������� ����������
	user int, -- �������

	info text, -- �����

	key (i)
);

-- ������� ���������

create table speed2 (
	cire int, -- ����� ��� ������
	vendor int, -- ���������
	speed int, -- ��������

	index (cire, vendor)
);

-- ���������/��������� ���������

create table naklad (
	i int auto_increment, -- ����

	dt int, -- ���� ��������/����������
	user int, -- �����

	vendor int, -- �����
	type tinyint, -- ��� ���������

	info text, -- �����������

	key (i)
);

-- ������ � ���������

create table nakst (
	i int auto_increment, -- ����

	naklad int, -- ���������

	store int, -- �����
	count int, -- ����������

	key (i)
);

-- �����

create table site (
	i int auto_increment,
	name tinytext, -- �������� �����
	url tinytext, -- �����

	key (i)
);

-- �������

create table dict (
	i int auto_increment,
	name tinytext, -- ��������
	code tinytext, -- ���

	key (i)
);

-- �����

create table word (
	i int auto_increment,
	site int, -- ����
	dict int, -- �������

	value text, -- ��������

	key (i)
);