<?

$select = 'SELECT orst.i i'
.',orst.dt dt'
.',orst.last last'
.',orst.user user'
.',orst.staff staff'
.',orst.state state'
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
.',user.city city'
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

	w('clean');
	$ids = isset($_REQUEST['id']) && is_array($_REQUEST['id']) ? $_REQUEST['id'] : array();
	foreach ($ids as $k=>$v) {
		$ids[$k] = clean_09($v);
	}
	$state = isset($_REQUEST['state']) ? clean_09($_REQUEST['state']) : 0;

	if (count($ids) && $state) {
		w('mail');
		w('comment');
		$states = w('order-state');
		$select = 'SELECT orst.*,vendor.name sklad,sync.count scount FROM orst LEFT JOIN vendor ON orst.vendor=vendor.i LEFT JOIN sync ON vendor.i=sync.vendor AND orst.store=sync.store WHERE orst.i IN ('.implode(',', $ids).')';
		$q = db_query($select);
		while ($i = db_fetch($q)) {
			if ($i['state'] != $state) {
				db_update('orst', array('state'=>$state, 'last'=>now()), array('i'=>$i['i']));
				mail_order($i['user'], $i['i']);
				comment_type('o'.$i['i'], $state, ''); // 'Связанные: Состояние ('.$states[$i['state']].')'

				if ($i['state'] == 1 && $state > 1 && $state <= 30) {
					$text = $i['sklad'].' '.$i['scount'].' - '.$i['count'].' = '.($i['scount'] - $i['count']);
					alert('Количество товара на складе '.$text);

					db_query('UPDATE sync SET count=count-'.$i['count']
						.' WHERE vendor='.$i['vendor'].' AND store='.$i['store']);
					db_query('UPDATE store SET count=count-'.$i['count']
						.' WHERE vendor='.$i['vendor'].' AND i='.$i['store']);

					w('log');
					logs(36, $row['i'], $text);
				}

				if ($i['state'] > 1 && $state < 35 && $state == 1) {
					$text = $i['sklad'].' '.$i['scount'].' + '.$i['count'].' = '.($i['scount'] + $i['count']);
					alert('Количество товара на складе '.$text);

					db_query('UPDATE sync SET count=count+'.$i['count']
						.' WHERE vendor='.$i['vendor'].' AND store='.$i['store']);
					db_query('UPDATE store SET count=count+'.$i['count']
						.' WHERE vendor='.$i['vendor'].' AND i='.$i['store']);

					w('log');
					logs(37, $row['i'], $text);
				}

				if ($i['state'] > 1 && $state != 35 && $state == 35) {
					$text = $i['sklad'].' '.$i['scount'].' + '.$i['count'].' = '.($i['scount'] + $i['count']);
					alert('Количество товара на складе '.$text);

					db_query('UPDATE sync SET count=count+'.$i['count']
						.' WHERE vendor='.$i['vendor'].' AND store='.$i['store']);
					db_query('UPDATE store SET count=count+'.$i['count']
						.' WHERE vendor='.$i['vendor'].' AND i='.$i['store']);

					w('log');
					logs(38, $row['i'], $text);

					$q = db_query('SELECT * FROM bill WHERE orst LIKE "%'.$i['i'].'%" AND state<=1');
					while ($bill = db_fetch($q)) {
						$orst = explode('|', $bill['orst']);
						if (in_array($i['i'], $orst)) {
							db_update('bill', array('state'=>5), array('i'=>$bill['i']));
						}
					}
					db_close($q);
				}
			}
		}
		db_close($q);

		$orders = array();
		foreach ($ids as $i) {
			$orders[] = '<a href="/order/'.$i.'">№'.$i.'</a>';
		}
		alert('Состояние документов: '.implode(', ', $orders).' изменено на '.$states[$state]);
	}

	$config['row'] = $row;
} else {
	redirect($root);
}

?>