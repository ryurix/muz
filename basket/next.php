<?

w('basket');
$basket = basket_calc(kv($_SESSION, 'basket', array()), kv($_SESSION, 'sale', ''));

$total = 0;
foreach ($basket as $k=>$i) {
	$total+= $i['price2']*$i['count'];
}

$config['total'] = $total;
$config['dost'] = 300;

$city = cache_load('city');
$dost = w('list-dost');
$ptype = w('list-pay-user');

$plan = array(
	''=>array('method'=>'POST', 'open'=>0, 'close'=>1),
	'name'=>array('name'=>'Фамилия', 'type'=>'line', 'required'=>1, 'placeholder'=>'Иван Иванов'),
	'phone'=>array('name'=>'Телефон', 'type'=>'phone', 'id'=>'contact_phone', 'placeholder'=>'+7 987 1234567', 'min'=>11),
	'email'=>array('name'=>'Email', 'type'=>'line', 'placeholder'=>'mail@mail.com'),
	'cire'=>array('name'=>'Город', 'type'=>'combo', 'class'=>'basket2_select', 'values'=>$city, 'default'=>kv($_SESSION, 'cire', 0)),
	'comm'=>array('name'=>'Комментарий', 'type'=>'wiki', 'rows'=>13, 'id'=>'comm'),

	'dost'=>array('name'=>'Способ доставки', 'type'=>'radio', 'default'=>'self', 'values'=>$dost),
	'city'=>array('name'=>'Город', 'type'=>'adres', 'class'=>'basket_address', 'placeholder'=>'Москва, ул. пушкина 10'),
	'adres'=>array('name'=>'Адрес', 'type'=>'line', 'class'=>'basket_address', 'placeholder'=>'2 подъзд, 2 этаж, 70 квартира'),

	'send'=>array('type'=>'button', 'count'=>1, 1=>'Заказ подтверждаю', 'class'=>'btn-success'),

	'ul'=>array('name'=>'Юридическое лицо', 'type'=>'checkbox', 'label'=>'Я юридическое лицо', 'id'=>'ur_checkbox', 'class'=>'basket_checkbox'),

// оплата (pay)

	'ptype'=>array('type'=>'radio', 'values'=>$ptype, 'class'=>'basket_radio', 'default'=>7),
	'uname'=>array('name'=>'Наименование предприятия', 'type'=>'line', 'class'=>'basket_inputtext'),
	'head'=>array('name'=>'Директор', 'type'=>'line', 'class'=>'basket_inputtext'),
	'uadr'=>array('name'=>'Юридический адрес', 'type'=>'line', 'placeholder'=>'индекс, область, город, улица, дом', 'class'=>'basket_inputtext'),
	'fadr'=>array('name'=>'Физический адрес', 'type'=>'line', 'placeholder'=>'индекс, область, город, улица, дом', 'class'=>'basket_inputtext'),
	'inn'=>array('name'=>'ИНН', 'type'=>'line', 'class'=>'basket_inputtext'),
	'kpp'=>array('name'=>'КПП', 'type'=>'line', 'class'=>'basket_inputtext'),
	'okpo'=>array('name'=>'ОКПО', 'type'=>'line', 'class'=>'basket_inputtext'),
	'bank'=>array('name'=>'Наименование банка', 'type'=>'line', 'class'=>'basket_inputtext'),
	'bik'=>array('name'=>'БИК', 'type'=>'line', 'class'=>'basket_inputtext'),
	'bras'=>array('name'=>'Расчетный счет', 'type'=>'line', 'class'=>'basket_inputtext'),
	'bkor'=>array('name'=>'Корреспондентский счет', 'type'=>'line', 'class'=>'basket_inputtext'),
);

if (\User::is()) {
	$default = array(
		'name'=>$_SESSION['name'],
		'phone'=>$_SESSION['phone'],
		'email'=>$_SESSION['email'],
		'cire'=>$_SESSION['cire'],
		'dost'=>$_SESSION['dost'],
		'city'=>$_SESSION['city'],
		'adres'=>$_SESSION['adres'],

		'ul'=>$_SESSION['ul'],
	);

	$pay = array_decode($_SESSION['pay']);

	$default = $default + $pay;

	$plan['']['default'] = $default;

	$plan['phone']['readonly'] = 1;
	$plan['email']['readonly'] = 1;
}

w('request', $plan);
if ($plan['dost']['value'] == 'cour' || $plan['dost']['value'] == 'post') {
	w('u8');
	if (u8len($plan['city']['value']) < 3) {
		$plan['city']['iv'] = 1;
		$plan['']['valid'] = 0;
	}
}

/*
if ($plan['dost']['value'] == 'cour' || $plan['dost']['value'] == 'self') {
	if ($plan['cire']['value'] != 34 && $plan['cire']['value'] != 89) {
		$plan['dost']['iv'] = 1;
		$plan['cire']['iv'] = 1;
		$plan['']['valid'] = 0;
	}
}
*/

/*
if ($plan['phone']['value'] != $_SESSION['phone'] && strlen($plan['phone']['value'])) {
	w('clean');
	if (db_result('SELECT COUNT(*) FROM user WHERE phone="'.$plan['phone']['value'].'"')) {
		$plan['phone']['iv'] = 1;
		$plan['phone']['valid'] = 0;
		$plan['']['valid'] = 0;
	}
}
*/

if ($plan['email']['value'] != ($_SESSION['email'] ?? '') && strlen($plan['email']['value'])) {
	w('clean');
	if (db_result('SELECT COUNT(*) FROM user WHERE email="'.clean_mail($plan['email']['value']).'"')) {
		$plan['email']['iv'] = 1;
		$plan['email']['valid'] = 0;
		$plan['']['valid'] = 0;
	}
}

$config['plan'] = $plan;

if ($plan['send']['value'] == 1 && $plan['']['valid']) {

	$pay = array(
		'ptype'=>$plan['ptype']['value'],
		'uname'=>$plan['uname']['value'],
		'head'=>$plan['head']['value'],
		'uadr'=>$plan['uadr']['value'],
		'fadr'=>$plan['fadr']['value'],
		'inn'=>$plan['inn']['value'],
		'kpp'=>$plan['kpp']['value'],
		'okpo'=>$plan['okpo']['value'],
		'bank'=>$plan['bank']['value'],
		'bik'=>$plan['bik']['value'],
		'bras'=>$plan['bras']['value'],
		'bkor'=>$plan['bkor']['value'],
	);

	$user = array(
		'name'=>$plan['name']['value'],
		'phone'=>$plan['phone']['value'],
		'email'=>$plan['email']['value'],
		'cire'=>$plan['cire']['value'],
		'dost'=>$plan['dost']['value'],
		'city'=>$plan['city']['value'],
		'adres'=>$plan['adres']['value'],
		'ul'=>$plan['ul']['value'],
		'pay'=>array_encode($pay),
		'mark'=>kv($_SESSION, 'mark', 0),
		'mark2'=>kv($_SESSION, 'mark2', 0),
	);

	if (kv($plan['city'], 'lat', 0) && kv($plan['city'], 'lon', 0)) {
		$user['lat'] = $plan['city']['lat'];
		$user['lon'] = $plan['city']['lon'];
	}

	if (\User::is()) {
		db_update('user', $user, array('i'=>$_SESSION['i']));
		$_SESSION = $_SESSION + $user;
		$ui = $_SESSION['i'];
	} else {
		$where = array();
		$email = preg_replace('@[^0-9a-zA-Z_\-\.\+\^!#\@\$%&*+\/\=\?\`\|\{\}~\']+@', '', $user['email']);
		$email = strtolower($email);
		if (strlen($email) && strpos($email, '@')) {
			$where[]= 'email="'.$email.'"';
		}
		$phone = preg_replace('@[^0-9]+@', '', $plan['phone']['value']);
		if (strlen($phone) > 10) {
			if (substr($phone, 0, 1) == '8') {
				$phone = '7'.substr($phone, 1);
			}
			$where[] = 'phone="'.$phone.'"';
		}
		if (count($where) == 0) {
			$where[] = '1=0';
		}

		$q = db_query('SELECT * FROM user WHERE '.implode(' OR ', $where));
		if ($row = db_fetch($q)) {
			if ($phone == $row['phone']) {
				$ui = $row['i'];
				$note = 'Проверить авторизацию, адрес: '.addslashes(trim($plan['city']['value'].' '.$plan['adres']['value']));

				if (!isset($user['lat'])) {
					$user['lat'] = $row['lat'];
					$user['lon'] = $row['lon'];
				}

				$user['mark'] = $row['mark'];
				$user['mark2'] = $row['mark2'];
			} else {
				\Flydom\Alert::danger('Пользователь с данной электронной почтой уже зарегистрирован! <a href="/my">Восстановление пароля.</a>');

				w('log');
				logs(22, $ui, array_encode($user));

				$ui = 0;
			}
		} else {
			$pass = '';
			for ($i=0; $i<4; $i++) {$pass.= rand(0, 9);}

			w('u8');
			$name = u8capitalize($plan['name']['value']);

			$data = $user + array(
				'quick'=>'',
				'dt'=>\Config::now(),
				'last'=>\Config::now(),
//				'login'=>$login,
				'pass'=>$pass,
				'spam'=>1,
				'note'=>1,
				'color'=>0,
				'roles'=>'',
			);
			db_insert('user', $data);

			if (is_mail($data['email'])) {
				w('mail-register', $data);
				\Flydom\Alert::warning('Вы зарегистрированы, пароль <b>'.$pass.'</b>, выслан на почту: '.$data['email']);
			} else {
				\Flydom\Alert::warning('Вы зарегистрированы, пароль <b>'.$pass.'</b>');
			}
			\User::try($data['phone'], $pass);
			$ui = $_SESSION['i'];

			w('cache-user', $ui);
		}
	}

	if ($ui) {

		$table = '<table border=1 bordercolor=gray cellpadding=3><thead><tr>'
.'<th># Заказа</th>'
.'<th>Наименование</th>'
.'<th>Цена</th>'
.'<th>Количество</th>'
.'<th>Сумма</th>'
.'</tr></thead><tbody>'; // .'<th>Комментарий</th>'

		$products = array();
		$transID = 0;
		$amount = 0;

		$mark = array();
		if ($user['mark']) { $mark[] = $user['mark']; }
		if ($user['mark2']) { $mark[] = $user['mark2']; }

		w('log');
		foreach ($basket as $v) {
			$data = array(
				'dt'=>\Config::now(),
				'last'=>\Config::now(),
				'user'=>$ui,
				'staff'=>\User::is('order') ? $ui : 0,
				'state'=>1,
				'cire'=>$plan['cire']['value'],
				'city'=>$plan['city']['value'],
				'adres'=>$plan['adres']['value'],
				'vendor'=>0,
				'store'=>$v['store'],
				'name'=>$v['name'],
				'price'=>$v['price2'],
				'sale'=>$v['sale'],
				'info'=>strip_tags($plan['comm']['value']),
				'note'=>isset($note) ? $note : '',
				'count'=>$v['count'],
				'dost'=>$plan['dost']['value'],
				'pay'=>$plan['ptype']['value'],
				'mark'=>count($mark) ? ','.implode(',', $mark).',' : '',
			);
			if (isset($user['lat'])) { $data['lat'] = $user['lat']; }
			if (isset($user['lon'])) { $data['lon'] = $user['lon']; }

			$ids = (new \Order\Model($data))->create();

			if(!$transID) $transID = reset($ids);

			$table.= '<tr>'
.'<td align=center>'.reset($ids).'</td>'
.'<td>'.$v['name'].'</td>'
.'<td align=center nowrap>'.number_format($v['price2'], 0, '.', ' ').'</td>'
.'<td align=center nowrap>'.number_format($v['count'], 0, '.', ' ').'</td>'
.'<td align=center nowrap>'.number_format($v['count']*$v['price2'], 0, '.', ' ').'</td>'
.'</tr>'; // <td>'.$v['info'].'</td>

			$p = array("id" => $v["store"]);
			if($v["price2"])
			{
				$p["price"] = $v["price2"];
				$amount += $v["price2"] * $v['count'];
			}
			$p["quantity"] = $v['count'];
			$p["name"] = $v['name'];
			$p["brand"] = $v["brand"];
			$p["category"] = $v["category"];
			if ($v["sale"])
			{
				$p["coupon"] = $v["sale"];
			}
			$products[] = $p;

			logs(25, $v['store'], $v['name'].' '.$v['count'].'*'.$v['price2'].' '.array_encode($_REQUEST));
		}

		$_SESSION['basket'] = array();

		$table.= '<tr><td colspan=5>'.$plan['comm']['value'].'</td></tr></tbody></table>';

		if(count($products))
		{
			$_SESSION["ecommerce"] = array(
				"event" => "gtm-ee-event",
				"gtm-ee-event-category" => "Enhanced Ecommerce",
				"gtm-ee-event-action" => "Purchase",
				"gtm-ee-event-non-interaction" => "False",
				"ecommerce" => array(
					"currencyCode" => "RUB",
					"purchase" => array(
						"actionField" => array("id" =>$transID, "revenue" => $amount),
						"products" => $products
				))
			);
		}

		w('clean');
		if (is_mail($_SESSION['email'])) {

			$html = cache_get('mail-basket');
			$html = str_replace('%NAME%', $_SESSION['name'], $html);
			$html = str_replace('%TABLE%', $table, $html);

			w('ft');
			w('email2');

			email2($_SESSION['email'], $config['backmail'], $_SESSION['name'], 'Заказ от '.ft(\Config::now()), $rows = array($html));

			logs(43, $_SESSION['i'], $_SESSION['email']);

			\Flydom\Alert::warning('Заказ оформлен, на почту отправлено письмо с подтверждением');
		}

		\Page::redirect('/basket/thanks');
	} else {
		\Flydom\Alert::warning('Зарегистрируйтесь или войдите под своей учетной записью. В случае проблем &mdash; обратитесь к менеджеру.');
	}
}

?>