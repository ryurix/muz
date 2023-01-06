<? namespace Flydom\Input;

class Select extends Input
{
	function parse($values = null) {
		$v = &$this->data;

		$v['value'] = isset($v['value']) ? $v['value'] : (isset($v['default']) ? $v['default'] : '');
		$v['valid'] = array_key_exists($v['value'], $v['values']);
	}


	function build($class = '') {
		$v = &$this->data;

		$back = '<select name="'.$v['code'].'"';

		$class = array('form-control');
		if (isset($v['class'])) { $class[] = $v['class']; }
		if ($v['iv'] ?? 0) { $class[] = 'is-invalid'; }
		if (count($class)) {
			$back.=' class="'.implode(' ', $class).'"';
		}

		$style = array();
		if (isset($v['style'])) { $style[] = $v['style']; }
		if (isset($v['width'])) { $style[] = 'width:'.$v['width'].'px';	}
		if (count($style)) {
			$back.=' style="'.implode(';', $style).';"';
		}

		if (isset($v['readonly']) && $v['readonly']) { $back.= ' disabled="disabled"'; }
		if (isset($v['id'])) { $back.= ' id="'.$v['id'].'"'; }
		if (isset($v['more'])) { $back.= ' '.$v['more']; }

		$back.= '>';

		$values = $v['values'];
		if (!isset($values[$v['value']])) {
			$back.= '<option value="'.$v['value'].'" SELECTED>';
		}
		foreach ($values as $k=>$i) {
			$back.= '<option value="'.$k.'"';
			if (strcmp($v['value'], $k) == 0) {
				$back.= ' SELECTED';
			}
			$name = $i;
			if (is_array($name)) {
				$name = $i['name'];
			}
			$back.= '>'.$name;
		}
		$back.= '</select>';
		return $back;
	}
}