<?php

/*
 *	Copyright flydom.ru
 *	Version 3.0.2011-07-15
 */

//header('Content-Encoding: gzip');

error_reporting(E_ALL);
ini_set('display_errors', 'stdout');

ini_set('magic_quotes_runtime', 0);
ini_set('magic_quotes_sybase', 0);
ini_set('zlib.output_compression', 'On');
ini_set('session.name', 'muzmart');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 0);
ini_set('session.cache_expire', 60*60*8);

$config['database'] = 'mysql://admin:nimda@127.0.0.1/muz';
//$config['database'] = 'mysql://ryurix_second:nimda@localhost/ryurix_second';
//$config['database'] = 'mysql://045315160_mse:nimda@localhost/hmaomarketing_mse';
$config['find'] = array('user' => 1, 'new' => 2, 'text' => 3);

$config['libs'] = array('include', 'forms', 'types', 'input', 'mail', 'work', 'catalog', 'f-doc', 'design', 'include2');
$config['root'] = getcwd().'/';

$config['title'] = 'Muzmart';
$config['site'] = 'muzmart.com';
$config['meta'] = 'Профессиональное мультимедийное оборудование проектирование, установка, продажа, аренда';
$config['backmail'] = ''; // gmail account
$config['backpass'] = ''; // gmail password
$config['front'] = '/';
$config['design'] = 'design';
$config['GMT'] = 0;

$config['kkmserver'] = 'http://admin:@muzmart.com:5894';

$config['update-url'] = 'http://ruvix.ru';

// test

$config['city']['head'] = '';
$config['domain'] = 'mmart';

$config['DEBUG'] = 1;
$config['DEBASE'] = 1;

define('MARKETPLACE_MARK', 14);

$user['0'] = array(
	'i' => 0,
	'login' => 'guest',
	'name' => '',
	'pass' => '',
	'roles' => array('guest'),

	'quick'=>NULL,
	'phone'=>NULL,
	'email'=>NULL,
	'city'=>NULL,
	'adres'=>NULL,
	'pay'=>NULL,
	'ul'=>NULL,
	'spam'=>NULL,
	'note'=>NULL,
	'color'=>NULL,
	'dost'=>NULL,
	'config'=>NULL,
);

$yandex = [
	5048=>['campaignId'=>21927869, 'token'=>'80000001EE76460A', 'cron'=>49, 'oauth_token'=>'AgAAAABIUtW1AAbVgJN4iEP5WURDp4R1aIzHqVA', 'oauth_client_id'=>'1b902bf296114e5f9959440e91ba6069'],
	13060=>['campaignId'=>21622579, 'token'=>'5B0000015A710316', 'cron'=>24, 'oauth_token'=>'AgAAAAAHpXmLAAbXrBXOxSDNYkn6jGkuMOhxlY8', 'oauth_client_id'=>'2c79cbe25ab1417abbc5acd4ef000af5'],
];

$goods = [
	11679=>['cabinet'=>4919, 'basic'=>'Z3N2YzpzZGNmc2Rj', 'token'=>'054A8EF8-AC89-4114-B4B4-381B31400221'],
//	11679=>['cabinet'=>5342, 'basic'=>'Z3N2YzpzZGNmc2Rj', 'token'=>'DBFB8C7E-3770-431E-B339-AF0180DB1E4A'],
];

$config['ozon'] = [
	11593=>['client'=>36677, 'api'=>'6873ab42-ce32-49db-9fd3-d4e0d80adf5d'],
	15874=>['client'=>74611, 'api'=>'3f1ee40f-36cd-446c-a6d2-23db7da814c2'],
	16096=>['client'=>43484, 'api'=>'bc93d7f4-9ae4-4e8d-90d9-f54b4899d1b1'],
	16097=>['client'=>44609, 'api'=>'d1fdcdb4-8ea8-4add-804a-a20608161105'],
	16098=>['client'=>40472, 'api'=>'4ffe0dee-09aa-41a8-bf57-59ec29205671'],
	17824=>['client'=>177112, 'api'=>'6d662b7e-c9c0-410b-96de-a753ae18665a'],
	19124=>['client'=>377910, 'api'=>'eef11444-8f6b-427c-b281-e51f963e5fc6'],
];

$config['wildberries'] = [
	16838=>[
		'name'=>'WILDBERRIES Маркет',
		'token'=>'96e3a4a04e3d80791a421e4b45264d93a96a20df0f9417ab04c80caaeb0f8b6c',
		'storeId'=>6049,
//		'storeId'=>61062,
		'user'=>16838,
//		'authorization'=>'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY2Nlc3NJRCI6ImMyMDAyMDkyLTA3M2MtNDhjYS1hZGM5LWYzYmZmMGNhYzk1MiJ9.2su8COXydP6JyEN4O1X5KDH4YjsLpxsFQAX9AlyBch0',

		'authorization'=>'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY2Nlc3NJRCI6IjYwMTZhYzZlLTAxMjUtNGM1Ny04Mzc5LTc4NzNjYmQ1MzlkOCJ9.sPhZEtkthtHykKnEJD3CSsa7Icqi_eJIII1u1cVvDcU',
	],

	19773=>[
		'name'=>'WILDBERRIES Маркет RX',
		'token'=>'96e3a4a04e3d80791a421e4b45264d93a96a20df0f9417ab04c80caaeb0f8b6c',
		'storeId'=>242899,
		'user'=>19773,
		'authorization'=>'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhY2Nlc3NJRCI6ImVmOGI1M2Y4LTljMTktNDkwZC04MThmLWExNDk4NmIzNDQ1NSJ9.keIIWmz7ibj-0t8dN4u1lqaOmMJaQIeIkTIYD41_rfA',
	],
];

/*
ID: 2c79cbe25ab1417abbc5acd4ef000af5
Пароль: 79d8378e9c3f49d69ddf0d6f6f39119b
Callback URL: https://muzmart.com/oauth/13060
Время жизни токена: Не менее, чем 1 год
Дата создания: 27.01.2021
*/