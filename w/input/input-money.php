<?

w('input-line');
w('clean');

function parse_money(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = clean_money($v['value']);
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : '';
	}	
}

function input_money($v) {
	return input_line($v);
}

?>