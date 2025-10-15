<?

function parse_label(&$v) {
	if (!isset($v['value']) && isset($v['default'])) {
		$v['value'] = $v['default'];
	}
	$v['valid'] = TRUE;
}

function input_label($v) {
	return isset($v['value']) ? $v['value'] : (isset($v['default']) ? $v['default'] : '');
}

?>