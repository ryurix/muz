<?

w('input-combo');
w('clean');

function parse_filter1(&$r) {
	$r['valid'] = 1;

	if (isset($r['value']) && is_array($r['value'])) {
		$value = array_unique($r['value']);
		$value = \Flydom\Arrau::remove('0', $value);
		$r['value'] = implode(',', $value);
	} else {
		$r['value'] = isset($r['default']) ? $r['default'] : '';
	}
}

function input_filter1($r) {
	$val = explode(',', $r['value']);

	$s = '';
	for ($i=0; $i<3; $i++) {
		$a = array(
			'code'=>$r['code'].'[]', //.$i,
			'value'=>isset($val[$i]) ? $val[$i] : 0,
			'values'=>$r['values'],
			'width'=>300,
		);
		$s.= input_combo($a);
	}
	return $s;
}

?>