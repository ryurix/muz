<? namespace Flydom\Input;

class Number extends Input
{
	function parse($values = null) {
		$v = &$this->data;

		$v['valid'] = true;
		if (isset($v['value'])) {
			$v['value'] = trim($v['value']);
			$v['valid'] = is_numeric($v['value']);
			if ($v['valid'] && isset($v['min']) && $v['value'] < $v['min']) {
				$v['valid'] = false;
			}
		} else {
			if (isset($v['default'])) {
				$v['value'] = $v['default'];
			} else {
				$v['value'] = '';
				$v['valid'] = !isset($v['min']);
			}
		}
	}

	function build($class = '') {
		$v = &$this->data;

		$back = '<input type="text" name="'.$v['code'].'"';

		$class = !is_array($class) && strlen($class) ? array($class) : array();
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
		return $back;
	}

}

?>