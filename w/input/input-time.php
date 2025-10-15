<?

w('input-combo');

function parse_time(&$v) {
	$code = $v['code'];
	$dt = isset($v['value']) ? $v['value'] : $v['default'];

	if (!isset($v['hours'])) {
		$hours = array();
		for ($i=0; $i<24; $i++) {
			$hours[$i] = strlen($i) < 2 ? '0'.$i : $i;
		}
		$v['hours'] = $hours;
	}

	$hh = ((isset($_REQUEST[$code.'-hh']) ? $_REQUEST[$code.'-hh'] : date('H', $dt)) - date('H', 0));
	if ($hh < 0) { $hh+= 24; }
	if (!isset($v['hours'][$hh])) {
		reset($v['hours']);
		$hh = key($v['hours']);
	}

	$step = isset($v['step']) ? $v['step'] : 5;
	if (!isset($v['minutes'])) {
		$minutes = array();
		for ($i=0; $i<60; $i+=$step) {
			$minutes[$i] = strlen($i) < 2 ? '0'.$i : $i;
		}
		$v['minutes'] = $minutes;
	}

	$mm = isset($_REQUEST[$code.'-mm']) ? $_REQUEST[$code.'-mm'] : round(date('i', $dt) / $step) * $step;
	if (!isset($v['minutes'][$mm])) {
		reset($v['minutes']);
		$mm = key($v['minutes']);
	}

	$v['value'] = mktime($hh, $mm, 00, date('n', $dt), date('d', $dt), date('Y', $dt)) + date('H', 0)*60*60;
	$v['valid'] = TRUE;
}

function input_time($v) {
	$hh = date('H', $v['value']) * 1;
	$step = isset($v['step']) ? $v['step'] : 5;
	$mm = round(date('i', $v['value']) / $step) * $step;

	if (!isset($v['hours'])) { $v['hours'] = array(); }
	if (!isset($v['hours'][$hh])) { $v['hours'][$hh] = strlen($hh) < 2 ? '0'.$hh : $hh; ksort($v['hours']); }

	if (!isset($v['minutes'])) { $v['minutes'] = array(); }
	if (!isset($v['minutes'][$mm])) { $v['minutes'][$mm] = strlen($mm) < 2 ? '0'.$mm : $mm; ksort($v['minutes']); }

	$code = $v['code'];
	$back = input_combo(array('code'=>$code.'-hh', 'value'=>$hh, 'values'=>$v['hours'], 'width'=>70, 'readonly'=>isset($v['readonly']) ? $v['readonly'] : 0))
	.' '
	.input_combo(array('code'=>$code.'-mm', 'value'=>$mm, 'values'=>$v['minutes'], 'width'=>70, 'readonly'=>isset($v['readonly']) ? $v['readonly'] : 0));

	return $back;
}

?>