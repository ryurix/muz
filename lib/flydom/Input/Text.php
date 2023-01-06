<? namespace Flydom\Input;

class Text extends Input
{
	function parse($values = null) {
		$v = &$this->data;

		$v['valid'] = 1;
		if (isset($v['value'])) {
			$v['value'] = trim($v['value']);
		} else {
			$v['value'] = isset($v['default']) ? $v['default'] : '';
		}
		$v['rows'] = isset($v['rows']) ? $v['rows'] : 3;
		$v['columns'] = $v['rows'] > 3 ? 2 : 1;
	}

	function build($class = '') {
		$v = &$this->data;

		$back = '<textarea name="'.$v['code'].'"';

		$class = array('form-control');
		if (isset($v['class'])) { $class[] = $v['class']; }
		if (count($class)) {
			$back.=' class="'.implode(' ', $class).'"';
		}

		$style = array();
		if (isset($v['width'])) { $style[] = 'width:'.$v['width'].'px';	}
		if (count($style)) {
			$back.=' style="'.implode(';', $style).';"';
		}

		if (isset($v['rows'])) {
			$back.= ' rows='.$v['rows'];
		}
		if (isset($v['placeholder'])) {
			$back.=' placeholder="'.$v['placeholder'].'"';
		}
		if (isset($v['readonly']) && $v['readonly']) {
			$back.=' disabled="disabled"';
		}
		if (isset($v['id'])) {
			$back.= ' id="'.$v['id'].'"';
		}
		if (isset($v['more'])) {
			$back.= ' '.$v['more'];
		}

		$back.= '>'.$v['value'].'</textarea>';
		return $back;
	}

}