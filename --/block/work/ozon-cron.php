<?

//	https://cb-api.ozonru.me/apiref/ru/#t-fbs_list

w('ft');
w('clean');

function ozon_query($ozon, $url, $args) {
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://api-seller.ozon.ru'.$url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Client-Id: '.$ozon['client'],
		'Api-Key: '.$ozon['api'],
		'Content-Type: application/json',
	));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$result = curl_exec($ch);
	curl_close($ch);

    return json_decode($result, true);
}

$limit = 10;

foreach ($config['ozon'] as $user=>$ozon) {

    $result = [];
	$offset = 0;

    do {
        $new = ozon_query($ozon, '/v3/posting/fbs/unfulfilled/list', [
			'dir'=>'asc',
			'filter'=>[
				'cutoff_from'=>date('c', now() - 3*24*60*60),
				'cutoff_from'=>date('c', now()),
				'status'=>'awaiting_packaging'
			],
			'limit'=>$limit,
			'offset'=>$offset,
		]);

		$offset+= $limit;

		if (!isset($new['result'])) {
			w('log');
			logs(355, $user, json_encode($new));
			break;
		}

		$found = count($new['result']['postings']);
		$result = array_merge($result, $new['result']['postings']);
	} while ($found == $limit);

	$mark = db_get_row('SELECT mark,mark2 FROM user WHERE i='.$user);
	$mark = $mark ? ','.trim($mark['mark'].','.$mark['mark2'], ',').',' : '';

    foreach ($result as $order) {

        foreach ($order['products'] as $item) {
            $store = clean_09($item['offer_id']);

			$where = [
				'mpi="'.addslashes($order['posting_number']).'"',
				'user='.$user,
			];
			if ($store) {
				$where[] = 'store='.$store;
			}

            $exists = db_result('SELECT COUNT(*) FROM orst WHERE '.implode(' AND ', $where));
            if ($exists) { continue; }

            db_insert('orst', [
                'dt'=>now(),
                'last'=>now(),
                'user'=>$user,
                'staff'=>null,
                'state'=>1,
                'cire'=>34,
                'city'=>'', // Адрес?
                'lat'=>null,
                'lon'=>null,
                'adres'=>'',
                'dost'=>'self',
                'vendor'=>0,
                'store'=>$store,
                'name'=>$item['name'],
                'price'=>$item['price'],
                'count'=>$item['quantity'],
                'money0'=>0,
                'pay'=>0,
                'money'=>0,
                'pay2'=>0,
                'money2'=>0,
                'bill'=>null,
                'sale'=>null,
                'info'=>'', // Примечание?
                'note'=>count($order['products']) > 1 ? 'парный заказ' : '',
                'docs'=>null,
                'files'=>null,
                'mark'=>$mark,
                'kkm'=>0,
                'kkm2'=>0,
                'mpi'=>$order['posting_number'],
                'mpdt'=>ft_parse($order['shipment_date'], true),
				'sku'=>clean_int($item['sku']),
            ]);
        }
    }

//    print_pre($packaging);
}
