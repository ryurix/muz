<?

w('clean');
w('input-combo');

function filters($up) {
	static $filter = NULL;

	if (is_null($filter)) {
		$pathway = cache_load('pathway-hide');

		$pre = isset($pathway[$up]) ? $pathway[$up] : array();
		$pre = isset($pre['pre']) ? $pre['pre'] : array();
		$pre[] = $up;

		$filter = array();
		foreach ($pre as $v) {
			if ($v == 0 || !isset($pathway[$v])) continue;
			$f = $pathway[$v]['f'];
			if (count($f)) {
				$filter = $filter + $f;
			}
		}
		$filter = array_unique($filter);
		$filter = array_slice($filter, 0, 3);
	}

	return $filter;
}

function parse_filter2(&$r) {
	$r['valid'] = 1;
	if (isset($r['value']) && is_array($r['value'])) {
		$filter = filters(isset($_REQUEST['up']) ? first_int($_REQUEST['up']) : $r['up']);
		$values = cache_load('filter3');

		$value = array();
		foreach ($filter as $k=>$v) {
			if (isset($r['value'][$k])) {
				$param = $r['value'][$k];
				if (isset($values[$v][$param])) {
					$value[]= $param;
				}
			}
		}

		$r['value'] = implode(',', $value);
	} else {
		$r['value'] = isset($r['default']) ? $r['default'] : '';
	}
}

function input_filter2($r) {
	$s = '';

	$names = cache_load('filter1');
	$values = cache_load('filter3');

	$filter = filters($r['up']);
	$value = explode(',', $r['value']); // is_array($r['value']) ? $r['value'] :
	foreach ($filter as $f) {
		$val = array_intersect($value, array_keys($values[$f]));
		$val = count($val) ? array_shift($val) : 0;

		$s.= input_combo($dummy = array(
			'code'=>$r['code'].'[]',
			'value'=>$val,
			'values'=>array(0=>$names[$f].':') + $values[$f],
			'width'=>300
		));
	}
	return $s;
}