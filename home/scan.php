<?

$plan = [
	''=>['class'=>'auto', 'method'=>'POST'],
	'scan'=>['type'=>'line', 'class'=>'auto', 'id'=>'scan'],
	'code'=>['type'=>'hidden'],
	'store'=>['type'=>'hidden', 'default'=>0],
	'dt'=>['type'=>'hidden', 'default'=>0],
	'send'=>['type'=>'button', 'count'=>1, 1=>'Поиск'],
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
		$plan['store']['value'] = $store['i'];
		$plan['dt']['value'] = now();


//		$store = \Db::fetchRow(\Db::select($fields, 'store', ['i'=>\Flydom\Util\Clean::int($scan)]));


	} else {
		$store = \Db::fetchRow(\Db::select($fields, 'store', ['code LIKE "%,'.addslashes($scan).',%"']));

		if ((now() - $plan['dt']['value']) < 60) {
			if (is_array($store)) {
				if ($store['i'] == $plan['store']['value']) {
					$alert = '<div class="alert alert-success">Товар соответствует заказу.</div>';
					$sound = 'success.mp3';
				} else {
					$alert = '<div class="alert alert-danger">Товар не соответствует заказу!</div>';
					$sound = 'wrong.wav';
				}
			} else {
				if (\Tool\Barcode::check($scan)) {
					$store = \Db::fetchRow(\Db::select($fields, 'store', ['i'=>\Flydom\Util\Clean::int($plan['store']['value'])]));
					$code = \Flydom\Cache::csvc_decode($store['code']);
					if (count($code)) {
						$alert = '<div class="alert alert-danger">У товара <a href="/store/'.$store['url'].'">'.$store['i'].'</a> уже есть штрихкод!</div>';
					} else {
						$code[] = $scan;
						$store['code'] = \Flydom\Cache::csvc_encode($code);
						\Db::update('store', [
							'code'=>$store['code']
						], ['i'=>$store['i']]);
						$alert = '<div class="alert alert-warning">Штрихкод '.$scan.' привязан к товару <a href="/store/'.$store['url'].'">'.$store['i'].'</a></div>';
					}
				} else {
					$alert = '<div class="alert alert-warning">Неправильный штрихкод: '.$scan.'</div>';
				}
			}
		}

		$plan['store']['value'] = 0;
		$plan['dt']['value'] = 0;
	}

} else {
	$store = null;
}

if (is_array($store)) {
	$brands = cache_load('brand');
	$config['name'] = ($brands[$store['brand']] ?? '').' '.$store['name'].' '.$store['model'];
}