<?

w('input-int');
w('input-checkbox2');
w('clean');

function parse_intus(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = -1*abs(round(clean_int($v['value'])));
		if (isset($_REQUEST[$v['code'].'+'])) {
			$v['value'] = abs($v['value']);
		}
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : 0;
	}	
}

function input_intus($r) {
	$box = $r;
	$box['value'] = $r['value'] > 0;
	$box['code'].= '+';

	$r['value'] = abs($r['value']);
	return input_int($r).input_checkbox2($box, 'checkbox-inline');
}

?>