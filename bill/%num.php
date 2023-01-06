<?

$key = isset($config['args'][0]) ? $config['args'][0] : 0;

$where = '';
if (!is_user('order')) {
	$where = ' AND state=1 AND user='.$_SESSION['i'];
}

$q = db_query('SELECT * FROM bill WHERE i='.$key.$where);

if (!$row = db_fetch($q)) {
	alert('Счет #'.$key.' не найден!');
	redirect('/');
}

$action = count($config['args']) > 1 ? $config['args'][1] : 'view';

if ($action == 'pay' && $row['state'] == 1) {
	$config['row'] = $row;
} elseif ($action == 'edit' && is_user('order')) {
	$plan = array(
		''=>array('method'=>'POST'),
		'type'=>array('name'=>'Вид', 'type'=>'combo', 'values'=>w('list-bill'), 'width'=>300, 'readonly'=>1),
		'state'=>array('name'=>'Состояние', 'type'=>'combo', 'values'=>w('state-bill'), 'width'=>300),
		'total'=>array('name'=>'Сумма', 'type'=>'line'),
		'send'=>array('type'=>'button', 'count'=>2, 1=>'Сохранить', 2=>'Удалить'),
	);
	$plan['']['default'] = $row;
	w('request', $plan);

	if ($plan['']['valid']) {
		if ($plan['send']['value'] == 1) {
			$data = array(
				'state'=>$plan['state']['value'],
				'total'=>round($plan['total']['value']),
			);

			$code = 0;
			if ($row['type'] >= 200 && $row['state'] == 0 && $plan['state']['value'] == 1) { // Отправка данных счета Аппексу
				w('appex');

				$info = '';
// Создание заказа
				$o = appex('POST', 'ecommerce/orders', array(
					'sourceCode'=>$row['i'],
					'priceData'=>array(
							'originalPrice'=>array(
							'amount'=>$plan['total']['value'],
							'currencyCode'=>'RUB'
						),
						'exchangeRate'=>1.0,
						'priceIsLinked'=>false,
						'exchangeRateSourceType'=>'manual',
						'exchangeRateAdditionalPercent'=>0.0,
						'currencyCode'=>'RUB',
					),
					'orderData'=>array(
						'products'=>array(
							array('description'=>$info)
						),
					),
					'billingCode'=>'muzmart003',
				));
				
				if (is_object($o)) {
					$code = $o->code;
					$data['code'] = $code;

// Отправка данных пользователя
					$q = db_query('SELECT * FROM user WHERE i='.$row['user']);
					if ($u = db_fetch($q)) {
						$name = explode(' ', $u['name']);
						$city = cache_load('city');
						$arr = array(
							'firstName'=>isset($name[1]) ? $name[1] : '',
							'lastName'=>$name[0],
							'email'=>$email,
							'phone'=>$u['phone'],
							'address'=>$u['adres'],
							'city'=>kv($city, $u['city']),
							'country'=>'Россия',
							'orderCode'=>$code,
						);
						/*
						w('clean');
						$email = clean_phone($u['email']);
						if ($email) {
							$arr['email'] = $email;
						}
						*/
						$o = appex('PUT', 'ecommerce/orders/'.$code.'/payer', $arr);
					}					

// Отправка кода возврата
					$o = appex('GET', 'bills/'.$code, array(
						'orderCode'=>$code,
						'returnUrl'=>'https://muzmart.com/basket/ok',
					));
				} else {
					alert('Ошибка создания запроса appex: '.$o.'. Необходимо создать новый счет!', 'danger');
				}
			}

			db_update('bill', $data, array('i'=>$key));
			alert('Счет изменен!');

			redirect('/bill/'.$key);
		} elseif ($plan['send']['value'] == 2) {
			db_delete('bill', array('i'=>$key));
			alert('Счет удален!');
			w('clean');
			$orst = first_int(trim($row['orst'], '|'));
			redirect('/order/'.$orst);
		}
	}

	$config['plan'] = $plan;
	refile('edit.html');
} else {
	$config['row'] = $row;
	refile('view.html');
}

?>