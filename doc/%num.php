<?

function mop($one, $two, $op, $offset = 0, $length = 0) {
	if (!is_array($one)) {
		$one = array($one);
		$single = true;
	} else {
		$single = false;
	}
	if ($length == 0) { $length = count($one); }
	if (!is_array($two)) {
		$a = array();
		foreach ($one as $k=>$v) {
			$a[$k] = $two;
		}
		$two = $a;
	}
	$back = array();
	$count = 0;
	foreach ($one as $k=>$v) {
		$count++;
		if (($count <= $offset) || ($count > ($offset + $length))) { continue; }

		switch ($op) {
		case '=>': $back[$k] = isset($two[$v]) ? $two[$v] : ''; break;
		case 'empty': if (strlen($v)) { $back[$k] = $v; } break;
		case '&': $back[$k] = htmlspecialchars($v, ENT_NOQUOTES); break;
		case '*': $back[$k] = $v * $two[$k]; break;
		case '/': $back[$k] = $v / $two[$k]; break;
		case '+': $back[$k] = $v + $two[$k]; break;
		case '-': $back[$k] = $v - $two[$k]; break;
		case '%': $back[$k] = $v * $two[$k] / 100; break;
		case 'round': $back[$k] = round($v, $two[$k]); break;
		case 'floor': $back[$k] = floor($v * pow(10, $two[$k])) / pow(10, $two[$k]); break;
		case 'format': $back[$k] = number_format($v, $two[$k], ',', ' '); break;
		case 'summa': return array_sum(array_slice($one, $offset, $length))
			+ array_sum(array_slice($two, $offset, $length)); break;
		}
	}
	return $single ? $back[0] : $back;
}

$key = isset($config['args'][0]) ? $config['args'][0] : 0;

w('ft');
w('clean');
w('rub2str');

$q = db_query('SELECT * FROM docs WHERE i='.$key.(is_user('doc') || is_user('auto') ? '' : ' AND user='.$_SESSION['i']));
if ($i = db_fetch($q)) {

	$templates = \Type\Doc::FILES;
	$template = $templates[$i['type']];
	$file = $config['root'].'files/docs/'.str2url($i['name']).'.xlsx';

	$data = php_decode($i['data']);
	$data['names'] = mop($data['names'], 0, '&');
	$data['note'] = mop($data['note'], 0, '&');

	$count = $data['count'];
	$pricem = mop($data['price'], 100/120, '*');
//	$pricem = mop($pricem, 2, 'round');
	$summam = mop($pricem, $count, '*');
	$totalm = mop($summam, 0, 'summa');
	$summan = mop($data['summa'], $summam, '-');
	$totaln = mop($summan, 0, 'summa');
	$counts = mop($count, 0, 'summa');

	$summa = mop($data['price'], $count, '*');
	$total = mop($summa, 0, 'summa');

	$rows = count($data['#']);

	w('clean');
	if (is_php_encoded($data['upay'])) {
		$upay = php_decode($data['upay']);
	} else {
		$upay = array_decode($data['upay']);
	}

	$upay = $upay + array(
		'ptype'=>'',
		'uname'=>'',
		'head'=>'',
		'uadr'=>'',
		'fadr'=>'',
		'inn'=>'',
		'kpp'=>'',
		'okpo'=>'',
		'bank'=>'',
		'bik'=>'',
		'bras'=>'',
		'bkor'=>'',
		'other'=>'',
		'line'=>'',
	);


	if ($data['staff'] == $data['user']) {
		$data['user'] = '';
		$data['adres'] = '';

		$upay['user'] = '';
		$upay['city'] = '';
		$upay['adres'] = '';
	}

//	Multipage

	$pages = 1;
	$p1 = 0;
	$p2 = 0;
	$p3 = 0;

	switch ($template) {
	case 'nak_s': case 'nak_su':
	case 'nak_k': case 'nak_ku': $p1 = 10; $p2 = 20; break;
	case 's-f_s': case 's-f_su': $p1 = 16; $p2 = 23; break;
	case 'upd_t': case 'upd_tu': $p1 = 16; $p2 = 14; $p3 = 14; break;
	default: $p1 = 0; $p2 = 0; break;
	}

	if ($p1) {
		if ($rows > ($p1 + $p2)) {
			$template.= '-3';
			if ($p3) {
				$p2 = $rows - $p1 - $p3;
			}
		} else {
			if ($rows > $p1) { $template.= '-2'; }
		}
	}

//	Calculations

	$data['money'] =  mop(
		isset($data['money']) ? $data['money'] : array(0),
		isset($data['money2']) ? $data['money2'] : array(0), '+');
	$totalb = mop($data['money'], 0, 'summa');
	$totalp = $data['total'] - $totalb;

	$data = array(
		'_encoding'=>'utf-8',
		'_template'=>$config['root'].'doc/template/'.$template.'.xlsx',
		'_filename'=>$file,

		'money'=>mop($data['money'], 2, 'format'),
		'pricem'=>mop($pricem, 2, 'format'),
		'summam'=>mop($summam, 2, 'format'),
		'totalm'=>mop($totalm, 2, 'format'),
		'summan'=>mop($summan, 2, 'format'),
		'totaln'=>mop($totaln, 2, 'format'),
		'total'=>mop($data['total'], 2, 'format'),
		'totalp'=>mop($totalp, 2, 'format'),
		'totalb'=>mop($totalb, 2, 'format'),
		'counts'=>mop($counts, 0, 'format'),

		'total_rus'=>rub2str($data['total'], 1),
		'totalb_rus'=>rub2str($totalb, 1),
		'totalp_rus'=>rub2str($totalp, 1),
		'amount_rus'=>rub2str($data['amount'], 0, array()),
		'rows'=>$rows,
		'rows_rus'=>rub2str($rows, 0, array()),
		'price'=>mop($data['price'], 2, 'format'),
		'summa'=>mop($data['summa'], 2, 'format'),
		'dt'=>ft($data['dt']),
		'dt2'=>ft($data['dt'], 1),

		'day'=>date('j', $data['dt']),
		'month'=>ft_month(date('n', $data['dt']), 'ya'),
		'year'=>date('Y', $data['dt']),

		'##'=>$data['#'],
		'name2'=>$data['names'],
		'count2'=>$count,
		'price2'=>mop($data['price'], 2, 'format'),
		'summa2'=>mop($data['summa'], 2, 'format'),
	) + $data + $upay;

//

	$data['#1'] = array_slice($data['#'], 0, $p1);
	$data['#2'] = array_slice($data['#'], $p1, $p2);
	$data['#3'] = array_slice($data['#'], $p1+$p2, $rows);

	$data['store1'] = array_slice($data['store'], 0, $p1);
	$data['store2'] = array_slice($data['store'], $p1, $p2);
	$data['store3'] = array_slice($data['store'], $p1+$p2, $rows);
	$data['names1'] = array_slice($data['names'], 0, $p1);
	$data['names2'] = array_slice($data['names'], $p1, $p2);
	$data['names3'] = array_slice($data['names'], $p1+$p2, $rows);
	$data['counts1'] = array_slice($count, 0, $p1);
	$data['counts2'] = array_slice($count, $p1, $p2);
	$data['counts3'] = array_slice($count, $p1+$p2, $rows);
	$data['pricem1'] = mop($pricem, 2, 'format', 0, $p1);
	$data['pricem2'] = mop($pricem, 2, 'format', $p1, $p2);
	$data['pricem3'] = mop($pricem, 2, 'format', $p1+$p2, $rows);
	$data['prices1'] = array_slice($data['price'], 0, $p1);
	$data['prices2'] = array_slice($data['price'], $p1, $p2);
	$data['prices3'] = array_slice($data['price'], $p1+$p2, $rows);
	$data['summam1'] = mop($summam, 2, 'format', 0, $p1);
	$data['summam2'] = mop($summam, 2, 'format', $p1, $p2);
	$data['summam3'] = mop($summam, 2, 'format', $p1+$p2, $rows);
	$data['summan1'] = mop($summan, 2, 'format', 0, $p1);
	$data['summan2'] = mop($summan, 2, 'format', $p1, $p2);
	$data['summan3'] = mop($summan, 2, 'format', $p1+$p2, $rows);
	$data['summas1'] = array_slice($data['summa'], 0, $p1);
	$data['summas2'] = array_slice($data['summa'], $p1, $p2);
	$data['summas3'] = array_slice($data['summa'], $p1+$p2, $rows);
	$data['summaz1'] = $data['summas1'];
	$data['summaz2'] = $data['summas2'];
	$data['summaz3'] = $data['summas3'];

	$data['totalc1'] = mop($count, 0, 'summa', 0, $p1);
	$data['totalc2'] = mop($count, 0, 'summa', $p1, $p2);
	$data['totalc3'] = mop($count, 0, 'summa', $p1+$p2, $rows);

	$data['totalm1'] = mop(mop($summam, 0, 'summa', 0, $p1), 2, 'format');
	$data['totalm2'] = mop(mop($summam, 0, 'summa', $p1, $p2), 2, 'format');
	$data['totalm3'] = mop(mop($summam, 0, 'summa', $p1+$p2, $rows), 2, 'format');

	$data['totaln1'] = mop(mop($summan, 0, 'summa', 0, $p1), 2, 'format');
	$data['totaln2'] = mop(mop($summan, 0, 'summa', $p1, $p2), 2, 'format');
	$data['totaln3'] = mop(mop($summan, 0, 'summa', $p1+$p2, $rows), 2, 'format');

	$data['total1'] = mop(mop($summa, 0, 'summa', 0, $p1), 2, 'format');
	$data['total2'] = mop(mop($summa, 0, 'summa', $p1, $p2), 2, 'format');
	$data['total3'] = mop(mop($summa, 0, 'summa', $p1+$p2, $rows), 2, 'format');

//	Store data
/*
	$data['sname'] = array();
	$data['sbrand'] = array();
	$data['smodel'] = array();

	$brand = cache_load('brand');
	if (isset($data['store'])
	foreach ($data['store'] as $store) {
		$q = db_query('SELECT * FROM store WHERE i='.$store);
		if ($s = db_fetch($q)) {
			$data['sname'][] = $s['name'];
			$data['sbrand'][] = $brand[$s['brand']];
			$data['smodel'][] = $s['model'];
		} else {
			$data['sname'][] = $data['name'][count($data['sname'])];
			$data['sbrand'][] = '';
			$data['smodel'][] = '';
		}
	}
*/
	$ptype = w('list-pay-user');
	$data['ptype'] = isset($ptype[$upay['ptype']]) ? $ptype[$upay['ptype']] : '';

	$dtype = w('list-dost');
	$dost = mop(isset($data['dost']) ? $data['dost'] : array(''), $dtype, '=>');
	$dost = array_unique($dost);
	$dost = mop($dost, 0, 'empty');
	$data['dost'] = implode(', ', $dost);

	$vendor = cache_load('vendor');
	if (isset($data['vendor'])) {
		$data['vendor'] = mop($data['vendor'], $vendor, '=>');
		$data['vendors'] = implode(' ,', $data['vendor']);
	} else {
		$data['vendor'] = array();
		$data['vendors'] = '';
	}

	$city = cache_load('city2');
	$data['shop'] = isset($city[$_SESSION['cire']]) ? $city[$_SESSION['cire']]['sign'] : '';

	if (isset($data['info']) && is_array($data['info'])) {
		$comment = array();
		foreach ($data['info'] as $k=>$v) {
			if (strlen($v)) {
				$comment[] = strip_tags($v);
			}
			if (isset($data['note'][$k])) {
				$v = $data['note'][$k];
				if (strlen($v)) {
					$comment[] = strip_tags($v);
				}
			}
		}
		$data['comment'] = implode(', ', $comment);
	} else {
		$data['comment'] = '';
	}

	$config['row'] = $data;
	w('f-doc', $data);

//	cache_save('f-doc', $data);
//	print_pre($data);
//*
	if (file_exists($file)) {
//		ini_set("zlib.output_compression", "Off");
//		redirect('/files/docs/'.basename($file));
//*
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
//*/
	}
//*/
} else {
	redirect('/');
}

?>