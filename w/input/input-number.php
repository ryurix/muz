<?

w('input-line');
w('clean');

function parse_number(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = clean_number($v['value']);
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : '';
	}	
}

function input_number($v) {
	return input_line($v);
}

?>