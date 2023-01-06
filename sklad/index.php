<?

$sklad = w('list-sklad');
$sk = isset($_REQUEST['sk']) ? $_REQUEST['sk'] : '';
if (!isset($sklad[$sk])) {
	refile('menu.html');
	return;
}

refile('sklad-action.html', 'sklad-action');
w('sklad-action', $sk);

$cuts = array(0=>'Все', 1=>'В плане', 2=>'В наличии');
$grps = array(0=>'Все', 1=>'По производителю');

$ups = w('catalog-all');
$vendor = array(0=>'') + cache_load('vendor');

$plan = array(
	''=>array('method'=>'POST', 'close'=>FALSE),
	'cut'=>array('name'=>'Фильтр', 'type'=>'combo', 'values'=>$cuts, 'default'=>0, 'width'=>400),
	'grp'=>array('name'=>'Группировка', 'type'=>'combo', 'values'=>$grps, 'default'=>0, 'width'=>400),
	'up'=>array('name'=>'Раздел', 'type'=>'combo2', 'values'=>$ups, 'width'=>400, 'placeholder'=>'Фильтр по разделу'),
	'vendor'=>array('name'=>'Поставщик', 'type'=>'combo', 'values'=>$vendor, 'default'=>0, 'width'=>400),
	'send'=>array('name'=>'', 'type'=>'submit', 'value'=>'Показать'),
);

// , 'count'=>4, 1=>'Показать', 2=>'Приходные', 3=>'Расходные', 4=>'Переоценки', 'class'=>array(1=>'btn-default', 2=>'btn-info', 3=>'btn-warning', 4=>'btn-success')

w('request', $plan);
$config['plan'] = $plan;
$config['sklad'] = $sk;

$osts = cache_load('ost');
if(!is_array($osts)) {
	$osts = array();
}

$config['name'] = $sklad[$sk];

w('clean');

if (isset($_REQUEST['prices'])) {
	if (is_array($_REQUEST['i']) && is_array($_REQUEST['p'])) {
		w('clean');
		$new = array();
		$ost = array();
		foreach ($_REQUEST['i'] as $k=>$v) {
			$new[$v] = clean_09($_REQUEST['p'][$k]);
			$ost[$v] = clean_09($_REQUEST['o'][$k]);
		}

		$rows = array();
		$q = db_query('SELECT i,price,store FROM sync WHERE vendor='.$sk);
		while ($i = db_fetch($q)) {
			if (isset($new[$i['i']]) && $new[$i['i']] != $i['price']) {
				$rows[$i['i']] = array(
					'new'=>$new[$i['i']],
					'old'=>$i['price'],
					'store'=>$i['store'],
					'sync'=>$i['i'],
				);
			}
			
			if (isset($ost[$i['i']])) {
				$code = $sk.':'.$i['store'];
				$val = $ost[$i['i']];
				if ($val == '0') {
					$val = '';
				}
				if (strlen($val)) {
					$osts[$code] = $val;
				} else {
					unset($osts[$code]);
				}
			}
		}
		db_close($q);

		cache_save('ost', $osts);

		if (count($rows)) {
			db_insert('naklad', array(
				'dt'=>now(),
				'user'=>$_SESSION['i'],
				'vendor'=>$sk,
				'type'=>2,
				'info'=>$_REQUEST['info'],
			));

			$naklad = db_insert_id();

			foreach ($rows as $k=>$i) {
				db_insert('nakst', array(
					'naklad'=>$naklad,
					'store'=>$i['store'],
					'count'=>$i['old'],
					'price'=>$i['new'],
				));

				db_update('sync', array('price'=>$i['new'], 'dt'=>1893445200), array('i'=>$k));
			}

			redirect('/sklad/'.$naklad);
		}
	}
}

$config['ost'] = $osts;

?>