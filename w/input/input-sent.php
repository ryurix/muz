<?

function parse_sent(&$v) {
	$v['valid'] = 1;
	if (!isset($v['value'])) {
		$v['value'] = 0;
	}
	$v['hide'] = 1;
}

function input_sent($v) {
	return '<input type="hidden" name="'.$v['code'].'" value="1" />';
}

?>