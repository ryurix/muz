<?

$config['CashierName'] = 'Кокшаров А.С.';
$config['CashierVATIN'] = "720319339047"; // ИНН продавца тег ОФД 1203

function kkm($data, $debug = false) {
	global $config;
	$kkmserver = parse_url($config['kkmserver']);

	$url = $kkmserver['scheme'].'://'.$kkmserver['host'].'/Execute/sync';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_PORT, $kkmserver['port']);

	$headers = array(
		'Content-Type:application/json; charset=UTF-8',
		'Authorization: Basic '. base64_encode($kkmserver['user'].':'.$kkmserver['pass']),
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	if ($debug) {
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	}

	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$result = curl_exec($ch);

	if ($debug) {
		$info = curl_getinfo($ch);
	}

	curl_close($ch);

	if ($debug) {
		print_pre($info);
		print_pre($result);
	}

	$result = json_decode($result, true);

	return $result;
}

function kkm_fix($id = 0) {
	global $config;

	$where = array('kkm.state<10');
	if ($id) { $where[] = 'kkm.i='.$id; }
	else { $where[] = 'kkm.dt<'.(\Config::now() - 3*60); }
	$q = db_query('SELECT kkm.*,user.email,user.phone FROM kkm LEFT JOIN user ON kkm.usr=user.i WHERE '.implode(' AND ', $where).' ORDER BY kkm.i LIMIT 1');
	$kkm = db_fetch($q);
	if (!$kkm) { return 0; }

	$orst = trim($kkm['orst'], '|');
	if (!strlen($orst)) { $orst = '0'; }
	$orst = explode('|', $orst);
	$q = db_query('SELECT * FROM orst WHERE i IN ('.implode(',', $orst).')');
	$orst = array();

	$rows = array();

	$pred = true;
	$cash = 0;
	$card = 0;
	$total = 0;
	$max = 0;
	while ($i = db_fetch($q)) {
		$skip = true;

		$money = 0;

		if (!$i['kkm2'] && $i['money2']) {
			$skip = false;
			$pred = false;

			$money+= $i['money2'];

			if ($i['pay2'] == 1) {
				$cash+= $i['money2'];
			} else {
				$card+= $i['money2'];
			}
		}

		if (!$i['kkm'] && $i['money']) {
			$skip = false;

			$money+= $i['money'];

			if ($i['pay'] == 1) {
				$cash+= $i['money'];
			} else {
				$card+= $i['money'];
			}
		}

		// Если фискализируется сумма больше чем цена * количество -- пропускаем
		if ($skip || ($money > ($i['price'] * $i['count']))) { continue; }

		$total+= $money;
/*
		$rows[] = array(
			'i'=>$i['i'],
			'name'=>$i['name'],
			'price'=>$i['price'],
			'count'=>$i['count'],
			'money'=>$money,
		);
*/
		$rows[] = array(
			'Register'=>array(
				'Name'=>$i['name'],
				'Quantity'=>$i['count'],
				'Price'=>$i['price'],
				'Amount'=>$money,

			//	'Department'=>1,
				'Tax'=>20,

				'SignMethodCalculation'=>4,
				'SignCalculationObject'=>1,
			),
		);

		$orst[] = $i['i'];
	}


	if (!count($orst)) {
		db_update('kkm', array('state'=>15, 'dt2'=>\Config::now()), array('i'=>$kkm['i']));
		db_insert('log', array(
			'typ'=>95,
			'dt'=>\Config::now(),
			'usr'=>$_SESSION['i'],
			'info'=>'Не найден заказ для фискализации: '.$kkm['orst'],
			'code'=>$kkm['i'],
		));
		return 0;
	}

	$print = $kkm['state'] == 5;

	$data = array(
		'Command'=>'RegisterCheck', // Команда серверу

		// Номер устройства. Если 0 то первое не блокированное на сервере
		'NumDevice'=>0,
		// ИНН ККМ для поиска. Если "" то ККМ ищется только по NumDevice,
		// Если NumDevice = 0 а InnKkm заполнено то ККМ ищется только по InnKkm
		'InnKkm'=>'',
		// Заводской номер ККМ для поиска. Если "" то ККМ ищется только по NumDevice,
		'KktNumber'=>'',

		// Время (сек) ожидания выполнения команды.
		//Если За это время команда не выполнилась в статусе вернется результат "NotRun" или "Run"
		//Проверить результат еще не выполненной команды можно командой "GetRezult"
		//Если не указано или 0 - то значение по умолчанию 60 сек.
		// Поле не обязательно. Это поле можно указывать во всех командах
		'Timeout'=>30,
		// Уникальный идентификатор команды. Любая строка из 40 символов - должна быть уникальна для каждой подаваемой команды
		// По этому идентификатору можно запросить результат выполнения команды
		// Поле не обязательно
		'IdCommand'=>$kkm['i'],
		// Это фискальный или не фискальный чек
		'IsFiscalCheck'=>true,
		// Тип чека;
		// 0 – продажа;                             10 – покупка;
		// 1 – возврат продажи;                     11 - возврат покупки;
		// 8 - продажа только по ЕГАИС (обычный чек ККМ не печатается)
		// 9 - возврат продажи только по ЕГАИС (обычный чек ККМ не печатается)
		'TypeCheck'=>0,
		// Не печатать чек на бумагу
		'NotPrint'=> $print ? 'false' : 'true', //true,
		// Количество копий документа
		'NumberCopies'=>0,
		// Продавец, тег ОФД 1021
		'CashierName'=>$config['CashierName'], // $kkm['name'],
		// ИНН продавца тег ОФД 1203
		'CashierVATIN'=>$config['CashierVATIN'],
		// Телефон или е-Майл покупателя, тег ОФД 1008
		// Если чек не печатается (NotPrint = true) то указывать обязательно
		// Формат: Телефон +{Ц} Email {С}@{C}
		'ClientAddress'=>strlen($kkm['phone']) ? '+'.$kkm['phone'] : $kkm['email'],
		// Aдрес электронной почты отправителя чека тег ОФД 1117 (если задан при регистрации можно не указывать)
		// Формат: Email {С}@{C}
	//	'SenderEmail'=>"sochi@mama.com",
		// Система налогообложения (СНО) применяемая для чека
		// Если не указанно - система СНО настроенная в ККМ по умолчанию
		// 0: Общая ОСН
		// 1: Упрощенная УСН (Доход)
		// 2: Упрощенная УСН (Доход минус Расход)
		// 3: Единый налог на вмененный доход ЕНВД
		// 4: Единый сельскохозяйственный налог ЕСН
		// 5: Патентная система налогообложения
		// Комбинация разных СНО не возможна
		// Надо указывать если ККМ настроена на несколько систем СНО
		'TaxVariant'=>"",
		// Дополнительные произвольные реквизиты (не обязательно) пока только 1 строка
	/*
		'AdditionalProps'=>array(
			//{ Print: true, PrintInHeader: false, NameProp: "Номер транзакции", Prop: "234/154" },
			//{ Print: true, PrintInHeader: false, NameProp: "Дата транзакции", Prop: "10.11.2016 10:30" },
			array('Print'=>true, 'PrintInHeader'=>false, 'NameProp'=>'Дата транзакции', 'Prop'=>'10.11.2016 10:30'),
		),
	*/
		//ClientId: "557582273e4edc1c6f315efe",
		// Это только для тестов: Получение ключа суб-лицензии : ВНИМАНИЕ: ключ суб-лицензии вы должны генерить у себя на сервере!!!!
		//KeySubLicensing: GetKeySubLicensing("client@server.ru", "12qw12"),
		// КПП организации, нужно только для ЕГАИС
		//KPP: "782543005",

		// Строки чека
		'CheckStrings'=>array(
	/*
			array(
				'Register'=>array(
					// Наименование товара 64 символа
					'Name'=>"Шаровары мужские красные: НИМБУС-2000",
					// Количество товара (3 знака после запятой)
					'Quantity'=>3,
					// Цена за шт. без скидки (2 знака после запятой)
					'Price'=>100,
					// Конечная сумма строки с учетом всех скидок/наценок; (2 знака после запятой)
					'Amount'=>0.00,
					// Отдел, по которому ведется продажа (2 знака после запятой)
					'Department'=>1,
					// НДС в процентах или ТЕГ НДС: 0 (НДС 0%), 10 (НДС 10%), 18 (НДС 18%), -1 (НДС не облагается), 118 (НДС 18/118), 110 (НДС 10/110)
					'Tax'=>-1,
					//Штрих-код EAN13 для передачи в ОФД (не печатется)
					'EAN13'=>"1254789547853",
					// Признак способа расчета. тег ОФД 1214. Для ФФД.1.05 и выше обязательное поле
					// 1: "ПРЕДОПЛАТА 100% (Полная предварительная оплата до момента передачи предмета расчета)"
					// 2: "ПРЕДОПЛАТА (Частичная предварительная оплата до момента передачи предмета расчета)"
					// 3: "АВАНС"
					// 4: "ПОЛНЫЙ РАСЧЕТ (Полная оплата, в том числе с учетом аванса в момент передачи предмета расчета)"
					// 5: "ЧАСТИЧНЫЙ РАСЧЕТ И КРЕДИТ (Частичная оплата предмета расчета в момент его передачи с последующей оплатой в кредит )"
					// 6: "ПЕРЕДАЧА В КРЕДИТ (Передача предмета расчета без его оплаты в момент его передачи с последующей оплатой в кредит)"
					// 7: "ОПЛАТА КРЕДИТА (Оплата предмета расчета после его передачи с оплатой в кредит )"
					SignMethodCalculation: 4,
					// Признак предмета расчета. тег ОФД 1212. Для ФФД.1.1 и выше обязательное поле
					// 1: "ТОВАР (наименование и иные сведения, описывающие товар)"
					// 2: "ПОДАКЦИЗНЫЙ ТОВАР (наименование и иные сведения, описывающие товар)"
					// 3: "РАБОТА (наименование и иные сведения, описывающие работу)"
					// 4: "УСЛУГА (наименование и иные сведения, описывающие услугу)"
					// 5: "СТАВКА АЗАРТНОЙ ИГРЫ (при осуществлении деятельности по проведению азартных игр)"
					// 6: "ВЫИГРЫШ АЗАРТНОЙ ИГРЫ (при осуществлении деятельности по проведению азартных игр)"
					// 7: "ЛОТЕРЕЙНЫЙ БИЛЕТ (при осуществлении деятельности по проведению лотерей)"
					// 8: "ВЫИГРЫШ ЛОТЕРЕИ (при осуществлении деятельности по проведению лотерей)"
					// 9: "ПРЕДОСТАВЛЕНИЕ РИД (предоставлении прав на использование результатов интеллектуальной деятельности или средств индивидуализации)"
					// 10: "ПЛАТЕЖ (аванс, задаток, предоплата, кредит, взнос в счет оплаты, пени, штраф, вознаграждение, бонус и иной аналогичный предмет расчета)"
					// 11: "АГЕНТСКОЕ ВОЗНАГРАЖДЕНИЕ (вознаграждение (банковского)платежного агента/субагента, комиссионера, поверенного или иным агентом)"
					// 12: "СОСТАВНОЙ ПРЕДМЕТ РАСЧЕТА (предмет расчета, состоящем из предметов, каждому из которых может быть присвоено вышестоящее значение"
					// 13: "ИНОЙ ПРЕДМЕТ РАСЧЕТА (предмет расчета, не относящемуся к предметам расчета, которым может быть присвоено вышестоящее значение"
					SignCalculationObject: 1,

					// Единица измерения предмета расчета. Можно не указывать
					'MeasurementUnit'=>"шт",
					// Код товарной номенклатуры Тег ОФД 1162 (Новый классификатор товаров и услуг. Пока не утвержден налоговой. Пока не указывать)
					// 4 символа – код справочника; последующие 8 символов – код группы товаров; последние 20 символов – код идентификации товара
					'NomenclatureCode'=>"",
					// Данные для ЕГАИС системы, можно не указывать
				),
			),
	*/
		),

		// Наличная оплата (2 знака после запятой)
		'Cash'=>$cash,
		// Сумма электронной оплаты (2 знака после запятой)
		'ElectronicPayment'=>$card,
		// Сумма из предоплаты (зачетом аванса) (2 знака после запятой)
		'AdvancePayment'=>0,
		// Сумма постоплатой(в кредит) (2 знака после запятой)
		'Credit'=>0,
		// Сумма оплаты встречным предоставлением (сертификаты, др. мат.ценности) (2 знака после запятой)
		'CashProvision'=>0,
	);

	$data['CheckStrings'] = $rows;

/*
	foreach ($rows as $i) {
		$data['CheckStrings'][] = array(
			'Register'=>array(
				'Name'=>$i['name'],
				'Quantity'=>$i['count'],
				'Price'=>$i['price'],
				'Amount'=>$i['money'],

			//	'Department'=>1,
				'Tax'=>-1,

				'SignMethodCalculation'=>4,
				'SignCalculationObject'=>1,
			),
		);
	}
*/
	$back = kkm($data);
//	w('log');
//	logs(97, $kkm['i'], php_encode($back));
/*
$back = array(
	'RegisterCheck' => array(
		'FiscalNumber' => '681',
		'FiscalDate' => '2018-12-05T14:36:00',
		'CheckType' => 'Приход',
		'FiscalSign' => 2249046634,
		'CashierName' => 'Степанцов Олег',
		'CashierVATIN' => '',
		'TaxVariant' => 2,
		'ClientAddress' => '',
		'Register' => array(
			'0' => array(
				'Name' => 'Meinl NINO45Y Бубен (ручной барабан) 8"',
				'Quantity' => 1,
				'Amount' => 1550,
				'Tax' => -1,
			),

		),
		'Cash' => 0,
		'ElectronicPayment' => 1550,
		'AdvancePayment' => 0,
		'Credit' => 0,
		'CashProvision' => 0,
		'AllSumm' => 1550,
	),

	'URL' => 'https://cash.kontur.ru/CashReceipt/View/FN/8711000101647261/FD/681/FP/2249046634',
	'QRCode' => 't=20181205T143600&s=1550.00&fn=8711000101647261&i=681&fp=2249046634&n=1',
	'Command' => 'GetDataCheck',
	'Error' => '',
	'Status' => 0,
	'IdCommand' => '',
	'NumDevice' => 1,
);
//*/

	if (strlen($back['Error'])) {
		db_insert('log', array(
			'typ'=>95,
			'dt'=>\Config::now(),
			'usr'=>$_SESSION['i'],
			'info'=>$back['Error'].' '.php_encode($data).' '.php_encode($back),
			'code'=>$kkm['i'],
		));

		db_update('kkm', array('state'=>15, 'dt2'=>\Config::now()), array('i'=>$kkm['i']));
		return 0;
	}

	if (!is_array($back) || !count($back)) {
		db_insert('log', array(
			'typ'=>95,
			'dt'=>\Config::now(),
			'usr'=>$_SESSION['i'],
			'info'=>'Пустой ответ сервера: '.php_encode($data).' '.php_encode($back),
			'code'=>$kkm['i'],
		));

		db_update('kkm', array('state'=>15, 'dt2'=>\Config::now()), array('i'=>$kkm['i']));
		return 0;
	}

	$back = kkm(array(
		'Command'=>'GetDataCheck',
		'NumDevice'=>0,
		'FiscalNumber'=>$back['CheckNumber'],
	));

//	if (!isset($back['URL']) || !isset($back['QRCode'])) {
//		w('log');
//		logs(95, $kkm['i'], 'Не найдено URL, QRCode: '.php_encode($back));
//	}


	$data = array(
		'URL'=>$back['URL'] ?? '',
		'QRCode'=>$back['QRCode'] ?? '',
	);
	foreach ($back['RegisterCheck'] as $k=>$v) {
		if (!is_array($v)) {
			$data[$k]=$v;
		}
	}
	foreach ($back['RegisterCheck']['Register'] as $i) {
		foreach ($i as $k=>$v) {
			if (isset($data[$k])) {
				$data[$k][] = $v;
			} else {
				$data[$k] = array($v);
			}
		}
	}

	$data['#'] = array();
	$data['Price'] = array();
	foreach ($data['Quantity'] as $k=>$v) {
		$data['#'][] = count($data['#']) + 1;
		$data['Price'][] = $data['Amount'][$k] / $v;
	}

	db_update('kkm', array(
		'orst'=>'|'.implode('|', $orst).'|',
		'state'=>10,
		'dt2'=>\Config::now(),
		'data'=>php_encode($data),
		'total'=>$total,
	), array('i'=>$kkm['i']));

	db_update('orst', array(
		'kkm'=>$kkm['i'],
	), array(
		'i IN ('.implode(',', $orst).')',
		'kkm=0',
		'pay>0',
		'money>0',
	));

	db_update('orst', array(
		'kkm2'=>$kkm['i'],
	), array(
		'i IN ('.implode(',', $orst).')',
		'kkm2=0',
		'pay2>0',
		'money2>0',
	));

	return 1;
}

// Открытие/закрытие сессии
//*
w('ft');
$now = ft(\Config::now());
$dt = cache_get('kkm-open');

if ($dt != $now) {
	cache_set('kkm-open', $now);

	// Закрываем сессию
	kkm(array(
		'Command'=>'CloseShift',
		'NumDevice'=>0,
		'IdDevice'=>'',
		'CashierName'=>$config['CashierName'],
		'CashierVATIN'=>$config['CashierVATIN'], // ИНН продавца тег ОФД 1203
		'NotPrint'=>true,
	));

	sleep(1);

	// Открываем сессию
	kkm(array(
		'Command'=>'OpenShift',
		'NumDevice'=>0,
		'IdDevice'=>'',
		'CashierName'=>$config['CashierName'],
		'CashierVATIN'=>$config['CashierVATIN'], // ИНН продавца тег ОФД 1203
		'NotPrint'=>true, // Не печатать чек на бумагу
	));

	sleep(3);
}
//*/

?>