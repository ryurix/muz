<?

$clients = [];
foreach ($config['wildberries'] as $k=>$v) {
	$clients[$k] = $v['name'];
}

$plan = array(
	''=>array('method'=>'POST'),
    'client'=>['name'=>'Клиент', 'type'=>'combo', 'values'=>$clients],
	'file'=>array('name'=>'Файл номенклатуры', 'type'=>'file0', 'default'=>''),
	'clear'=>array('label'=>'Удалить синхронизации не указанные в файле', 'type'=>'checkbox'),
	'send'=>array('type'=>'button', 'count'=>1, 1=>'Загрузить номенклатуру'),
);

w('request', $plan);

$config['plan'] = $plan;

$rows = [];

if ($plan['send']['value'] == 1) {

	$wb = db_fetch_all('SELECT * FROM wb WHERE client='.$plan['client']['value'], 'chrt');

	w('phpexcel');
	$rows = phpexcel($plan['file']['value'], $plan['file']['filename']);
	unset($rows[0]);
	foreach ($rows as $i) {
		$chrt = $i[2];

		$delim = mb_substr($i[3], 0, 1);
        $store = explode($delim, $i[3])[1] ?? 0;
        if (!$store) {
        	continue;
        }

		if (isset($wb[$chrt])) {
			if ($wb[$chrt]['i'] != $i[4]
			|| $wb[$chrt]['price'] != $i[7]
			|| $wb[$chrt]['barcode'] != $i[6]
			|| $wb[$chrt]['store'] != $store) {
				db_update('wb', [
					'i'=>$i[4],
					'price'=>$i[7],
					'barcode'=>$i[6],
					'store'=>$store,
				], [
					'chrt'=>$chrt,
					'client'=>$plan['client']['value']
				]);
			}
			unset($wb[$chrt]);
		} else {
			db_insert('wb', [
				'i'=>$i[4],
				'dt'=>now(),
				'chrt'=>$chrt,
				'barcode'=>$i[6],
				'store'=>$store,
				'usr'=>0,
				'price'=>$i[7],
				'quantity'=>0,
				'client'=>$plan['client']['value'],
			]);
		}
	}
	if (count($wb) && $plan['clear']['value']) {
		db_query('DELETE FROM wb WHERE chrt IN ('.implode(',', array_keys($wb)).')');
		alert('Удалено '.count($wb).' синхронизаций wildberries.');
	}
}

$config['rows'] = $rows;