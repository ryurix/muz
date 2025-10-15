<?

namespace Cron;

class Prices extends Task {

	public static function run($data) {
		return self::calc_all($data['price']);
	}

	static function calc_all($type) {
		$count = [];
		$rules = \Db::fetchAll('SELECT * FROM prices WHERE typ='.$type.' ORDER BY i');
		foreach ($rules as $rule) {
			$count[]= self::calc($rule);
		}
		return count($count) ? max($count) : 0;
	}

	static function calc($rule) {

		if (!is_array($rule['grp'])) {
			$rule['grp'] = explode(',', $rule['grp']);
		}
		if (!is_array($rule['brand'])) {
			$rule['brand'] = explode(',', $rule['brand']);
		}
		if (!is_array($rule['vendor'])) {
			$rule['vendor'] = explode(',', $rule['vendor']);
		}

		$upd = [];
		switch ($rule['price']) {
		case 1: $upd = self::calc_vendor($rule); break;
		case 2: $upd = self::calc_vendor($rule); break;
		case 5: $upd = self::calc_vendor($rule); break;
		case 11: $upd = self::calc_avg_max($rule, 0, 0); break;
		case 12: $upd = self::calc_avg_max($rule, 1, 0); break;
		case 13: $upd = self::calc_avg_max($rule, 0, 1); break;

		case 21: $upd = self::calc_yandex_opt($rule, 1); break;
		case 22: $upd = self::calc_yandex_opt($rule, 0); break;
		case 23: $upd = self::calc_yandex_price($rule, 1); break;
		case 24: $upd = self::calc_yandex_price($rule, 0); break;

		case 100: $upd = self::calc_zero($rule); break;
		case 101: $upd = self::calc_zero($rule, 1); break;
		}

		$count = 0;
		foreach ($upd as $k=>$v) {
			if (self::update($rule, $v)) {
				$count++;
			}
		}

		return $count;
	}

	static function update($rule, $i) {

		if (isset($i['price'])) {
			if ($rule['typ'] > 0) {
				$prices = self::decode($i['_prices']);
				$prices[$rule['typ'] - 1] = $i['price'];
				$i['prices'] = self::encode($prices);
				$i['price'] = $i['_price'];
			} else {
				$i['prices'] = $i['_prices'];
			}
		}


		if ($i['count'] != $i['_count']
		|| $i['vendor'] != $i['_vendor']
		|| (isset($i['price']) && $i['price'] != $i['_price'])
		|| (isset($i['prices']) && $i['prices'] != $i['_prices'])
		) {
			$data = array(
				'count'=>$i['count'],
				'vendor'=>$i['vendor'],
				'dt'=>\Config::now(),
				'rule'=>$rule['i'],
			);
			if (isset($i['sale'])) { $data['sale'] = $i['sale']; }
			if (isset($i['price'])) { $data['price'] = $i['price']; }
			if (isset($i['prices'])) { $data['prices'] = $i['prices']; }

			db_update('store', $data, 'i='.$i['i']);
			return TRUE;
		}
		return FALSE;
	}

	static function decode($prices) {
		if (is_array($prices)) {
			return $prices;
		}

		$array = empty($prices) ? [] : explode(',', $prices);
		while (count($array) < \Type\Price::count()) {
			$array[]= 0;
		}

		return $array;
	}

	static function encode($array) {
		return implode(',', $array);
	}

	static function calc_vendor($rule) {

		$where = self::where($rule);
		if ($rule['count'] == 2) {
			$where.= ' AND sync.count<=0';
		}
		if ($rule['price'] >= 5) {
			$where.= ' AND sync.opt>0';
		}
		$q = db_query('SELECT store.i, sync.'.($rule['price'] >= 5 ? 'opt':'price').' price2,'
	.' sync.count, sync.vendor vendor, store.price _price, store.prices _prices, store.count _count, store.vendor _vendor'
	.' FROM store,sync WHERE store.i=sync.store'.$where.' ORDER BY price2 DESC');
		$upd = [];
		while ($i = db_fetch($q)) {
			if ($rule['price'] != 2) {
				$i['price'] = round($i['price2']*(100 + $rule['sale'])/100);
			}
			if ($i['count'] == 0 && $rule['count'] == 2) {
				$i['_count'] = -1; // $i['count'] = -1;
			}
			$upd[$i['i']] = $i;
		}
		db_close($q);
		return $upd;
	}

	static function calc_avg_max($rule, $avg = 0, $max = 0) {
		$where = self::where($rule);
		$select = 'SELECT * FROM sync,'
	.' (SELECT store.i i, store.price _price, store.prices _prices, store.count _count, store.vendor _vendor'
	.' FROM store,sync WHERE store.i=sync.store'.$where.') store'
	.' WHERE sync.store=store.i AND sync.price IS NOT NULL';
		if ($rule['days']) {
			$select.= ' AND sync.dt>='.(\Config::now() - 24*60*60*$rule['days']);
		}
		$select.=' ORDER BY store.i,sync.price'.($max?' DESC':'').',sync.dt DESC';

		$rows = \Flydom\Memcached::fetchAll($select);
		$row = array();
		$store = 0;
		$prices = [];
		foreach ($rows as $i) {
			if ($store == $i['store']) {
				if ($i['count'] > 0 && $i['price'] > 0) {
					$pre = &$row[$store];
					if ($avg) {
						$prices[]= $i['price'];
					}
					if (($i['price'] < $pre['price']) xor $max) {
						$pre = $i;
					}
					$pre['count']+= $i['count'];
				}
			} else {
				if ($avg) {
					if ($store && count($prices)) {
						$row[$store]['price'] = round(array_sum($prices) / count($prices));
					}
					$prices = $i['count'] > 0 && $i['price'] > 0 ? array($i['price']) : array();
				}
				$store = $i['store'];
				$row[$store] = $i;
			}
		}

		if ($avg && $store && count($prices)) {
			$row[$store]['price'] = round(array_sum($prices) / count($prices));
		}

		$upd = [];
		foreach ($row as $i) {
			$i['price'] = round($i['price']*(100 + $rule['sale'])/100);
			$upd[$i['i']] = $i;
		}
		return $upd;
	}

	static function calc_zero($rule, $all = 0) {
		$where = array();

		$grp = implode(',', $rule['grp']);
		if (strlen($grp)) {
			$where[]= 'store.grp IN ('.$grp.')';
		}
		$brand = implode(',', $rule['brand']);
		if (strlen($brand)) {
			$where[]= 'store.brand IN ('.$brand.')';
		}
		$vendor = implode(',', $rule['vendor']);
		if (strlen($vendor)) {
			$where[]= 'store.vendor in ('.$vendor.')';
		}
		if ($rule['up']) {
			$children = cache_load('children');
			$where[]= 'store.up IN ('.implode(',', $children[$rule['up']]).')';
		}
		if ($rule['count']) {
			if ($rule['count'] == 1) {
				$where[]= 'store.count>0';
			}
			if ($rule['count'] == 2) {
				$where[]= 'store.count<1';
			}
		}
		$where_sync = $rule['days'] ? ' AND sync.dt>='.(\Config::now() - 24*60*60*$rule['days']) : '';
		if (!$all) {
			$where[]= 'NOT EXISTS (SELECT 1 FROM sync WHERE sync.store=store.i'.$where_sync.')';
		}

		$where = count($where) ? ' WHERE '.implode(' AND ', $where) : '';

		$q = db_query('SELECT store.i, store.vendor vendor, store.price _price, store.prices _prices,'
		.' store.count _count, store.vendor _vendor FROM store'.$where);

		$upd = [];
		while ($i = db_fetch($q)) {
			$i['price'] = 0;
			$i['count'] = 0;

			if (count($rule['vendor']) > 0) {
				$i['vendor'] = $rule['vendor'][0];
			}

			$upd[$i['i']] = $i;
		}
		db_close($q);
		return $upd;
	}



	static function where($rule) {
		$where = [];
		$grp = implode(',', $rule['grp']);
		if (strlen($grp)) {
			$where[]= 'store.grp IN ('.$grp.')';
		}
		$brand = implode(',', $rule['brand']);
		if (strlen($brand)) {
			$where[]= 'store.brand IN ('.$brand.')';
		}
		$vendor = implode(',', $rule['vendor']);
		if (strlen($vendor)) {
			$where[]= 'sync.vendor IN ('.$vendor.')';
		}
		if ($rule['up']) {
			$children = cache_load('children');
			$where[]= 'store.up IN ('.implode(',', $children[$rule['up']]).')';
		}
		if ($rule['count']) {
			if ($rule['count'] == 1) {
				$where[]= 'sync.count>0';
			}
			if ($rule['count'] == 2) {
				$where[]= 'sync.count<1';
			}
		}
		if ($rule['days']) {
			$where[]= 'sync.dt>='.(\Config::now() - 24*60*60*$rule['days']);
		}
		if ($rule['pmin']) {
			$where[]= 'store.price>='.$rule['pmin'];
		}
		if ($rule['pmax']) {
			$where[]= 'store.price<='.$rule['pmax'];
		}
		return ' AND '.implode(' AND ', $where);
	}

	static function calc_yandex_opt($rule, $less) {
		$where = self::where($rule);
		if ($rule['count'] == 2) {
			$where.= ' AND store.count<=0';
		}
		$select = 'SELECT store.i, sync.price, sync.opt, sync.count, sync.vendor'
	.',store.price _price, store.prices _prices, store.count _count, store.vendor _vendor, y.price yandex'
	.' FROM store,sync,sync y WHERE sync.price>0 AND 19=y.vendor AND sync.opt>0 AND store.i=y.store AND store.i=sync.store'.$where.' ORDER BY sync.opt DESC';

		$q = db_query($select);

		$upd = [];
		while ($i = db_fetch($q)) {
			$min = round($i['opt']*(100 + $rule['sale'])/100);

			$upd[$i['i']] = self::calc_yandex($i, $min, $less, $rule['price']);
		}
		db_close($q);
		return $upd;
	}

	static function calc_yandex_price($rule, $less) {
		$where = self::where($rule);
		if ($rule['count'] == 2) {
			$where.= ' AND store.count<=0';
		}
		$select = 'SELECT store.i, sync.price, sync.count, sync.vendor'
	.',store.price _price, store.prices _prices, store.count _count, store.vendor _vendor, y.price yandex'
	.' FROM store,sync,sync y WHERE sync.price>0 AND 19=y.vendor AND store.i=y.store AND store.i=sync.store'.$where.' ORDER BY sync.price DESC';

		$q = db_query($select);

		$upd = [];
		while ($i = db_fetch($q)) {
			$min = round($i['price']*(100 - $rule['sale'])/100);

			$upd[$i['i']] = self::calc_yandex($i, $min, $less);
		}
		db_close($q);
		return $upd;
	}

	static function calc_yandex($i, $min, $less) {
		$yandex = $i['yandex'];
		if ($yandex >= $min) {
			if ($less) {
				$yandex = self::round($yandex, 1);
				if ($yandex >= $min) {
					$i['price'] = $yandex;
				} else {
					$i['price'] = self::round($min); // $i['count'] = -1;
				}
			} else {
				$i['price'] = $yandex;
			}
			$i['sale'] = max(0, round( ($i['price']-$min)*100/$i['price'] ));

			return $i;
		} else {
			$i['price'] = self::round($min);
			$i['sale'] = 0;
			return $i;
		}
	}

	static function round($num, $less = 0) {
		if ($num < 100) {
			$step = 1; $tip = 1; $round = 1;
		} elseif ($num < 300) {
			$step = 5; $tip = 5; $round = 1;
		} elseif ($num < 1000) {
			$step = 10; $tip = 10; $round = 5;
		} elseif ($num < 10000) {
			$step = 100; $tip = 10; $round = 10;
		} elseif ($num < 100000) {
			$step = 1000; $tip = 100; $round = 50;
		} else {
			$step = 1000; $tip = 1000; $round = 100;
		}

		if ($less) {
			$num2 = $num - $num % $step;
			if ($num2 < $num) {
				$num2 = $num2 - $tip;
			} else {
				$num2 = $num2 - $step;
			}
			return $num2;
		} else {
			$num2 = $num - $num % $round;
			return $num2;
		}

	}
}