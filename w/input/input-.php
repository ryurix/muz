<?

function parse_(&$v) {
	$v['valid'] = 1;
	$v['value'] = isset($v['default']) ? $v['default'] : '';
	$v['hide'] = 1;
}

function input_($v) {
}

?>