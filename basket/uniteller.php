<?

w('uniteller');
w('ft');

$plan = array(
	'Signature'=>array('type'=>'line', 'min'=>12),
	'Order_ID'=>array('type'=>'int'),
	'Status'=>array('type'=>'combo', 'values'=>array(
		'authorized'=>'Подписано',
		'paid'=>'Оплачено',
		'canceled'=>'Отменено',
		'waiting'=>'Ожидание',
		)
	),
);


w('request', $plan);

if ($plan['']['valid']) {
	$order = $plan['Order_ID']['value'];
	$status = $plan['Status']['value'];
	if (u_check($order, $status) == $plan['Signature']['value']) {
		if ($status == 'waiting') {
			$q = db_query('SELECT * FROM bill WHERE i='.$order);
			if ($i = db_fetch($q)) {
				db_update('orst', array('state'=>3), array('bill'=>$order));
				db_update('bill', array('state'=>3), array('i'=>$order)); // , 'type'=>1 // ???

				w('comment');
				$orders = explode('|', $i['orst']);
				foreach ($orders as $o) {
					comment_type('o'.$o, 8, 'Ожидание Uniteller');
				}
			}
		}
		if ($status == 'paid' || $status == 'authorized') {
			$q = db_query('SELECT * FROM bill WHERE i='.$order.' AND state<10'); // .' AND type=1' // ???
			if ($row = db_fetch($q)) {
				db_update('bill', array('state'=>10, 'dt2'=>now()), array('i'=>$order));

				$orst = explode('|', trim($row['orst'], '|'));
				$q = db_query('SELECT * FROM orst WHERE i in ('.implode(',', $orst).')');
				$part = array();
				while ($i = db_fetch($q)) {
					$part[$i['i']] = $i['price']*$i['count'] - $i['money'] - $i['money2'];
				}

				$part2 = array();
				$total = $row['total'];
				$summa = array_sum($part);
				if ($summa == $total) {
					$dest = '2';
					$part2 = $part;
				} else {
					$dest = $summa <= $total ? '2' : '';
					$left = $total;
					foreach ($part as $k=>$v) {
						$money = floor($total * ($v/$summa));
						$part2[$k] = $money;
						$left-= $money;
						$last = $k;
					}
					if ($left != 0) { $part2[$last]+= $left; }
				}

				w('comment');
				foreach ($part2 as $k=>$v) {
/*
					db_update('orst', array(
						'last'=>now(),
						'state'=>7,
						'money'.$dest=>$v,
						'pay'.$dest=>7,
					), array('i'=>$k));
*/
					if ($row['state'] < 10) {
						db_update('orst',
							'last='.now()
							.',state=GREATEST(7,state)'
							.',money'.$dest.'='.$v
							.',pay'.$dest.'=7'
						, array('i'=>$k));
					}
					comment_type('o'.$k, 10, ($status == 'paid' ? 'Деньги поступили' : 'Подписано').' Uniteller: '.$v);
				}

				// Фискализация
				if (count($part) && $total < 200000) {
					db_insert('kkm', array(
						'dt'=>now(),
						'state'=>0,
						'usr'=>$row['user'],
						'orst'=>'|'.implode('|', array_keys($part)).'|',
					));
				}
			}
		}
	}
}

$info = array(
	'dt'=>ft(now(), 1),
	'POST'=>$_POST,
	'plan'=>$plan,
);

$infos = cache_load('_uniteller');

if (!is_array($infos)) {
	$infos = array();
}

$infos[] = $info;

cache_save('_uniteller', $infos);

?>