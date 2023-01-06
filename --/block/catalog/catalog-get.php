<?

function catalog_get_array($plan) {
	$a = array();
	foreach ($plan as $k=>$v) {
		if ($k == '') { continue; }

		if ($v['value'] != $v['default']) {
			$a[$k] = $v['value'];
		}
	}
	return $a;
}

function catalog_param($a, $def = '') {
	$a = catalog_get_array($a);

	$s = array();
	foreach ($a as $k=>$v) {
		$s[]= $k.'='.urlencode($v);
	}
	return count($s) ? '?'.implode('&', $s) : $def;
}

?>