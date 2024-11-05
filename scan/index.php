<?

\Form\Barcode::start();

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

	$fields2 = array_merge($fields, ['orst.i orst_i', 'orst.vendor', 'orst.state', 'orst.count', 'orst.info', 'orst.note', 'orst.last']);

	$store = \Db::fetchRow(\Db::select($fields2, ['store', 'orst'], [
		'orst.mpi'=>$scan,
		'orst.store=store.i'
	]));

	if (is_array($store)) // это заказ
	{
		$_SESSION['scan'] = [
			'store' => $store['i'],
			'orst'=>$store['orst_i'],
			'dt' => now()
		];
		$sklad = array_keys(w('list-sklad'));
		if (in_array($store['vendor'], $sklad)) {
			$sound = $store['count'] > 1 ? 'sklad2' : 'sklad';
		} else {
			$sound = $store['count'] > 1 ? 'info2' : 'info';
		}
	} else { // это товар
		$store = \Db::fetchRow(\Db::select($fields, 'store', ['code LIKE "%,'.addslashes($scan).',%"']));

		if ((now() - ($_SESSION['scan']['dt'] ?? 0)) < 60) {
			if (is_array($store)) { // это товар в заказе
				if ($store['i'] == ($_SESSION['scan']['store'] ?? 0)) {
					$alert = '<div class="alert alert-success">Товар соответствует заказу.</div>';
					$sound = 'success';
					$orst = $_SESSION['scan']['orst'] ?? 0;
					if ($orst) {
						$order = new \Model\Order($orst);
						$order->setState(27);
						$order->save();
					}
				} else {
					$alert = '<div class="alert alert-danger">Товар не соответствует заказу!</div>';
					$sound = 'wrong';
				}
			} else {
				if (\Tool\Barcode::check($scan)) {
					$store = \Db::fetchRow(\Db::select($fields, 'store', ['i'=>\Flydom\Clean::int($_SESSION['scan']['store'])]));
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
						$sound = 'new';
					}
				} else {
					$alert = '<div class="alert alert-danger">Неправильный штрихкод: '.$scan.'</div>';
					$sound = 'wrong';
				}
			}
		} else {
			if (is_array($store)) {
				$sound = 'info';
			} else {
				$sound = 'wrong';
			}
		}

		$_SESSION['scan'] = [
			'store' => $store['i'] ?? 0,
			'dt' => 0
		];
	}
} else {
	$store = null;
}

$result = '<!--'.($sound ?? '').'-->';

if (is_array($store)) {
	$brands = cache_load('brand');
	$name = ($brands[$store['brand']] ?? '').' '.$store['name'].' '.$store['model'];

	$code = implode(', ', \Flydom\Cache::csvc_decode($store['code']));
	$barcode = \Form\Barcode::button('Штрихкод', 'scan', 'btn btn-default btn-sm');

//	echo '<h3>'.($brands[$store['brand']] ?? '').' '.$store['name'].' '.$store['model'].'</h3>';
	$result.= '<div class="row"><div class="col"><img src="'.$store['pic'].'" class="img-fluid"></div><div class="col">';
	$result.= $alert ?? '';
	if (isset($store['orst_i'])) {
		$state = w('order-state');
		$vendor = cache_load('vendor');
		$count_class = $store['count'] > 1 ? ' class="text-danger"' : '';
		$result.= '
<table class="table table-bordered"><tbody>
<tr><td>Название</td><td>'.$name.'</td></tr>
<tr><td>Заказ #</td><td><a href="/order/'.$store['orst_i'].'">'.$store['orst_i'].'</a></td></tr>
<tr><td>Обновлён</td><td>'.\Flydom\Time::dateTime($store['last']).'</td></tr>
<tr><td>Статус</td><td>'.$state[$store['state']].'</td></tr>
<tr><td>Поставщик</td><td>'.$vendor[$store['vendor']].'</td></tr>
<tr><td'.$count_class.'>Количество</td><td'.$count_class.'>'.$store['count'].'</td></tr>
<tr><td>'.$barcode.'</td><td>'.$code.'</td></tr>
<tr><td>Комментарий</td><td>'.$store['info'].'</td></tr>
<tr><td>Замечания</td><td>'.$store['note'].'</td></tr>
</tbody></table>';
	} else {
		$result.= '
<table class="table table-bordered"><tbody>
<tr><td>Название</td><td>'.$name.'</td></tr>
<tr><td>'.$barcode.'</td><td>'.$code.'</td></tr>
</tbody></table>';
	}
	$result.= '</div></div>';
} else {
	if (strlen($scan)) {
		$result.= '<div class="alert alert-warning">Товар не найден: '.$scan.'</div>';
		if (!\Tool\Barcode::check($scan)) {
			$result.= '<div class="alert alert-danger">Неправильный штрихкод: '.$scan.'</div>';
		}
	}
}

if (isset($_REQUEST['design']) && $_REQUEST['design'] == 'none') {
	echo $result;
	exit();
}