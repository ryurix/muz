<?

//w('input-line');
//w('clean');

function parse_multi2(&$v) {
	$v['valid'] = 1;
	if (isset($_REQUEST[$v['code'].'--'])) {
		$v['value'] = array();
		if (isset($_REQUEST[$v['code']])) {
			$data = $_REQUEST[$v['code']];
			if (is_array($data)) {
				foreach ($v['values'] as $k=>$i) {
					if (isset($data[$k])) {
						$v['value'][] = $k;
					}
				}
			}
		}
	} else {
		if (isset($v['default'])) {
			$v['value'] = $v['default'];
		} else {
			$v['value'] = array();
			$v['valid'] = 0;
		}
	}
}

function input_multi2($v) {
	$disabled = isset($args['readonly']) && $args['readonly'] ? ' disabled=disabled' : '';

	$cols = isset($v['cols']) ? $v['cols'] : 1;
	if ($cols > 1) {
		$box = 'table';
		$box2 = 'td';
	} else {
		$box = 'div';
		$box2 = 'div';
	}
	$s = "<$box class=\"form-multi\">";
	$i = 0;
	w('lib_array');
	$values = $cols > 1 ? array_for_columns($v['values'], $cols) : $v['values'];
	foreach($values as $k=>$i) {
		$i++;
		if (($i - 1) % $cols == 0 && $cols > 1) $s.= '<tr>';

		$s.= '<'.$box2.'><input type="checkbox" class="checkbox" id="'.$v['code'].'-'.$k.'" name="'.$v['code'].'['.$k.']" ';
		if (in_array($k, $v['value'])) {
			$s.= 'CHECKED';
		}
		$s.= '> <label for="'.$v['code'].'-'.$k.'">'.$i.'</label></'.$box2.'>';

		if ($i % $cols == 0 && $cols > 1) $s.= "</tr>\n";
	}
	if ($i % $cols != 0) {
		for (;$i % $cols !=0;$i++) {
			$s.= '<td>&nbsp;</td>';
		}
		$s.= "</tr>\n";
	}
	$s.= "</$box>";
	$s.= '<input type=hidden name="'.$v['code'].'--" />';

	return $s;
}

?>