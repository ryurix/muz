<? namespace Flydom\Input;

class Checkbox extends Input
{
	function parse($values = null) {
		$v = &$this->data;

		if (isset($v['value'])) {
			$v['value'] = 1;
		} else {
			if (isset($values[$v['code'].'--'])) {
				$v['value'] = 0;
			} else {
				if (isset($v['default']) && $v['default']) {
					$v['value'] = $v['default'];
				} else {
					$v['value'] = 0;
				}
			}
		}
	}

	function build($class = '') {
		$v = &$this->data;

		$key = $v['code'];
		$id = isset($v['id']) ? $v['id'] : $key.'-checkbox';

		$back = '<div class="form-check"><input type=hidden name="'.$v['code'].'--" />';
		$back.= '<input type=checkbox name="'.$key.'" id="'.$id.'"';

		$class = array('checkbox');
		if (isset($v['class'])) { $class[] = $v['class']; }
		if (count($class)) {
			$back.=' class="'.implode(' ', $class).'"';
		}

		if (isset($v['readonly']) && $v['readonly']) {
			//$back.=' disabled="disabled"';
			$back.= ' readonly';
		}

		$checked = isset($v['checked']) ? $v['checked'] : 1;
		if ($v['value'] == $checked) { $back.= ' CHECKED'; }
		if (isset($v['more'])) { $back.= ' '.$v['more']; }
		$back.= '>';

		$label = kv($v, 'label', '');
	//	if (strlen($label)) {
		$back.= '<label for="'.$id.'">&nbsp;'.$label.'</label>';
	//	}

		$back.= '</div>';

		return $back;
	}

}