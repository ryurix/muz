<?

set_time_limit(0);

$q = db_query('SELECT * FROM cron WHERE typ=1 AND every>0 AND dt<'.now().' ORDER BY dt LIMIT 1'); //
while ($row = db_fetch($q)) {
	$data = array_decode($row['data']);
	$count = w('yml', $data);

	if ($row['every'] == 1) {
		w('cron-tools');
		$next = cron_next(now(), $data);
	} else {
		$next = now() + $row['every'];
	}

	w('ft');
	db_update('cron', array(
		'dt'=>$next,
		'info'=>'<a href="/files/'.$data['filename'].'">'.$data['filename'].'</a> ('.$count.') '.ft(now(), 1),
	), array('i'=>$row['i']));
}

$q = db_query('SELECT * FROM cron WHERE typ=10 AND every>0 AND dt<'.now().' ORDER BY dt LIMIT 1'); //
while ($row = db_fetch($q)) {
	$data = array_decode($row['data']);

	if ($row['every'] == 1) {
		w('cron-tools');
		$next = cron_next(now(), $data);
	} else {
		$next = now() + $row['every'];
	}

	$count = w('ozon', $data);

	w('ft');
	db_update('cron', array(
		'dt'=>$next,
		'info'=>'('.$count.') '.ft(now(), 1),
	), array('i'=>$row['i']));
}

$q = db_query('SELECT * FROM cron WHERE typ=20 AND every>0 AND dt<'.now().' ORDER BY dt LIMIT 1'); //
while ($row = db_fetch($q)) {
	$data = array_decode($row['data']);

	if ($row['every'] == 1) {
		w('cron-tools');
		$next = cron_next(now(), $data);
	} else {
		$next = now() + $row['every'];
	}

	$count = w('wildberries', $data);

	w('ft');
	db_update('cron', array(
		'dt'=>$next,
		'info'=>'('.$count.') '.ft(now(), 1),
	), array('i'=>$row['i']));
}
