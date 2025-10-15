<?

function parse_line(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = trim($v['value']);
		if (isset($v['exp'])) {
			$v['valid'] = preg_match('/^'.$v['exp'].'$/ui', $v['value']) ? 1 : 0;
		}
		if (isset($v['min']) && mb_strlen($v['value'])<$v['min']) {
			$v['valid'] = 0;
		}
	} else {
		if (isset($v['default'])) {
			$v['value'] = $v['default'];
		} else {
			$v['value'] = '';
			$v['valid'] = !isset($v['exp']) && !isset($v['min']);
		}
	}
}

function input_line($v, $class = '') {
	$back = '<input type="text" name="'.$v['code'].'"';

	$class = strlen($class) ? array($class) : array();
	$class[] = 'form-control';
	if (isset($v['class'])) { $class[] = $v['class']; }
	if (isset($v['iv']) && $v['iv']) { $class[] = 'is-invalid'; }
	if (count($class)) {
		$back.=' class="'.implode(' ', $class).'"';
	}

	$style = array();
	if (isset($v['width'])) { $style[] = 'width:'.$v['width'].(ctype_digit($v['width'].'') ? 'px' : '');	}
	if (count($style)) {
		$back.=' style="'.implode(';', $style).';"';
	}

	if (isset($v['placeholder'])) { $back.=' placeholder="'.$v['placeholder'].'"'; }
	if (isset($v['readonly']) && $v['readonly']) { $back.=' disabled="disabled"'; }
	if (isset($v['id'])) { $back.= ' id="'.$v['id'].'"'; }
	if (isset($v['required']) && $v['required']) { $back.= ' required aria-required="true"'; }
	if (isset($v['more'])) { $back.= ' '.$v['more']; }

	$value = isset($v['value']) ? str_replace('"', '&quot;', $v['value']) : '';
	$more = isset($v['more']) ? ' '.$v['more'] : '';

	$back.=' value="'.$value.'"'.$more.'>';
	return $back;
}

?>