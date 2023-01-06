<?

function parse_password(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = trim($v['value']);
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : '';
	}
}

function input_password($v) {
	$back = '<input type="password"';

	$class = array();
	if (isset($v['class'])) { $class[] = $v['class']; }
	if (count($class)) {
		$back.=' class="'.implode(' ', $class).'"';
	}

	$style = array();
	if (isset($v['width'])) { $style[] = 'width:'.$v['width'].'px';	}
	if (count($style)) {
		$back.=' style="'.implode(';', $style).';"';
	}

	if (isset($v['readonly']) && $v['readonly']) {
		$back.=' disabled="disabled"';
	}
	if (isset($v['code'])) {
		$back.=' name="'.$v['code'].'"';
	}
	if (isset($v['placeholder'])) {
		$back.=' placeholder="'.$v['placeholder'].'"';
	}

	$value = isset($v['value']) ? $v['value'] : (isset($v['default']) ? $v['default'] : '');
	$value = str_replace('"', '&quot;', $value);
	$more = isset($v['more']) ? ' '.$v['more'] : '';

	$back.=' value="'.$value.'"'.$more.'>';
	return $back;
}

?>