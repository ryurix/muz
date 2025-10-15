<?

function parse_hidden(&$v) {
	$v['valid'] = 1;
	if (!isset($v['value'])) {
		$v['value'] = isset($v['default']) ? $v['default'] : '';
	}
}

function input_hidden($v) {
	return '<input type="hidden" name="'.$v['code'].'" value="'.$v['value'].'" />';
}

?>