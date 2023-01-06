<?

w('input-line');
w('clean');

function parse_phone(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$value = clean_phone($v['value']);
		$len = strlen($value);
		$min = kv($v, 'min', 11);
		$max = kv($v, 'max', 11);		
		if ($min <= $len && $len <= $max) {
			$v['value'] = $value;
		} else {
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

function input_phone($v, $class = '') {
	$class = trim($class.' input-phone');
	return input_line($v, $class);
}

?>