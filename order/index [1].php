<?

w('ft');

$vendor = array(-1=>'Поставщик') + cache_load('vendor');
$dost = array_values(w('list-dost'));
$dost[0] = 'Неизвестно';
$cdost = array(-1=>'Способ') + w('list-dost');
$states = array(-1=>'Активные заказы') + w('order-state');
$staff = array(0=>'Менеджер') + cache_load('staff');
$cire = array('na'=>'Город') + cache_load('city');
$pay = array('na'=>'Предоплата') + w('list-pay');
$pay2 = array('na'=>'Оплата') + w('list-pay');
$mark = array(0=>'Нет меток') + cache_load('mark-name');
$camark = array(0=>'Добавить метку') + cache_load('mark-name');
$cdmark = array(0=>'Удалить метку') + cache_load('mark-name');

$cstate = array(0=>'Статус') + w('order-state');

w('user-config');

$plan = array(
	''=>array('method'=>'POST', 'default'=>get_user_config('orders', array())),

	'link'=>array('name'=>'Массовое изменение', 'type'=>'checkbox', 'default'=>0),
	'num'=>array('name'=>'Номер заказа', 'type'=>'int', 'default'=>'', 'class'=>'form-control auto', 'width'=>80, 'placeholder'=>'№ заказа'),
	'dt1'=>array('name'=>'Дата обновления от', 'type'=>'date', 'default'=>now() - 183*24*60*60, 'class'=>'auto', 'width'=>100, 'button'=>0, 'context'=>1),
	'dt2'=>array('name'=>'до', 'type'=>'date', 'default'=>ft_parse(ft(now())) + 24*60*60 - 1, 'class'=>'auto', 'width'=>100, 'button'=>0, 'context'=>1), // 'width'=>65
	'staff'=>array('name'=>'Менеджер', 'type'=>'combo', 'values'=>$staff, 'default'=>0, 'class'=>'auto'),
	'cire'=>array('name'=>'Город', 'type'=>'cire', 'class'=>'form-control auto', 'values'=>$cire, 'default'=>'na'),
	'user'=>array('name'=>'Пользователь', 'type'=>'line', 'placeholder'=>'ФИО', 'x'=>1, 'default'=>'', 'class'=>'form-control auto'),
	'store'=>array('name'=>'Наименование', 'type'=>'line', 'placeholder'=>'Товар', 'x'=>1, 'class'=>'form-control auto'),
	'pay'=>array('name'=>'Оплата', 'type'=>'combo', 'values'=>$pay2, 'default'=>'na', 'class'=>'auto'),

	'group'=>array('name'=>'Группировать', 'type'=>'checkbox2', 'label'=>'Группировать', 'default'=>0, 'class'=>'auto'),

	'sort'=>array('name'=>'Сортировать по', 'type'=>'combo', 'values'=>array(
		5=>'Номеру заказа &darr;',
		105=>'Номеру заказа &uarr;',
		0=>'Дате создания &darr;',
		100=>'Дате создания &uarr;',
		1=>'Дате обновления &darr;',
		101=>'Дате обновления &uarr;',
		2=>'Пользователь &darr;',
		102=>'Пользователь &uarr;',
		14=>'Менеджер &darr;',
		114=>'Менеджер &uarr;',
		4=>'Наименование &darr;',
		104=>'Наименование &uarr;',
		6=>'Цена &darr;',
		106=>'Цена &uarr;',
		7=>'Сумма &darr;',
		107=>'Сумма &uarr;',
		8=>'Количество &darr;',
		108=>'Количество &uarr;',
		9=>'Оплачено &darr;',
		109=>'Оплачено &uarr;',
		11=>'Оплата &darr;',
		111=>'Оплата &uarr;',
		3=>'Статус &darr;',
		103=>'Статус &uarr;',
		13=>'Город &darr;',
		113=>'Город &uarr;',
		10=>'Доставка &darr;',
		110=>'Доставка &uarr;',
		12=>'Поставщик &darr;',
		112=>'Поставщик &uarr;',
		15=>'Метки &darr;',
		115=>'Метки &uarr;',
	), 'default'=>1, 'class'=>'form-control auto'),
//	'staff'=>array('name'=>'Мои', 'label'=>'', 'type'=>'checkbox', 'default'=>0),
	'vendor'=>array('name'=>'Поставщик', 'type'=>'combo', 'default'=>-1, 'class'=>'auto', 'values'=>$vendor),
	'dost'=>array('name'=>'Доставка', 'type'=>'dropcheck', 'default'=>array(), 'item-class'=>'auto2', 'values'=>$dost, 'placeholder'=>'Доставка'),
	'state'=>array('name'=>'Состояния', 'type'=>'dropcheck', 'item-class'=>'auto2', 'values'=>$states, 'default'=>array(-1), 'placeholder'=>'Статус'),
//	'mark'=>array('name'=>'Метки', 'type'=>'combo', 'default'=>0, 'class'=>'auto', 'values'=>$mark),
	'mark'=>array('name'=>'Метки', 'type'=>'dropcheck', 'default'=>array(), 'item-class'=>'auto2', 'values'=>$mark, 'placeholder'=>'Метки'),
	'send'=>array('name'=>'', 'type'=>'button', 'count'=>4, 1=>'Фильтровать', 2=>'Загрузить по умолчанию', 3=>'Сохранить по умолчанию', 4=>'Сбросить'),

	'cstaff'=>array('name'=>'Менеджер', 'type'=>'combo', 'values'=>$staff, 'default'=>0, 'class'=>'auto form-control-sm'),
	'cpay'=>array('name'=>'Предоплата', 'type'=>'combo', 'values'=>$pay, 'default'=>'na', 'class'=>'auto form-control-sm'),
	'cpay2'=>array('name'=>'Оплата', 'type'=>'combo', 'values'=>$pay2, 'default'=>'na', 'class'=>'auto form-control-sm'),
	'cstate'=>array('name'=>'Статус', 'type'=>'combo', 'values'=>$cstate, 'default'=>0, 'class'=>'auto form-control-sm'),
	'cdost'=>array('name'=>'Доставка', 'type'=>'combo', 'values'=>$cdost, 'default'=>-1, 'class'=>'auto form-control-sm'),
	'cvendor'=>array('name'=>'Новый поставщик', 'type'=>'combo', 'values'=>$vendor, 'default'=>-1, 'class'=>'auto form-control-sm'),

	'camark'=>array('name'=>'Добавить метку', 'type'=>'combo', 'values'=>$camark, 'default'=>0, 'class'=>'auto form-control-sm'),
	'cdmark'=>array('name'=>'Удалить метку', 'type'=>'combo', 'values'=>$cdmark, 'default'=>0, 'class'=>'auto form-control-sm'),
);

if (is_user('ones')) {
	$plan['staff']['type'] = 'hidden';
	$plan['staff']['value'] = $_SESSION['i'];
}

//$plan = isset($_SESSION['order']) ? $_SESSION['order'] : $default;

w('request', $plan);

if (in_array(-1, $plan['state']['value'])) {
	$plan['state']['value'] = array(1,2,3,7,10,13,15,20,23,25);
}

$values = w('values', $plan);

$linked = array('cstaff', 'camark', 'cdmark', 'cpay', 'cpay2', 'cstate', 'cdost', 'cvendor');
foreach ($linked as $i) {
	unset($values[$i]);
}

/*
unset($values['cstaff']);
unset($values['camark']);
unset($values['cdmark']);
unset($values['cpay']);
unset($values['cpay2']);
unset($values['cstate']);
unset($values['cdost']);
unset($values['cvendor']);
*/

set_user_config('orders', $values);

if ($plan['send']['value'] == 2) {
	set_user_config('orders', get_user_config('orders2'));
	save_user_config();
	redirect('/order');
}

if ($plan['send']['value'] == 3) {
	unset($values['dt1']);
	unset($values['dt2']);
	set_user_config('orders2', $values);
}

if ($plan['send']['value'] == 4) {
	set_user_config('orders', array());
	save_user_config();
	redirect('/order');
}

save_user_config();

// SELECT

$select = 'SELECT orst.i i'
.',orst.dt dt'
.',orst.store store'
.',s.url url'
.',orst.price price'
.',orst.state state'
.',orst.info info'
.',orst.note note'
.',orst.docs docs'
.',orst.files files'
.',orst.pay2 pay2'
.',orst.dost dost'
.',orst.cire'
.',orst.vendor'
.',orst.staff'
.',orst.mark'
.',user.i user'
.',user.name user_name'
.',user.color user_color';

$where_user = '';
if (strlen($plan['user']['value'])) {
	$user = $plan['user']['value'];
	w('clean');
	if (is_09($user)) {
		$where_user = ' AND orst.user='.$user;
		if (isset($_GET['user'])) {
			$plan['group']['value'] = 0;
		}
	} else {
		w('search');
		$where_user = ' AND user.quick LIKE "%'.search_like($user).'%"';
	}
}

if (is_user('ones')) {
	$where_user.= ' AND orst.staff='.$_SESSION['i'];
}

if ($plan['group']['value']) {
	$select.= ',COUNT(orst.i) name'
.',SUM(orst.count) count,SUM(orst.count*orst.price) total,SUM(orst.money+orst.money2) money';
} else {
	$select.= ',orst.name name'
.',orst.count count,orst.count*orst.price total,orst.money+orst.money2 money';
}

$select.= ' FROM orst LEFT JOIN user ON orst.user=user.i LEFT JOIN store s ON orst.store=s.i LEFT JOIN cire ON orst.cire=cire.i LEFT JOIN user staff ON orst.staff=staff.i';

if (strlen($plan['store']['value'])) {
	w('search');
	$select.= ',store WHERE orst.store=store.i AND store.quick LIKE "%'.search_like($plan['store']['value']).'%" AND';
} else {
	$select.= ' WHERE';
}

if ($plan['sort']['value'] == 5 || $plan['sort']['value'] == 105) {
	$select.= ' orst.dt>'.$plan['dt1']['value'].' AND orst.dt<'.($plan['dt2']['value']+24*60*60-1);
} else {
	$select.= ' orst.last>'.$plan['dt1']['value'].' AND orst.last<'.($plan['dt2']['value']+24*60*60-1);
}

$select.= $where_user;

w('clean');
if ($plan['num']['value'] && is_09($plan['num']['value'])) {
	$select.= ' AND orst.i='.$plan['num']['value'];
}

if ($plan['cire']['value'] != 'na') {
	$select.= ' AND orst.cire='.$plan['cire']['value'];
}

if ($plan['staff']['value']) {
	$select.= ' AND orst.staff='.$plan['staff']['value'];
}

if (count($plan['state']['value'])) {
	$select.= ' AND orst.state IN ('.implode(',', $plan['state']['value']).')';
}

if ($plan['pay']['value'] != 'na') {
	$select.= ' AND (orst.pay='.$plan['pay']['value'].' OR orst.pay2='.$plan['pay']['value'].')';
}

if ($plan['vendor']['value'] != -1) {
	$select.= ' AND orst.vendor='.$plan['vendor']['value'];
}

if (count($plan['dost']['value'])) {
	$codes = array_keys(w('list-dost'));
	$values = array();
	foreach ($plan['dost']['value'] as $i) {
		$values[] =  '"'.kv($codes, $i).'"';
	}
	$select.= ' AND orst.dost IN ('.implode(',', $values).')';
//	$select.= ' AND orst.dost="'.$plan['dost']['value'].'"';
}

if (count($plan['mark']['value'])) {
	$or = array();
	foreach ($plan['mark']['value'] as $i) {
		if ($i) {
			$or[] = '(orst.mark LIKE "%,'.$i.',%")';
		} else {
			$or[] = '(orst.mark="" OR orst.mark IS NULL)';			
		}
	}
	$select.= ' AND ('.implode(' OR ', $or).')';
}

if ($plan['group']['value']) {
	$select.= ' GROUP BY orst.user';
}

$select.= ' ORDER BY ';
switch ($plan['sort']['value']) {
case 0: $select.= 'orst.dt DESC'; break;
case 100: $select.= 'orst.dt'; break;
case 1: $select.= 'orst.last DESC'; break;
case 101: $select.= 'orst.last'; break;
case 2:$select.= 'user_name'; break;
case 102:$select.= 'user_name DESC'; break;
case 3: $select.= 'orst.state'; break;
case 103: $select.= 'orst.state DESC'; break;
case 4:$select.= 'orst.name'; break;
case 104:$select.= 'orst.name DESC'; break;
case 5: $select.= 'orst.i'; break;
case 105: $select.= 'orst.i DESC'; break;
case 6: $select.= 'orst.price'; break;
case 106: $select.= 'orst.price DESC'; break;
case 7: $select.= 'total'; break;
case 107: $select.= 'total DESC'; break;
case 8:$select.= 'orst.count'; break;
case 108:$select.= 'orst.count DESC'; break;
case 9:$select.= 'money'; break;
case 109:$select.= 'money DESC'; break;
case 10:$select.= 'dost'; break;
case 110:$select.= 'dost DESC'; break;
case 11:$select.= 'orst.pay2'; break;
case 111:$select.= 'orst.pay2 DESC'; break;
case 12:$select.= 'orst.vendor'; break;
case 112:$select.= 'orst.vendor DESC'; break;
case 13:$select.= 'cire.w'; break;
case 113:$select.= 'cire.w DESC'; break;
case 14:$select.= 'staff.name'; break;
case 114:$select.= 'staff.name DESC'; break;
case 15:$select.= 'orst.mark'; break;
case 115:$select.= 'orst.mark DESC'; break;
}
$select.= ',orst.user';

$config['select'] = $select;

if (is_user('link')
&& isset($_REQUEST['finish'])
&& (isset($_REQUEST['c']) && is_array($_REQUEST['c']))
&& ($plan['cstaff']['value']
	|| $plan['camark']['value']
	|| $plan['cdmark']['value']
	|| $plan['cpay']['value'] !== 'na'
	|| $plan['cpay2']['value'] !== 'na'
	|| $plan['cstate']['value']
	|| $plan['cdost']['value'] >= 0
	|| $plan['cvendor']['value'] >= 0)
) {
	$orst = $_REQUEST['c'];

	$updated = 0;

	w('comment');

	$q = db_query($select);
	while ($i = db_fetch($q)) {
		if (!isset($orst[$i['i']]) || !$orst[$i['i']]) { continue; }

		//	alert($i['i']);
		//	$type = $row['state'] == $plan['state']['value'] ? -1 : $plan['state']['value'];

		if ($plan['cstaff']['value'] && $plan['cstaff']['value'] != $i['staff']) {
			db_update('orst', array('staff'=>$plan['cstaff']['value']), array('i'=>$i['i']));
			comment_type('o'.$i['i'], -1, 'Связанное изменение менеджера ('.kv($plan['cstaff']['values'], $i['staff']).')');
			$updated++;
		}

		if ($plan['camark']['value']) {
			$mark = strlen($i['mark']) ? explode(',', trim($i['mark'], ',')) : array();
			$mark = remove_role($plan['camark']['value'], $mark);
			$mark[] = $plan['camark']['value'];
			$mark = ','.implode(',', $mark).',';
			if ($mark != $i['mark']) {
				db_update('orst', array('mark'=>$mark), array('i'=>$i['i']));
				comment_type('o'.$i['i'], -1, 'Связанное добавление метки '.kv($plan['camark']['values'], $plan['camark']['value']));
				$updated++;
			}
		}

		if ($plan['cdmark']['value']) {
			$mark = strlen($i['mark']) ? explode(',', trim($i['mark'], ',')) : array();
			$mark = remove_role($plan['cdmark']['value'], $mark);
			$mark = count($mark) ? ','.implode(',', $mark).',' : '';

			if ($mark != $i['mark']) {
				db_update('orst', array('mark'=>$mark), array('i'=>$i['i']));
				comment_type('o'.$i['i'], -1, 'Связанное удаление метки '.kv($plan['cdmark']['values'], $plan['cdmark']['value']));
				$updated++;
			}
		}

		if ($plan['cpay']['value'] !== 'na' && $plan['cpay']['value'] != $i['pay']) {
			db_update('orst', array('pay'=>$plan['pay']['value']), array('i'=>$i['i']));
			comment_type('o'.$i['i'], -1, 'Связанный способ предоплаты ('.kv($plan['cpay']['values'], $i['pay']).')');
			$updated++;
		}

		if ($plan['cpay2']['value'] !== 'na' && $plan['cpay2']['value'] != $i['pay2']) {
			db_update('orst', array('pay2'=>$plan['cpay2']['value']), array('i'=>$i['i']));
			comment_type('o'.$i['i'], -1, 'Связанный способ оплаты ('.kv($plan['cpay2']['values'], $i['pay2']).')');
			$updated++;
		}

		if ($plan['cstate']['value'] && $plan['cstate']['value'] != $i['state']) {
			db_update('orst', array('state'=>$plan['cstate']['value']), array('i'=>$i['i']));
			comment_type('o'.$i['i'], $plan['cstate']['value'], 'Связанное изменение статуса ('.kv($plan['cstate']['values'], $i['state']).')');
			$updated++;
		}

		if ($plan['cdost']['value'] >= 0 && $plan['cdost']['value'] != $i['dost']) {
			db_update('orst', array('dost'=>$plan['cdost']['value']), array('i'=>$i['i']));
			comment_type('o'.$i['i'], -1, 'Связанный способ доставки ('.kv($plan['cdost']['values'], $i['dost']).')');
			$updated++;
		}

		if ($plan['cvendor']['value'] >= 0 && $plan['cvendor']['value'] != $i['vendor']) {
			db_update('orst', array('vendor'=>$plan['cvendor']['value']), array('i'=>$i['i']));
			comment_type('o'.$i['i'], -1, 'Связанное изменение поставщика ('.kv($plan['cvendor']['values'], $i['vendor']).')');
			$updated++;
		}
	}

	if ($updated) {
		alert('Обновлено заказов: '.$updated);
	}
}

foreach ($linked as $i) {
	$plan[$i]['value'] = $plan[$i]['default'];
}

$config['plan'] = $plan;

?>