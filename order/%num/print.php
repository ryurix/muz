<?

$select = 'SELECT orst.i i'
.',orst.dt dt'
.',orst.last last'
.',orst.user user'
.',orst.staff staff'
.',orst.state state'
.',orst.city city'
.',orst.adres adres'
.',orst.store store'
.',orst.name name'
.',orst.price price'
.',orst.sale sale'
.',orst.count count'
.',orst.info info'
.',orst.docs docs'
.',orst.files files'
.',orst.money0 money0'
.',orst.money money'
.',orst.pay pay'
.',orst.money2 money2'
.',orst.pay2 pay2'
.',orst.dost dost'
.',orst.vendor vendor'
.',user.name uname'
.',user.cire cire'
.',user.pay upay'
.',user.ul ul'
.',user.phone phone'
.',user.email email'
.',user.login login'
.',s.name sname'
.' FROM orst'
.' LEFT JOIN user ON orst.user=user.i'
.' LEFT JOIN user s ON orst.staff=s.i'
.' WHERE orst.i="'.$config['args'][0].'"';

$q = db_query($select);
$root = '/order';

//alert($select);

if ($row = db_fetch($q)) {
	w('ft');
	$config['name'] = 'Заказ №'.$row['i'].' от '.ft($row['dt'], 1).' &mdash; '.ft($row['last'], 1);
	$config['row'] = $row;

	$docs = w('list-doc');
	$docs[90] = 'фискализация';
	$docs[95] = 'печать с фиск.';
	$docs[101] = 'Uniteller аванс';
	$docs[100] = 'Uniteller оплата';
	$docs[201] = 'Appex аванс';
	$docs[200] = 'Appex оплата';
	$type = 0;
	foreach($docs as $k=>$v) {
		if (isset($_REQUEST['doc'.$k])) {
			$type = $k;
			break;
		}
	}

	// Массив выбранных заказов
	$ids = isset($_REQUEST['id']) && is_array($_REQUEST['id']) ? $_REQUEST['id'] : array();
	// Если не выбраны заказы или не определён тип -- возвращаемся.
	if (!count($ids) || !$type) {
		return;
	}

	// Создание документа	
	if ($type < 80) {
		$select = 'SELECT * FROM orst WHERE i in ('.implode(',', $ids).')';
		$q = db_query($select);

		w('keys');
		$key = key_next('doc'.$type);
		$dt = now();
		$city = cache_load('city');

		$data = array(
			'num'=>$key,
			'dt'=>$dt,
			'user'=>$row['uname'],
			'staff'=>$row['staff'] ? $row['sname'] : $_SESSION['name'],
			'ul'=>$row['ul'],
			'upay'=>$row['upay'],
			'city'=>kv($city, $row['cire']),
			'adres'=>trim($row['city'].' '.$row['adres']),
			'adres!'=>'',
			'phone'=>$row['phone'],
			'email'=>$row['email'],
			'login'=>$row['login'],

			'#'=>array(),
			'names'=>array(),
			'count'=>array(),
			'price'=>array(),
			'summa'=>array(),
			'info'=>array(),
			'money'=>array(),
			'money2'=>array(),
			'dost'=>array(),
		);

		$no = 0;
		$total = 0;
		$amount = 0;

		$orst = array();
		$store = array();

		$checkadres = false;

		$money0 = 0; // оплаченная доставка
		$money1 = 0; // всего доставка
		while ($i = db_fetch($q)) {
			$no++;
			$orst[] = $i['i'];
			$store[] = $i['store'];
			$data['#'][] = $no;
			$data['pay'][] = $i['pay'];
			$data['pay2'][] = $i['pay2'];
			$data['names'][] = $i['name'];
			$data['count'][] = $i['count'];
			$data['price'][] = $i['price'];
			$data['summa'][] = $i['count'] * $i['price'];
			$data['info'][] = $i['info'];
			$data['note'][] = $i['note'];
			$data['money'][] = $i['money'];
			$data['money2'][] = $i['money2'];
			$data['dost'][] = $i['dost'];
			$data['vendor'][] = $i['vendor'];

			if ($i['money0'] < 0) {
				$money1-= $i['money0'];
			} else {
				$money0+= $i['money0'];
				$money1+= $i['money0'];
			}

			$total+= $i['count'] * $i['price'];
			$amount+= $i['count'];

			if ($i['cire'] != $row['cire']
			|| $i['city'] != $row['city']
			|| $i['adres'] != $row['adres']) {
				$data['adres!'] = ' ПРОВЕРИТЬ АДРЕС!';
			}
		}
		$data['adres'].= $data['adres!'];

		if ($money1 > 0) {
			$data['#'][] = $no + 1;
			$data['pay'][] = 0;
			$data['pay2'][] = 0;
			$data['names'][] = 'Доставка';
			$data['count'][] = 1;
			$data['price'][] = $money1;
			$data['summa'][] = $money1;
			$data['info'][] = '';
			$data['money'][] = 0;
			$data['money2'][] = $money0;
//			$data['dost'][] = '';
//			$data['vendor'][] = '';

			$total+= $money1;
			$amount+= 1;
		}
		$data['total'] = $total;
		$data['amount'] = $amount;
		$data['store'] = $store;

		$name = $docs[$type].' №'.$key.' от '.ft($dt);

		if (count($store) && $type < 100) {
			if ($type == 5 || $type == 15 || $type == 6 || $type == 16) {
				$q = db_query('SELECT * FROM orst WHERE i IN ('.implode(',', $orst).')');
				while ($i = db_fetch($q)) {
					if ($i['state'] == 1) {
						db_query('UPDATE orst SET state=3, last='.now().' WHERE i='.$i['i']);
						db_query('UPDATE sync SET count=count-'.$i['count']
							.' WHERE vendor='.$i['vendor'].' AND store='.$i['store']);
						db_query('UPDATE store SET count=count-'.$i['count']
							.' WHERE vendor='.$i['vendor'].' AND i='.$i['store']);
						alert('Количество товара '.$i['name'].' на складе уменьшено на '.$i['count']);
					}
				}
			}

			db_insert('docs', array(
				'orst'=>'|'.implode('|', $orst).'|',
				'staff'=>$_SESSION['i'],
				'store'=>'|'.implode('|', $store).'|',
				'user'=>$row['user'],
				'type'=>$type,
				'num'=>$key,
				'dt'=>$dt,
				'name'=>$name,
				'total'=>$total,
				'data'=>php_encode($data),
			));

			$id = db_insert_id();

			alert('Документ сформирован: <a href="/doc/'.$id.'" target="_BLANK">'.$name.'</a>');
		}
	}

	// Создание чека ККМ
	if ($type>=90 && $type<100) {
		/*
		$select = 'SELECT * FROM orst WHERE i in ('.implode(',', $ids).')';
		$q = db_query($select);

		$rows = array();

		$user = 0;
		$orst = array();
		$total = 0;
		while ($i = db_fetch($q)) {
			$user = $i['user'];
			$orst[] = $i['i'];
			$summa = $i['money2'];
			$total+= $summa;

			$rows[] = array(
				'i'=>$i['i'],
				'name'=>$i['name'],
				'price'=>$i['price'],
				'count'=>$i['count'],
				'money'=>$i['money2'],
			);
		}
		*/

		db_insert('kkm', array(
		//	'rows'=>php_encode($rows),
			'total'=>0,
			'usr'=>$row['user'],
			'staff'=>$_SESSION['i'],
			'dt'=>now(),
			'state'=>$type >= 95 ? 5 : 0,
			'orst'=>'|'.implode('|', $ids).'|',
		));
	}

	// Создание счета онлайн оплаты
	if ($type >= 100) {
		$select = 'SELECT * FROM orst WHERE i in ('.implode(',', $ids).')';
		$q = db_query($select);

		$user = 0;
		$orst = array();
		$total = 0;
		while ($i = db_fetch($q)) {
			$user = $i['user'];
			$orst[] = $i['i'];
			$summa = $i['count']*$i['price'];
			$total+= $summa - $i['money'] - $i['money2'];
		}

		if ($type == 101 || $type == 201) {
			$total = floor($total / 2);
		}

		db_insert('bill', array(
			'type'=>$type,
			'orst'=>'|'.implode('|', $orst).'|',
			'user'=>$user,
			'staff'=>$_SESSION['i'],
			'dt1'=>now(),
			'dt2'=>0,
			'total'=>$total,
			'state'=>0,
			'status'=>'',
			'info'=>'',
		));

		$q = db_query('SELECT * FROM orst WHERE i IN ('.implode(',', $orst).')');
		while ($i = db_fetch($q)) {
			if ($i['state'] == 1) {
				db_query('UPDATE orst SET state=3, last='.now().' WHERE i='.$i['i']);
				db_query('UPDATE sync SET count=count-'.$i['count']
					.' WHERE vendor='.$i['vendor'].' AND store='.$i['store']);
				db_query('UPDATE store SET count=count-'.$i['count']
					.' WHERE vendor='.$i['vendor'].' AND i='.$i['store']);
				alert('Количество товара '.$i['name'].' на складе уменьшено на '.$i['count']);
			}
		}
	}

} else {
	redirect($root);
}

?>