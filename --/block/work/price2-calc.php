<?

function price2_calc($rule) {
	global $config;
	$config['price_rule'] = $rule['i'];

	if (!is_array($rule['grp'])) {
		$rule['grp'] = explode(',', $rule['grp']);
	}
	if (!is_array($rule['brand'])) {
		$rule['brand'] = explode(',', $rule['brand']);
	}

	$count = 0;
	switch ($rule['price']) {
	case 1: $count = price2_calc_vendor($rule); break;
	case 2: $count = price2_calc_vendor($rule); break;
	case 5: $count = price2_calc_vendor($rule); break;
	case 11: $count = price2_calc_avg_max($rule, 0, 0); break;
	case 12: $count = price2_calc_avg_max($rule, 1, 0); break;
	case 13: $count = price2_calc_avg_max($rule, 0, 1); break;

	case 21: $count = price2_calc_yandex_opt($rule, 1); break;
	case 22: $count = price2_calc_yandex_opt($rule, 0); break;
	case 23: $count = price2_calc_yandex_price($rule, 1); break;
	case 24: $count = price2_calc_yandex_price($rule, 0); break;

	case 100: $count = price2_calc_zero($rule); break;
	case 101: $count = price2_calc_zero($rule, 1); break;
	}

	if ($count) {
		// Устанавливаем дату следующей выгрузки яндекса так, чтобы выгрузка произошла через 5 минут
		$next = cache_load('cron');
		$next['yandex'] = now() + 5*60;
		cache_save('cron', $next);
		//w('yandex');
	}
	return $count;
}

function price2_calc_vendor($rule) {
	$where = price2_where($rule);
	if ($rule['count'] == 2) {
		$where.= ' AND sync.count<=0';
	}
	if ($rule['price'] >= 5) {
		$where.= ' AND sync.opt>0';
	}
	$q = db_query('SELECT store.i, sync.'.($rule['price'] >= 5 ? 'opt':'price').' price2, sync.count, sync.vendor vendor, store.price2 _price, store.count _count, store.vendor _vendor'
.' FROM store,sync WHERE store.i=sync.store'.$where.' ORDER BY price2 DESC');
	$count = 0;
	while ($i = db_fetch($q)) {
		if ($rule['price'] != 2) {
			$i['price'] = round($i['price2']*(100 + $rule['sale'])/100);
		}
		if ($i['count'] == 0 && $rule['count'] == 2) {
			$i['_count'] = -1; // $i['count'] = -1;
		}
		if (price2_update($i)) {
			$count++;
		}
	}
	db_close($q);
	return $count;
}

function price2_calc_avg_max($rule, $avg = 0, $max = 0) {
	$where = price2_where($rule);
	$select = 'SELECT * FROM sync,'
.' (SELECT store.i i, store.price2 _price, store.count _count, store.vendor _vendor'
.' FROM store,sync WHERE store.i=sync.store'.$where.') store'
.' WHERE sync.store=store.i AND sync.price IS NOT NULL';
	if ($rule['days']) {
		$select.= ' AND sync.dt>='.(now() - 24*60*60*$rule['days']);
	}
	$select.=' ORDER BY store.i,sync.price'.($max?' DESC':'').',sync.dt DESC';
//		alert($select);
	$q = db_query($select);
	$row = array();
	$store = 0;
	while ($i = db_fetch($q)) {
		if ($store == $i['store']) {
			if ($i['count'] > 0 && $i['price'] > 0) {
				$pre = &$row[$store];
				if ($avg) {
					$price2[] = $i['price'];
				}
				if ($pre['count'] < 1 || (($i['price'] < $pre['price']) xor $max)) {
					$pre = $i;
				}
			}
		} else {
			if ($avg) {
				if ($store && count($price2)) {
					$row[$store]['price'] = round(array_sum($price2) / count($price2));
				}
				$price2 = $i['count'] > 0 && $i['price'] > 0 ? array($i['price']) : array();
			}
			$store = $i['store'];
			$row[$store] = $i;
		}
	}
	db_close($q);

	if ($avg && $store && count($price2)) {
		$row[$store]['price'] = round(array_sum($price2) / count($price2));
	}

	$count = 0;
	foreach ($row as $i) {
		$i['price'] = round($i['price']*(100 + $rule['sale'])/100);
		if (price2_update($i)) {
			$count++;
		}
	}
	return $count;
}

function price2_calc_zero($rule, $all = 0) {
	$where = array();

	$grp = implode(',', $rule['grp']);
	if (strlen($grp)) {
		$where[] = 'store.grp IN ('.$grp.')';
	}
	$brand = implode(',', $rule['brand']);
	if (strlen($brand)) {
		$where[] = 'store.brand IN ('.$brand.')';
	}
	if ($rule['vendor'] >= 0) {
		$where[] = 'store.vendor='.$rule['vendor'];
	}
	if ($rule['up']) {
		$children = cache_load('children');
		$where[] = 'store.up IN ('.implode(',', $children[$rule['up']]).')';
	}
	if ($rule['count']) {
		if ($rule['count'] == 1) {
			$where[] = 'store.count>0';
		}
		if ($rule['count'] == 2) {
			$where[] = 'store.count<1';
		}
	}
	$where_sync = $rule['days'] ? ' AND sync.dt>='.(now() - 24*60*60*$rule['days']) : '';
	if (!$all) {
		$where[] = 'NOT EXISTS (SELECT 1 FROM sync WHERE sync.store=store.i'.$where_sync.')';
	}

	$where = count($where) ? ' WHERE '.implode(' AND ', $where) : '';

	$q = db_query('SELECT store.i, store.vendor vendor, store.price2 _price, store.count _count, store.vendor _vendor FROM store'.$where);

	$count = 0;
	while ($i = db_fetch($q)) {
		$i['price'] = 0;
		$i['count'] = 0;

		if ($rule['vendor'] >= 0) {
			$i['vendor'] = $rule['vendor'];
		}

		if (price2_update($i)) {
			$count++;
		}
	}
	db_close($q);
	return $count;
}

function price2_update($i) {
	global $config;
//	$i['price'] = $i['count'] == 0 ? 0 : $i['price']; // Если количество = 0 -- цену не ставим

	if (isset($i['price']) && $i['price'] != $i['_price']) {
		$data['price2'] = $i['price'];
		if (isset($config['price_rule'])) { $data['rule2'] = $config['price_rule']; }
		db_update('store', $data, 'i='.$i['i']);
		return TRUE;
	}
	return FALSE;
}

function price2_where($rule) {
	$where = '';
	$grp = implode(',', $rule['grp']);
	if (strlen($grp)) {
		$where.= ' AND store.grp IN ('.$grp.')';
	}
	$brand = implode(',', $rule['brand']);
	if (strlen($brand)) {
		$where.= ' AND store.brand IN ('.$brand.')';
	}
	if ($rule['vendor'] >= 0) {
		$where.= ' AND sync.vendor='.$rule['vendor'];
	}
	if ($rule['up']) {
		$children = cache_load('children');
		$where.= ' AND store.up IN ('.implode(',', $children[$rule['up']]).')';
	}
	if ($rule['count']) {
		if ($rule['count'] == 1) {
			$where.= ' AND sync.count>0';
		}
		if ($rule['count'] == 2) {
			$where.= ' AND sync.count<1';
		}
	}
	if ($rule['days']) {
		$where.= ' AND sync.dt>='.(now() - 24*60*60*$rule['days']);
	}
	return $where;
}

function price2_calc_yandex_opt($rule, $less) {
	$where = price2_where($rule);
	if ($rule['count'] == 2) {
		$where.= ' AND store.count<=0';
	}
	$select = 'SELECT store.i,sync.price,sync.opt,sync.count,sync.vendor'
.',store.price2 _price,store.count _count,store.vendor _vendor,y.price yandex'
.' FROM store,sync,sync y WHERE sync.price>0 AND 19=y.vendor AND sync.opt>0 AND store.i=y.store AND store.i=sync.store'.$where;

	$q = db_query($select);

	$count = 0;
	while ($i = db_fetch($q)) {
		$min = round($i['opt']*(100 + $rule['sale'])/100);

		if (price2_calc_yandex($i, $min, $less, $rule['price'])) {
			$count++;
		}
	}
	db_close($q);
	return $count;
}

function price2_calc_yandex_price($rule, $less) {
	$where = price2_where($rule);
	if ($rule['count'] == 2) {
		$where.= ' AND store.count<=0';
	}
	$select = 'SELECT store.i,sync.price,sync.count,sync.vendor'
.',store.price2 _price,store.count _count,store.vendor _vendor,y.price yandex'
.' FROM store,sync,sync y WHERE sync.price>0 AND 19=y.vendor AND store.i=y.store AND store.i=sync.store'.$where;

	$q = db_query($select);

	$count = 0;
	while ($i = db_fetch($q)) {
		$min = round($i['price']*(100 - $rule['sale'])/100);

		if (price2_calc_yandex($i, $min, $less)) {
			$count++;
		}
	}
	db_close($q);
	return $count;
}

function price2_calc_yandex($i, $min, $less) {
	$yandex = $i['yandex'];
	if ($yandex >= $min) {
		if ($less) {
			$yandex = price2_round($yandex, 1);
			if ($yandex >= $min) {
				$i['price'] = $yandex;
			} else {
				$i['price'] = price2_round($min); // $i['count'] = -1;
			}
		} else {
			$i['price'] = $yandex;
		}
		$i['sale'] = round( ($i['price']-$min)*100/$i['price'] );

		return price2_update($i);
	} else {
		$i['price'] = price2_round($min);
		$i['sale'] = 0;
		return price2_update($i);
	}
}

function price2_round($num, $less = 0) {
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

?>