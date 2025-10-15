<?

w('clean');
w('chosen.js');

//	$store = w('catalog-all');

$ifs = array(
	''=>'',
	'dt-before'=>'Регистрация не ранее',
	'dt-after'=>'Регистрация после',
	'last-before'=>'Заходил до',
	'last-after'=>'Заходил после',
	'order-before'=>'Есть активный заказ до',
	'order-after'=>'Есть активный заказ после',
	'buy-before'=>'Покупал до',
	'buy-after'=>'Покупал после',
	'buy-none'=>'Ничего не покупал',
);

$plan = array(
	''=>array('method'=>'POST'),

	'test'=>array('name'=>'Проверочный адрес', 'type'=>'line'),

//	'city'=>array('name'=>'Пользователь из города', 'type'=>'multi2', 'values'=>$city, 'placeholder'=>'Все города', 'width'=>700),
//	'store'=>array('name'=>'Есть заказы в разделах', 'type'=>'multi2', 'values'=>$store, 'placeholder'=>'Все разделы', 'width'=>700),

	'if'=>array('name'=>'Условие', 'type'=>'combo', 'values'=>$ifs),

	'dt'=>array('name'=>'Дата', 'type'=>'date', 'default'=>\Config::now()-31*24*60*60),

	'theme'=>array('name'=>'Тема', 'type'=>'line'),

	'text'=>array('name'=>'', 'type'=>'rich', 'rows'=>12),

	'send'=>array('type'=>'button', 'count'=>2, 1=>'Проверка', 2=>'Разослать', 'class'=>array(1=>'btn-success', 2=>'btn-warning')),
);

w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value']) {
	$dt = $plan['dt']['value'];
	switch ($plan['if']['value']) {
	case 'dt-before': $where = 'user.dt<'.$dt; break;
	case 'dt-after': $where = 'user.dt>'.$dt; break;
	case 'last-before': $where = 'user.last<'.$dt; break;
	case 'last-after': $where = 'user.last>'.$dt; break;
	case 'order-before': $where = 'EXISTS (SELECT 1 FROM orst WHERE orst.user=user.i AND orst.state<30 AND orst.last<'.$dt.')'; break;
	case 'order-after': $where = 'EXISTS (SELECT 1 FROM orst WHERE orst.user=user.i AND orst.state<30 AND orst.last>'.$dt.')'; break;
	case 'buy-before': $where = 'EXISTS (SELECT 1 FROM orst WHERE orst.user=user.i AND orst.state=30 AND orst.last<'.$dt.')'; break;
	case 'buy-after': $where = 'EXISTS (SELECT 1 FROM orst WHERE orst.user=user.i AND orst.state=30 AND orst.last>'.$dt.')'; break;
	case 'buy-none': $where = 'NOT EXISTS (SELECT 1 FROM orst WHERE orst.user=user.i AND orst.state=30)'; break;
	default: $where = '1=1';
	}

	w('email2');
	if ($plan['send']['value'] == 1) {
		$select = 'SELECT COUNT(*) FROM user WHERE user.spam>0 AND '.$where;
		$count = db_result($select);
		\Flydom\Alert::warning('Аудитория рассылки: '.$count.' адресов.');
		w('clean');
		if (is_mail($plan['test']['value'])) {
			\Flydom\Alert::warning('Проверочное письмо отправлено.', 'success');
			email2($plan['test']['value'], 'news@muzmart.com', 'Тестовый адресат', $plan['theme']['value'], $plan['text']['value']);
		} else {
			\Flydom\Alert::warning('Проверочный адрес указан неверно!');
		}
	}

	if ($plan['send']['value'] == 2) {
		$select = 'SELECT * FROM user WHERE user.spam>0 AND '.$where;
		$count = 0;
		$q = db_query($select);

		w('log');
		while ($i = db_fetch($q)) {
			$email = $i['email'];
			if (!is_mail($email)) {
				continue;
			}

			$count++;
			db_insert('mail', array(
				'dt'=>\Config::now(),
				'user'=>$i['i'],
				'info'=>php_encode(array(
					'mail'=>$email,
					'from'=>'news@muzmart.com',
					'name'=>$i['name'],
					'theme'=>$plan['theme']['value'],
					'rows'=>$plan['text']['value']
				)),
			));
		}
		\Flydom\Alert::warning('Рассылка на '.$count.' адресов поставлена в очередь.', 'success');
	}
}

$config['plan'] = $plan;

?>