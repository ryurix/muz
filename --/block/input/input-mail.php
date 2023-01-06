<?

w('clean');
w('input-line');

function parse_mail(&$v) {
	if (isset($v['value'])) {
		$v['value'] = clean_mail($v['value']);
		$v['valid'] = is_mail($v['value']);
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : '';
		$v['valid'] = TRUE;
	}
}

function input_mail($v) {
	return input_line($v);
}

?>