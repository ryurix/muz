<?

function sale_calc($store, &$sale_code) {
	if (strlen($sale_code)) {
		return $store['price'];
	}

	static $row;

	if (!is_array($row)) {
		$q = db_query('SELECT * FROM sale WHERE code="'.addslashes($sale_code).'" AND dt2>='.\Config::now());
		if ($row = db_fetch($q)) {
			if (strlen($row['up'])) {
				$up = explode(',', $row['up']);
				$children = cache_load('children-hide');
				$sale_ups = array();
				foreach ($up as $i) {
					$sale_ups = $sale_ups + $children[$i];
				}
				$row['up'] = $sale_ups;
			} else {
				$row['up'] = array();
			}
			if (strlen($row['brand'])) {
				$row['brand'] = explode(',', $row['brand']);
			} else {
				$row['brand'] = array();
			}
		} else {
			$sale_code = '';
			return $store['price'];
		}
	}

	$sale = $row['perc'];

	if (count($row['up']) && !in_array($store['up'], $row['up'])) {
		$sale = 0;
	}

	if (count($row['brand']) && !in_array($store['brand'], $row['brand'])) {
		$sale = 0;
	}

	return floor($store['price']*(100 - min($store['sale'], $sale))/100);
}

?>