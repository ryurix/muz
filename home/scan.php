<?

$plan = [
	''=>['method'=>'POST'],
	'scan'=>['type'=>'line', 'id'=>'scan', 'class'=>'auto1'],
	'send'=>['type'=>'button', 'count'=>1, 1=>'Поиск', 'id'=>[1=>'send']],
//	'learn'=>['type'=>'checkbox', 'label'=>'Обучение'],
];

w('request', $plan);

$scan = trim($plan['scan']['value']);
$plan['scan']['value'] = '';

if (strlen($scan)) {

	$fields = ['store.i', 'store.code', 'store.url', 'store.pic', 'store.brand', 'store.name', 'store.model'];

	$fields2 = array_merge($fields, ['orst.i orst_i', 'orst.vendor', 'orst.state', 'orst.count', 'orst.info', 'orst.note']);

	$store = \Db::fetchRow(\Db::select($fields2, ['store', 'orst'], [
		'orst.mpi'=>$scan,
		'orst.store=store.i'
	]));

	if (is_array($store))
	{
		$_SESSION['scan'] = [
			'store' => $store['i'],
			'dt' => now()
		];
		$sound = 'success';
	} else {
		$store = \Db::fetchRow(\Db::select($fields, 'store', ['code LIKE "%,'.addslashes($scan).',%"']));

		if ((now() - ($_SESSION['scan']['dt'] ?? 0)) < 60) {
			if (is_array($store)) {
				if ($store['i'] == ($_SESSION['scan']['store'] ?? 0)) {
					$alert = '<div class="alert alert-success">Товар соответствует заказу.</div>';
					$sound = 'success';
				} else {
					$alert = '<div class="alert alert-danger">Товар не соответствует заказу!</div>';
					$sound = 'wrong';
				}
			} else {
				if (\Tool\Barcode::check($scan)) {
					$store = \Db::fetchRow(\Db::select($fields, 'store', ['i'=>\Flydom\Util\Clean::int($_SESSION['scan']['store'])]));
					$code = \Flydom\Cache::csvc_decode($store['code']);
					if (count($code)) {
						$alert = '<div class="alert alert-danger">У товара <a href="/store/'.$store['url'].'">'.$store['i'].'</a> уже есть штрихкод!</div>';
						$sound = 'wrong';
					} else {
						$code[] = $scan;
						$store['code'] = \Flydom\Cache::csvc_encode($code);
						\Db::update('store', [
							'code'=>$store['code']
						], ['i'=>$store['i']]);
						$alert = '<div class="alert alert-warning">Штрихкод '.$scan.' привязан к товару <a href="/store/'.$store['url'].'">'.$store['i'].'</a></div>';
						$sound = 'success';
					}
				} else {
					$alert = '<div class="alert alert-warning">Неправильный штрихкод: '.$scan.'</div>';
					$sound = 'wrong';
				}
			}
		} else {
			if (is_array($store)) {
				$sound = 'success';
			} else {
				$sound = 'wrong';
			}
		}

		$_SESSION['scan'] = [
			'store' => 0,
			'dt' => 0
		];
	}
} else {
	$store = null;
}

$result = '<!--'.$sound.'-->';

if (is_array($store)) {
	$brands = cache_load('brand');
	$config['name'] = ($brands[$store['brand']] ?? '').' '.$store['name'].' '.$store['model'];

//	echo '<h3>'.($brands[$store['brand']] ?? '').' '.$store['name'].' '.$store['model'].'</h3>';
	$result.= '<div class="row"><div class="col"><img src="'.$store['pic'].'"></div><div class="col">';
	$result.= $alert ?? '';
	if (isset($store['orst_i'])) {
		$state = w('order-state');
		$vendor = cache_load('vendor');
		$count_class = $store['count'] > 1 ? ' class="text-danger"' : '';
		$result.= '
<table class="table table-bordered"><tbody>
<tr><td>Заказ #</td><td><a href="/order/'.$store['orst_i'].'">'.$store['orst_i'].'</a></td></tr>
<tr><td>Статус</td><td>'.$state[$store['state']].'</td></tr>
<tr><td>Поставщик</td><td>'.$vendor[$store['vendor']].'</td></tr>
<tr><td'.$count_class.'>Количество</td><td'.$count_class.'>'.$store['count'].'</td></tr>
<tr><td>Штрихкод</td><td>'.trim($store['code'], ',').'</td></tr>
<tr><td>Комментарий</td><td>'.$store['info'].'</td></tr>
<tr><td>Замечания</td><td>'.$store['note'].'</td></tr>
</tbody></table>';
	} else {
		$result.= '
<table class="table table-bordered"><tbody>
<tr><td>Штрихкод</td><td>'.implode(', ', \Flydom\Cache::csvc_decode($store['code'])).'</td></tr>
</tbody></table>';
	}
	$result.= '</div></div>';
} else {
	if (strlen($scan)) {
		$result.= '<div class="alert alert-warning">Товар не найден: '.$scan.'</div>';
	}
}

if (isset($_REQUEST['design']) && $_REQUEST['design'] == 'none') {
	echo $result;
	exit();
}