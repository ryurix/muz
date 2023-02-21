<?

$q = db_query('SELECT * FROM user WHERE i='.$config['args'][0]);

if (($row = db_fetch($q)) && (is_user('admin') || strlen($row['roles']) == 0 || $row['i'] == $_SESSION['i'])) {
	$sales = strpos($row['roles'], 'order') !== FALSE
	&& !(strpos($row['roles'], 'ones') !== FALSE && $_SESSION['i'] != $row['i']);

	$action = array();
	$action[] = array('href'=>'/user/'.$row['i'], 'action'=>'Посмотреть');
	$action[] =	array('href'=>'/user/'.$row['i'].'/order', 'action'=>'Заказы');
	$action[] = array('href'=>'/user/'.$row['i'].'/docs', 'action'=>'Документы');
	if (is_user('roles')) {
		$action[] = ['href'=>'/user/'.$row['i'].'/roles', 'action'=>'Роли'];
	}
	if ($sales) {
		$action[] = array('href'=>'/user/'.$row['i'].'/sales', 'action'=>'Продажи');
	}
	$action[] = array('href'=>'/user/'.$row['i'].'/erase', 'action'=>'Удалить');
	$config['action'] = $action;

	$config['name'] = $row['name'];
	$action = count($config['args']) > 1 ? $config['args'][1] : 'edit';

	if ($action == 'edit') {
		$plan = w('plan-user');

		$row['config'] = strlen($row['config'])  > 2 ? array_decode($row['config']) : array();
		$row['vendor'] = isset($row['config']['vendor']) ? $row['config']['vendor'] : 0;

		if (strlen($row['pay']) > 5) {
			if (substr($row['pay'], 0, 1) == '[' || substr($row['pay'], 0 , 5) == 'array') {
				$row = $row + php_decode($row['pay']);
			} else {
				$row = $row + array_decode($row['pay']);
			}
		}

		$plan['']['default'] = $row;
		$plan['pass1']['default'] = $row['pass'];
		w('request', $plan);
		$plan['pay']['ul'] = $plan['ul']['value'];

		if (strlen($plan['phone']['value'])) {
			$plan['phone']['placeholder'] = htmlspecialchars($plan['phone']['value']);
		}

		if ($plan['phone']['value'] != $row['phone'] && strlen($plan['phone']['value'])) {
			w('clean');
			if (db_result('SELECT COUNT(*) FROM user WHERE phone="'.$plan['phone']['value'].'" AND i<>'.$row['i'])) {
				$plan['phone']['iv'] = 1;
				$plan['phone']['valid'] = 0;
				$plan['']['valid'] = 0;
				alert('Данный телефон уже зарегистрирован на другого пользователя!', 'danger');
			}
		}

		if ($plan['email']['value'] != $row['email'] && strlen($plan['email']['value'])) {
			w('clean');
			if (db_result('SELECT COUNT(*) FROM user WHERE email="'.clean_mail($plan['email']['value']).'" AND i<>'.$row['i'])) {
				$plan['email']['iv'] = 1;
				$plan['email']['valid'] = 0;
				$plan['']['valid'] = 0;
			}
		}

		if ($plan['']['valid'] && $plan['send']['value'] == 1) {
			w('search');

 			if ($plan['vendor']['value']) {
 				$row['config']['vendor'] = $plan['vendor']['value'];
 			} else {
 				unset($row['config']['vendor']);
 			}
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

 			w('clean');
			$data = array(
				'quick'=>'',
//				'login'=>$plan['login']['value'],
				'pass'=>$plan['pass1']['value'],
				'roles' => is_user('admin') ? $plan['roles']['value'] : $row['roles'] ,
				'name'=>$plan['name']['value'],
				'phone'=>clean_phone($plan['phone']['value']),
				'email'=>clean_mail($plan['email']['value']),
				'cire'=>$plan['cire']['value'],
				'city'=>$plan['city']['value'],
				'adres'=>$plan['adres']['value'],
				'pay'=>array_encode($pay),
				'ul'=>$plan['ul']['value'],
				'dost'=>$plan['dost']['value'],
				'note'=>$plan['note']['value'],
				'spam'=>$plan['spam']['value'],
				'config'=>count($row['config']) ? array_encode($row['config']) : NULL,
				'color'=>$plan['color']['value'],
				'mark'=>$plan['mark']['value'],
				'mark2'=>$plan['mark2']['value'],
			);

			db_update('user', $data, array('i'=>$row['i']));
			db_insert('log', array(
				'type'=>10,
				'dt'=>now(),
				'user'=>$_SESSION['i'],
				'info'=>'',
			));
			w('cache-user', $row['i']);
			w('cache-staff');
//			redirect('/user');
		}  else {
			$fields = array();
			foreach ($plan as $v) {
				if (isset($v['iv'])) {
					$fields[] = $v['name'];
				}
			}
			if (count($fields) > 0) {
				$s = 'Заполните поле "'.$fields[0].'"';
				$s.= count($fields) > 1 ? ' и другие поля выделенные' : ' выделенное';
				$s.= ' красным цветом';
				alert($s);
			} elseif (count($fields) > 1) {
				alert('Заполните поле "'.$fields[0].'" выделенное красным цветом.');
			}
		}
		$config['plan'] = $plan;
	} elseif ($action == 'order') {
		w('order-user', $row['i']);
		refile('order.html');
	} elseif ($action == 'docs') {
		$config['row'] = $row;
		refile('docs.html');
	} elseif ($action == 'erase') {
		$plan = w('plan-erase');
		w('request', $plan);

		if ($plan['']['valid']) {
			if ($plan['send']['value'] == 1) {
				db_delete('user', array('i'=>$row['i']));
				db_delete('comment', array('theme'=>'u'.$row['i']));
				db_delete('orst', array('user'=>$row['i']));
				db_delete('session', array('usr'=>$row['i']));
				redirect('/user');
			} else {
				redirect('/user/'.$row['i']);
			}
		} else {
			$config['plan'] = $plan;
			refile('erase.html');
		}
	} elseif ($action == 'roles') {
		refile('roles.html');
		$plan = w('plan-roles', $row['roles']);
		w('request', $plan);

		if ($plan['']['valid']) {
			if ($plan['send']['value'] == 1) {
				$roles = [];
				foreach (w('list-roles') as $k=>$v) {
					if ($plan[$k]['value']) {
						$roles[] = $k;
					}
				}

				db_update('user', [
					'roles'=>implode(' ', $roles)
				], ['i'=>$row['i']]);

				db_delete('session', ['usr'=>$row['i']]);
				alert('Роли сохранены', 'success');
				redirect('/user/'.$row['i']);
			}
		}
	} elseif ($action == 'sales' && $sales) {
		refile('sales.html');
		$config['row'] = $row;
	}
} else {
	redirect('/user');
}