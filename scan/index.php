<?

\Form\Barcode::start();

define('NO_BARCODE', 'NO-BARCODE');


$plan = [
	''=>['method'=>'POST'],
	'scan'=>['type'=>'line', 'id'=>'scan', 'class'=>'auto1'],
	'send'=>['type'=>'button', 'count'=>1, 1=>'Поиск', 'id'=>[1=>'send']],
//	'learn'=>['type'=>'checkbox', 'label'=>'Обучение'],
];

w('request', $plan);

$scan = trim($plan['scan']['value']);
$plan['scan']['value'] = '';

if (isset($_REQUEST['pack'])) {
	$scan = NO_BARCODE;
}

if (strlen($scan)) {

	$fields = ['store.i', 'store.code', 'store.url', 'store.pic', 'store.brand', 'store.name', 'store.model'];

	$fields2 = array_merge($fields, ['orst.i orst_i', 'orst.vendor', 'orst.state', 'orst.count', 'orst.info', 'orst.note', 'orst.last', 'orst.complex']);

	$store = \Db::fetchRow(\Db::select($fields2, ['store', 'orst'], [
		'orst.mpi'=>$scan,
		'orst.store=store.i'
	], 'ORDER BY orst.state'));

	if (is_array($store)) // это заказ
	{
		$_SESSION['scan'] = [
			'store' => $store['i'],
			'orst'=>$store['orst_i'],
			'mpi'=>$scan,
			'dt' => now()
		];

		if ($store['state'] == 27) {
			$sound = 'state27';
		} elseif ($store['state'] == 1) {
			$sound = 'first';
		} elseif ($store['state'] == 2 || $store['state'] == 3) {
			$sound = 'state2-3';
		} elseif ($store['state'] == 35) {
			$sound = 'cancel';
		} else {
			$complex = $store['complex'] ? \Db::result(\Db::select('COUNT(*)', 'complex', ['up'=>$store['complex']], 'GROUP BY up')) : 0;

			if ($complex > 1) {
				$sound = 'complex2';
			} else {
				$sklad = array_keys(w('list-sklad'));
				if (in_array($store['vendor'], $sklad)) {
					$sound = $store['count'] > 1 ? 'sklad2' : 'sklad';
				} else {
					if ($store['state'] == 7) {
						$sound = 'state7';
					} else {
						$sound = $store['count'] > 1 ? 'info2' : 'info';
					}
				}
			}
		}
	} else { // это товар
		if ($scan == NO_BARCODE) {
			$where = ['store.i'=>$_SESSION['scan']['store'] ?? 0];
		} else {
			$where = ['store.code LIKE "%,'.addslashes($scan).',%"'];
		}

		$mpi = $_SESSION['scan']['mpi'] ?? '';
		if (!empty($mpi)) {
			$where['orst.mpi'] = $mpi;
			$store = \Db::fetchRow(\Db::select($fields2, 'store LEFT JOIN orst ON store.i=orst.store', $where, 'ORDER BY orst.state'));
		} else {
			$store = \Db::fetchRow(\Db::select($fields, 'store', $where));
		}

		if ((now() - ($_SESSION['scan']['dt'] ?? 0)) < 60) {
			if (is_array($store)) { // это товар в заказе
				if (!empty($store['orst_i'])) {
					$sound = 'success';
					$order = new \Order\Model($store['orst_i']);
					if ($order->getState() < 27) {
						$order->setState(27);
						$order->save();
						$alert = '<div class="alert alert-success">Заказ собран!</div>';
					} else {
						$alert = '<div class="alert alert-success">Заказ уже собран.</div>';
					}
				} else {
					$alert = '<div class="alert alert-danger">Товар не соответствует заказу!</div>';
					$sound = 'wrong';
				}
			} else {
				if (\Tool\Barcode::check($scan)) {
					$store = \Db::fetchRow(\Db::select($fields, 'store', ['i'=>\Flydom\Clean::int($_SESSION['scan']['store'])]));
					$code = \Flydom\Arrau::decodec($store['code']);
					if (count($code)) {
						$alert = '<div class="alert alert-danger">У товара <a href="/store/'.$store['url'].'">'.$store['i'].'</a> уже есть штрихкод!</div>';
						$sound = 'wrong';
					} else {
						$code[] = $scan;
						$store['code'] = \Flydom\Arrau::encodec($code);
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

	$code = implode(', ', \Flydom\Arrau::decodec($store['code']));
	$barcode = \Form\Barcode::button('Штрихкод', 'scan', 'btn btn-default btn-sm');

//	echo '<h3>'.($brands[$store['brand']] ?? '').' '.$store['name'].' '.$store['model'].'</h3>';
	$result.= '<div class="row"><div class="col"><img src="'.$store['pic'].'" class="img-fluid"></div><div class="col">';
	$result.= $alert ?? '';
	if (isset($store['orst_i']))
	{
		$pack = $store['state'] < 27 ? '<button name="pack" class="btn btn-default btn-sm" id="pack">Собрать</button>' : '';

		$state = w('order-state');
		$vendor = cache_load('vendor');
		$count_class = $store['count'] > 1 ? ' class="text-danger"' : '';
		$result.= '
<table class="table table-bordered"><tbody>
<tr><td>Название</td><td>'.$name.'</td></tr>
<tr><td>Заказ #</td><td><a href="/order/'.$store['orst_i'].'">'.$store['orst_i'].'</a></td></tr>
<tr><td>Обновлён</td><td>'.\Flydom\Time::dateTime($store['last']).'</td></tr>
<tr><td>Статус</td><td>'.$state[$store['state']].' '.$pack.'</td></tr>
<tr><td>Поставщик</td><td>'.$vendor[$store['vendor']].'</td></tr>
<tr><td'.$count_class.'>Количество</td><td'.$count_class.'>'.$store['count'].'</td></tr>
<tr><td>'.$barcode.'</td><td>'.$code.'</td></tr>
<tr><td>Комментарий</td><td>'.$store['info'].'</td></tr>
<tr><td>Замечания</td><td>'.$store['note'].'</td></tr>
</tbody></table>';
	}
	else
	{
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
	} else {
		$result.= $alert ?? '';
	}
}

if (isset($_REQUEST['design']) && $_REQUEST['design'] == 'none') {
	echo $result;
	exit();
}