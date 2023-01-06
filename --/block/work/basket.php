<?

w('gtm');

function basket_calc($basket, $sale_code) {
	$back = array();
	if (count($basket) == 0) {
		return $back;
	}

	$sale = 0;
	$sale_ups = array();
	$sale_brand = array();
	if (strlen($sale_code)) {
		$q = db_query('SELECT * FROM sale WHERE code="'.addslashes($sale_code).'" AND dt2>='.now());
		if ($row = db_fetch($q)) {
			if (strlen($row['up'])) {
				$up = explode(',', $row['up']);
				$children = cache_load('children-hide');
				foreach ($up as $i) {
					$sale_ups = array_merge($sale_ups, $children[$i]);
				}
			}

			if (strlen($row['brand'])) {
				$sale_brand = explode(',', $row['brand']);
			}
			$sale = $row['perc'];
		} else {
			$sale_code = '';
		}
	}
	$_SESSION['sale'] = $sale_code;

	$q = db_query('SELECT store.*,catalog.name2,catalog.url url2 FROM store,catalog WHERE store.up=catalog.i AND store.i IN ('.implode(',', array_keys($basket)).')');
	$goods = array();
	while ($i = db_fetch($q)) {
		$goods[$i['i']] = $i;
	}

	$brand = cache_load('brand');

	$cfg = isset($_SESSION['config']) ? $_SESSION['config'] : '';
	if (!is_array($cfg)) {
		$cfg = strlen($cfg) ? php_decode($cfg) : array();
	}
	$vendor = isset($cfg['vendor']) ? $cfg['vendor'] : 0;

	foreach ($basket as $k=>$v) {
		if (isset($goods[$k])) {
			$store = $goods[$k];
			$sale_value = $sale;

			if (count($sale_ups) && !in_array($store['up'], $sale_ups)) {
				$sale_value = 0;
			}
			if (count($sale_brand) && !in_array($store['brand'], $sale_brand)) {
				$sale_value = 0;
			}

			if ($vendor) {
				$count = db_result('SELECT count FROM sync WHERE store='.$store['i'].' AND vendor='.$vendor);
				if ($count) {
					$store['vendor'] = $vendor;
				}
			}

			$price = floor($store['price']*(100 - min($store['sale'], $sale_value))/100);
			$name = trim($brand[$store['brand']].' '.$store['model'].' '.$store['name']);
			$back[] = array(
				'store'=>$store['i'],
				'url'=>$store['url'],
				'url2'=>$store['url2'],
				'icon'=>$store['icon'],
				'name'=>$name,
				'name1'=>$store['name'],
				'name2'=>$store['name2'],
				'model'=>$store['model'],
				'price1'=>$store['price'],
				'price2'=>$price,
				'count'=>$v['c'],
				'info'=>$v['i'],
				'sale'=>($price < $store['price'] ? $sale_code : ''),
				'vendor'=>$store['vendor'],
				'brand' => $brand[$store["brand"]],
				'category'=>gtm_catalog_fullname($store["up"]),
			);
		}
	}
	return $back;
}

?>