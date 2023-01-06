<?

function parse_radio(&$v) {
	if (isset($v['value'])) {
		$v['value'] = trim($v['value']);
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : '';
	}
	$v['valid'] = array_key_exists($v['value'], $v['values']);
}

function input_radio($v) {
	$s = '<div';
	$class = array('form-radio');
	if (isset($v['class'])) { $class[] = $v['class']; }
	if (isset($v['iv']) && $v['iv']) { $class[] = 'is-invalid'; }
	if (count($class)) {
		$s.=' class="'.implode(' ', $class).'"';
	}

	$style = array();
	if (isset($v['width'])) { $style[] = 'width:'.$v['width'].'px';	}
	if (count($style)) {
		$s.=' style="'.implode(';', $style).';"';
	}

	if (isset($v['id'])) { $s.= ' id="'.$v['id'].'"'; }
	if (isset($v['more'])) { $s.= ' '.$v['more']; }

	$s.= '>';

	$values = $v['values'];
	
	if (!isset($values[$v['value']])) {
		$s.= '<div style="display:none"><input type="radio" name="'
			.$v['code'].'" value="" CHECKED></div>';
	}

	$code = $v['code'];
	foreach (array_keys($values) as $vk) {
		$v['code'] = $code.'-'.$vk;
		$s.= input_radio_one($v, $vk);

		$name = $values[$vk];
		if (is_array($name)) {
			$name = $values[$vk]['name'];
		}
		$s.= '<label for="'.$v['code'].'">'.$name.'</label>';
		$s.= "<br />\n";
	}
	$s.= '</div>';

	return $s;
}

function input_radio_one($v, $value) {
	$back = '<input type="radio" name="'.$v['code'].'"';

	$back.= ' value="'.$value.'"';

	if (isset($v['class'])) {
		$back.= ' class="'.$v['class'].'"';
	}

	if (strcmp($v['value'], $value) == 0) {
		$back.= ' checked';
	}

	$back.= (isset($v['readonly']) && $v['readonly']) ? ' disabled="disabled">' : '>';

	return $back;
}

?>