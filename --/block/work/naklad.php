<?

function naklad_commit($naklad, $vendor, $type) {
	$brand = cache_load('brand');
	$sign = ($type > 0 ? '+' : '-');
	$q = db_query('SELECT sync.i, nakst.count, nakst.store, store.name, store.brand, store.model FROM store,nakst LEFT JOIN sync ON sync.store=nakst.store AND sync.vendor='.$vendor.' WHERE store.i=nakst.store AND nakst.naklad='.$naklad);
	
	while ($i = db_fetch($q)) {
		if ($i['i']) {
			db_update('sync', array('count=count'.$sign.$i['count'], 'dt'=>now()+60*60*24*365*5), array('i'=>$i['i']));
		} else {
			if (isset($brand[$i['brand']]) && strlen($brand[$i['brand']])) {
				$name = $brand[$i['brand']];
			} else {
				$name = '';
			}
			if (strlen($i['model'])) {
				$name.= ' '.$i['model'];
			}
			$name.= ' '.$i['name'];

			db_insert('sync', array(
				'code'=>$i['store'],
				'name'=>$name,
				'dt'=>now()+60*60*24*365*5,
				'store'=>$i['store'],
				'vendor'=>$vendor,
				'count'=>$sign.$i['count'],
			));
		}
	}
	db_close($q);
}

?>