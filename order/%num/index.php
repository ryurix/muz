<?

$select = 'SELECT orst.i i'
.',orst.dt dt'
.',orst.last last'
.',orst.user user'
.',orst.staff staff'
.',orst.state state'
.',orst.cire cire'
.',orst.city city'
.',orst.lat lat'
.',orst.lon lon'
.',orst.adres adres'
.',orst.dost dost'
.',orst.vendor vendor'
.',orst.store store'
.',orst.name name'
.',orst.price price'
.',orst.sale sale'
.',orst.count count'
.',orst.info info'
.',orst.note note'
.',orst.docs docs'
.',orst.files files'
.',orst.mark mark'
.',orst.money0 money0'
.',orst.kkm kkm'
.',orst.pay pay'
.',orst.money money'
.',orst.kkm2 kkm2'
.',orst.pay2 pay2'
.',orst.money2 money2'
.',orst.mpi mpi'
.',orst.mpdt mpdt'
.',user.name username'
.' FROM orst LEFT JOIN user ON orst.user=user.i'
.' WHERE orst.i="'.$config['args'][0].'"';
if (is_user('ones')) {
	$select.= ' AND orst.staff='.$_SESSION['i'];
}
$q = db_query($select);
$root = '/order';

//alert($select);

if ($row = db_fetch($q)) {
	w('ft');
	$config['name'] = 'Заказ №'.$row['i'].' от '.ft($row['dt'], 1).' &mdash; '.ft($row['last'], 1);

	$sync = db_fetch_all('SELECT * FROM sync WHERE store='.$row['store'].' ORDER BY count DESC', 'vendor');
	$config['sync'] = $sync;

	$action = count($config['args']) > 1 ? $config['args'][1] : 'edit';

	if ($action == 'edit') {
		$vendor_name = cache_load('vendor');
		$vendors = [45=>$vendor_name[45]];
		foreach ($sync as $k=>$v) {
			if (isset($vendor_name[$k])) {
				$vendors[$k] = kv($vendor_name, $k).' &nbsp; &nbsp; &nbsp; '.$v['price'].($v['opt'] ? '/'.$v['opt'] : '').' '.$v['count'];
			}
		}

		$plan = w('plan-order');
		$plan['vendor']['values'] = $vendors;
//		$row['sale'] = '<a href="/sale/'.$row['sale'].'">'.$row['sale'].'</a>';
		if (!$row['staff']) { $row['staff'] = $_SESSION['i']; }
		$row['mark'] = explode(',', trim($row['mark'], ','));
		$row['mp'] = in_array(MARKETPLACE_MARK, $row['mark']);
		$plan['']['default'] = $row;

		if ($row['state'] == 0) {
			$plan['state']['readonly'] = 1;
		}

		$path = '/files/order/'.$row['i'].'/';
		$plan['']['path'] = $path;
		$plan['docs']['path'] = $path;
		$plan['files']['path'] = $path;

		if ($row['state'] < 35) { // Отменённые заказы в любом случае можно редактировать
			if ($row['kkm']) { $plan['pay']['readonly'] = 1; $plan['money']['readonly'] = 1; }
			if ($row['kkm2']) { $plan['pay2']['readonly'] = 1; $plan['money2']['readonly'] = 1; }
		}
		w('request', $plan);

		if ($plan['send']['value']) {
			w('invalid', $plan);
		}

		if ($plan['']['valid'] && ($plan['send']['value'] == 1 || $plan['send']['value'] == 2)) {
			if ($plan['money']['value'] && !$plan['pay']['value']) {
				$plan['']['valid'] = 0;
				$plan['pay']['iv'] = 1;
			}
			if ($plan['money2']['value'] && !$plan['pay2']['value']) {
				$plan['']['valid'] = 0;
				$plan['pay2']['iv'] = 1;
			}
		}

		if ($plan['last']['value'] < $row['last']) {
			alert('Заказ изменён другим пользователем! Сохранение невозможно!', 'danger');
			$plan['']['valid'] = 0;
		}

		if ($row['vendor'] != $plan['vendor']['value'] && $plan['vendor']['value'] != 45) {
			if ($plan['count']['value'] > kv($sync, $plan['vendor']['value'], ['count'=>0])['count']) {
				alert('Недостаточно товаров у поставщика!', 'danger');
				$plan['']['valid'] = 0;
				$plan['vendor']['iv'] = 1;
			}
		}

		if ($plan['']['valid'] && ($plan['send']['value'] == 1 || $plan['send']['value'] == 2 || $plan['send']['value'] == 4)) {
			w('comment');
			$changes = changes($plan,
				array('state', 'staff', 'adres', 'city', 'cire', 'dost', 'vendor', 'price', 'sale', 'count', 'info', 'note', 'money0', 'money', 'pay', 'money2', 'pay2', 'docs', 'files', 'mark', 'mpi', 'mpdt')
			);

			if (is_null($changes) && kv($plan['city'], 'lat', 0) && kv($plan['city'], 'lon', 0)) {
				$changes = 'добавлены координаты';
			}

			if (($plan['money']['value'] + $plan['money2']['value']) > ($plan['price']['value'] * $plan['count']['value'])) {
				$changes = null;
				alert('Сумма к оплате превышает сумму заказа!', 'danger');
			}

			if (!is_null($changes)) {
				$type = $row['state'] == $plan['state']['value'] ? -1 : $plan['state']['value'];
				comment_type('o'.$row['i'], $type, $changes);
				$data = array(
					'last'=>$row['state'] < 30 || $plan['state']['value'] < 30 ? now() : $row['last'],
					'staff'=>$plan['staff']['value'],
					'state'=>$plan['state']['value'],
					'cire'=>$plan['cire']['value'],
					'city'=>$plan['city']['value'],
					'adres'=>$plan['adres']['value'],
					'dost'=>$plan['dost']['value'],
					'vendor'=>$plan['vendor']['value'],
					'price'=>$plan['price']['value'],
					'sale'=>$plan['sale']['value'],
					'count'=>$plan['count']['value'],
					'info'=>$plan['info']['value'],
					'note'=>$plan['note']['value'],
					'money0'=>$plan['money0']['value'],
					'pay'=>$plan['pay']['value'],
					'money'=>$plan['money']['value'],
					'pay2'=>$plan['pay2']['value'],
					'money2'=>$plan['money2']['value'],
					'docs'=>$plan['docs']['value'],
					'files'=>$plan['files']['value'],
					'mark'=>count($plan['mark']['value']) ? ','.implode(',', $plan['mark']['value']).',' : '',
					'mpi'=>$plan['mpi']['value'],
					'mpdt'=>$plan['mpdt']['value'],
				);
				if (kv($plan['city'], 'lat', 0) && kv($plan['city'], 'lon', 0)) {
					$data['lat'] = $plan['city']['lat'];
					$data['lon'] = $plan['city']['lon'];
				}
				db_update('orst', $data, array('i'=>$row['i']));
				$plan['last']['value'] = now();
				alert('<a href="/order/'.$row['i'].'">Заказ</a> изменён: '.$changes);

				if ((($plan['send']['value'] == 1 || $plan['send']['value'] == 4) && $row['state'] != $plan['state']['value']) || $plan['dost']['value'] != $row['dost']) {
					w('mail');
					mail_order($row['user'], $row['i']);
				}

				$dummy = [
					'orst'=>$row['i'],
					'old'=>$row['state'],
					'new'=>$plan['state']['value'],
					'vendor'=>$plan['vendor']['value'],
					'store'=>$row['store'],
					'count'=>$plan['count']['value'],
					'name'=>$row['name'],
					'user'=>$row['user'],
					'mpi'=>$plan['mpi']['value'],
				];
				w('order-update-state', $dummy);

				// Фискализация
				$kkm = false;
				if (!$row['kkm'] && $row['money'] != $plan['money']['value'] && $plan['money']['value'] && $plan['pay']['value'] == 3) { // Банковская карта (Терминал)
					$kkm = true;
				}

				if (!$row['kkm2'] && $row['money2'] != $plan['money2']['value'] && $plan['money2']['value'] && $plan['pay2']['value'] == 3) { // Банковская карта (Терминал)
					$kkm = true;
				}

				if ($kkm && ($plan['money']['value'] + $plan['money2']['value']) < 200000) {
					// Автоматическая фискализация на сумму меньше 200 000
					$q = db_query('SELECT * FROM kkm WHERE state<10 AND usr='.$row['user']);
					if ($old = db_fetch($q)) {
						// Добавляем к чеку
						db_update('kkm', array(
							'orst'=>$old['orst'].$row['i'].'|',
							'dt'=>now(),
						), array(
							'i='.$old['i'],
						));
						alert('Добавлено в <a href="/kkm/'.$old['i'].'">фискальный чек</a>');
					} else {
						// Создаём новый чек
						db_insert('kkm', array(
							'dt'=>now(),
							'state'=>0,
							'usr'=>$row['user'],
							'staff'=>$_SESSION['i'],
							'orst'=>'|'.$row['i'].'|',
						));
						alert('Создан <a href="/kkm">фискальный чек</a>');
					}
				}
			}

			if ($plan['send']['value'] == 4) {
				redirect($root);
			}
		} elseif ($plan['']['valid'] && $plan['send']['value'] == 3) {
			db_delete('orst', array(
				'i'=>$row['i']
			));
			alert('Заказ удален.');

			w('log');
			logs(39, $row['i']);
			redirect($root);

			db_delete('bill', array(
				'orst'=>$row['i'],
				'state<10',
			));
		}
	}

	if ($action == 'link') {
		$config['row'] = $row;
		rebody('link');
	}

	$plan['name']['value'] = '<a href="/store/'.$row['store'].'">'.$row['name'].'</a>';
	$name = trim($row['username']);
	$plan['user']['value'] = '<a href="/user/'.$row['user'].'">'.(strlen($name) < 3 ? $row['user'].' '.$name : $name).'</a>';
	$config['plan'] = $plan;
	$config['row'] = $row;
} else {
	redirect($root);
}