<?

$order = new \Order\Model(\Page::arg());

if (\User::is('ones') && $order->getStaff() != $_SESSION['i']) {
	$order = new \Order\Model();
}

$root = '/order';

if ($order->getId()) {
	w('ft');
	\Page::name('Заказ №'.$order->getId().' от '.ft($order->getDt(), 1).' &mdash; '.ft($order->getLast(), 1));

	$sync = db_fetch_all('SELECT * FROM sync WHERE store='.$order->getStore().' ORDER BY count DESC', 'vendor');
	$config['sync'] = $sync;

	$action = \Page::arg(1, 'edit');

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
		if (!$order->getStaff()) { $order->setStaff($_SESSION['i']); }
		$order->setMark(\Flydom\Arrau::decodec($order->getMark()));

		$data = $order->getData();
		$plan['']['default'] = $data;

		if ($order->getState() == 0) {
			$plan['state']['readonly'] = 1;
		}

		$path = '/files/order/'.$order->getId().'/';
		$plan['']['path'] = $path;
		$plan['docs']['path'] = $path;
		$plan['files']['path'] = $path;

		if ($order->getState() < 35) { // Отменённые заказы в любом случае можно редактировать
			if ($order->getKkm()) { $plan['pay']['readonly'] = 1; $plan['money']['readonly'] = 1; }
			if ($order->getKkm2()) { $plan['pay2']['readonly'] = 1; $plan['money2']['readonly'] = 1; }
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

		if ($plan['last']['value'] < $order->getLast()) {
			\Flydom\Alert::warning('Заказ изменён другим пользователем! Сохранение невозможно!');
			$plan['']['valid'] = 0;
		}

		if ($order->getVendor() != $plan['vendor']['value'] && $plan['vendor']['value'] != 45) {
			if ($plan['count']['value'] > kv($sync, $plan['vendor']['value'], ['count'=>0])['count']) {
				\Flydom\Alert::warning('Недостаточно товаров у поставщика!');
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
				\Flydom\Alert::warning('Сумма к оплате превышает сумму заказа!');
			}

			if (!is_null($changes)) {
				$type = $order->getState() == $plan['state']['value'] ? -1 : $plan['state']['value'];
				comment_type('o'.$order->getId(), $type, $changes);


				// Фискализация
				$kkm = false;
				if (!$order->getKkm() && $order->getMoney() != $plan['money']['value'] && $plan['money']['value'] && $plan['pay']['value'] == 3) { // Банковская карта (Терминал)
					$kkm = true;
				}

				if (!$order->getKkm2() && $order->getMoney2() != $plan['money2']['value'] && $plan['money2']['value'] && $plan['pay2']['value'] == 3) { // Банковская карта (Терминал)
					$kkm = true;
				}

				$order->setStaff($plan['staff']['value'])
					->setState($plan['state']['value'])
					->setCire($plan['cire']['value'])
					->setCity($plan['city']['value'])
					->setAdres($plan['adres']['value'])
					->setDost($plan['dost']['value'])
					->setVendor($plan['vendor']['value'])
					->setPrice($plan['price']['value'])
					->setSale($plan['sale']['value'])
					->setCount($plan['count']['value'])
					->setInfo($plan['info']['value'])
					->setNote($plan['note']['value'])
					->setMoney0($plan['money0']['value'])
					->setPay($plan['pay']['value'])
					->setMoney($plan['money']['value'])
					->setPay2($plan['pay2']['value'])
					->setMoney2($plan['money2']['value'])
					->setDocs($plan['docs']['value'])
					->setFiles($plan['files']['value'])
					->setMark(\Flydom\Arrau::encodec($plan['mark']['value']))
					->setMpi($plan['mpi']['value'])
					->setMpdt($plan['mpdt']['value']);

				if (kv($plan['city'], 'lat', 0) && kv($plan['city'], 'lon', 0)) {
					$order->setLat($plan['city']['lat'])
						->setLon($plan['city']['lon']);
				}

				$order->update();

				$plan['last']['value'] = \Config::now();
				\Flydom\Alert::warning('<a href="/order/'.$order->getId().'">Заказ</a> изменён: '.$changes);

				if ((($plan['send']['value'] == 1 || $plan['send']['value'] == 4) && $order->getState() != $plan['state']['value']) || $plan['dost']['value'] != $order->getDost()) {
					w('mail');
					mail_order($order->getUser(), $order->getId());
				}

				if ($kkm && ($plan['money']['value'] + $plan['money2']['value']) < 200000) {
					// Автоматическая фискализация на сумму меньше 200 000
					$q = db_query('SELECT * FROM kkm WHERE state<10 AND usr='.$order->getUser());
					if ($old = db_fetch($q)) {
						// Добавляем к чеку
						db_update('kkm', array(
							'orst'=>$old['orst'].$order->getId().'|',
							'dt'=>\Config::now(),
						), [
							'i='.$old['i'],
						]);
						\Flydom\Alert::warning('Добавлено в <a href="/kkm/'.$old['i'].'">фискальный чек</a>');
					} else {
						// Создаём новый чек
						db_insert('kkm', [
							'dt'=>\Config::now(),
							'state'=>0,
							'usr'=>$order->getUser(),
							'staff'=>$_SESSION['i'],
							'orst'=>'|'.$order->getId().'|',
						]);
						\Flydom\Alert::warning('Создан <a href="/kkm">фискальный чек</a>');
					}
				}
			}

			if ($plan['send']['value'] == 4) {
				\Page::redirect($root);
			}
		} elseif ($plan['']['valid'] && $plan['send']['value'] == 3) {
			$id = $order->getId();
			$order->delete();
			\Flydom\Alert::warning('Заказ удален.');

			w('log');
			logs(39, $id);
			\Page::redirect($root);

			db_delete('bill', array(
				'orst'=>$id,
				'state<10',
			));
		}
	}

	if ($action == 'link') {
		rebody('link');
	}

	$plan['name']['value'] = '<a href="/store/'.$order->getStore().'">'.$order->getName().'</a>';
	$name = trim($order->getUserName());
	$plan['user']['value'] = '<a href="/user/'.$order->getUser().'">'.(strlen($name) < 3 ? $order->getUser().' '.$name : $name).'</a>';
} else {
	\Page::redirect($root);
}