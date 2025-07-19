<?

$key = isset($config['args'][0]) ? $config['args'][0] : 0;

$where = array();
$where[] = 'i='.$key;

$q = db_query('SELECT * FROM kkm WHERE '.implode(' AND ', $where));

if (!$row = db_fetch($q)) {
	alert('Чек #'.$key.' не найден!');
	redirect('/kkm');
}

$plan = array(
	'state'=>array('type'=>'combo', 'values'=>w('list-kkm'), 'width'=>300, 'readonly'=>1),
	'dt'=>array('name'=>'Создан', 'type'=>'datetime', 'step'=>1, 'readonly'=>1),
	'dt2'=>array('name'=>'Обработан', 'type'=>'datetime', 'step'=>1, 'readonly'=>1),
	'total'=>array('name'=>'Сумма', 'type'=>'money', 'readonly'=>1),
	'send'=>array('type'=>'button', 'count'=>array(1,2), 1=>'Обработать', 2=>'Отменить', 3=>'Печатать', 'class'=>array(2=>'btn-danger', 3=>'btn-success')),
);

$plan['']['default'] = $row;

if ($row['state'] == 10) { $plan['send']['count'] = array(3); }
if ($row['state'] > 10) { $plan['send']['count'] = 0; }

w('request', $plan);

if (!$plan['dt2']['value']) {
	w('input-hidden');
	$plan['dt2']['type'] = 'hidden';
}

if ($plan['state']['value'] < 10) {
	if ($plan['send']['value'] == 1) {
		// печать чека или фискализация без печати
		w('kkmserver');
		$state = kkm_fix($row['i']);
		redirect('/kkm/'.$row['i']);
	}

	if ($plan['send']['value'] == 2) {
		// отменяем задание на обработку
		db_update('kkm', array('state'=>20, 'dt2'=>now()), array('i'=>$row['i']));
		redirect('/kkm/'.$row['i']);
	}
}

if ($plan['send']['value'] == 3) {
	w('ft');
	$name = 'Фискальный чек '.$row['i'].' от '.ft($row['dt']);
	w('clean');
	$file = $config['root'].'files/docs/'.str2url($name).'.xlsx';

	$data = php_decode($row['data']);
	$data['_encoding'] = 'utf-8';
	$data['_template'] = $config['root'].'doc/template/kkm.xlsx';
	$data['_filename'] = $file;

	w('f-doc', $data);

	$zip = new ZipArchive;
	$imagefile = 'xl/media/image1.png';
	if (isset($data['QRCode'])) {
		if ($zip->open($file) === TRUE) {
			if ($zip->locateName($imagefile) !== false) {
				include_once $config['root'].'--/block/phpqrcode/qrlib.php';
				ob_start();
				QRcode::png($data['QRCode'], false, 'L', 4, 2);
				$png = ob_get_contents();
				ob_end_clean();

				$zip->deleteName($imagefile);
				$zip->addFromString($imagefile, $png);
			}

			$zip->close();
		}
	}

	header('Content-Disposition: attachment; filename='.basename($file));
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
//		header('Content-Length: ' . sprintf('%u', filesize($file)));

	readfile($file);
	flush();
	unlink($file);

	exit();
}
