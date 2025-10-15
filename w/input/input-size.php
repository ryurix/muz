<?

w('clean');

function parse_size(&$v) {
	$v['valid'] = 1;

	if (isset($v['value'])) {
		$code = $v['code'];
		$v['value'] = clean_money(kv($_REQUEST, $code, ''))
		.','.clean_money(kv($_REQUEST, $code.'-1'))
		.','.clean_money(kv($_REQUEST, $code.'-2'))
		.','.clean_money(kv($_REQUEST, $code.'-3'));
	} else {
		if (isset($v['default'])) {
			$v['value'] = $v['default'];
		} else {
			$v['value'] = '';
			$v['valid'] = !isset($v['exp']) && !isset($v['min']);
		}
	}
}

function input_size($v, $class = '') {
	$values = explode(',', $v['value']);
	$back = '
<table><tr>
	<td><input type="text" class="form-control d-block" name="'.$v['code'].'-1" placeholder="ш, см" value="'.kv($values, 1, '').'"></td>
	<td><input type="text" class="form-control d-block" name="'.$v['code'].'-2" placeholder="г, см" value="'.kv($values, 2, '').'"></td>
	<td><input type="text" class="form-control d-block" name="'.$v['code'].'-3" placeholder="в, см" value="'.kv($values, 3, '').'"></td>
	<td><input type="text" class="form-control d-block" name="'.$v['code'].'" placeholder="вес, кг" value="'.kv($values, 0, '').'"></td>
</tr></table>';
/*
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

	$value = str_replace('"', '&quot;', $v['value']);
	$more = isset($v['more']) ? ' '.$v['more'] : '';

	$back.=' value="'.$value.'"'.$more.'>';
*/
	return $back;
}

?>