<?

w('input-line');
w('clean');

function parse_int(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = clean_int($v['value']);
		if (isset($v['min']) && mb_strlen($v['value'])<=$v['min']) {
			$v['valid'] = 0;
		}
	} else {
		if (isset($v['default'])) {
			$v['value'] = $v['default'];
		} else {
			$v['value'] = '';
			$v['valid'] = 0;
		}
	}
}

function input_int($v) {
	return input_line($v);
}

?>