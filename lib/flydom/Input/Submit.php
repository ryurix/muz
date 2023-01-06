<? namespace Flydom\Input;

class Submit extends Input
{
	function parse($values = null) {
		$v = &$this->data;
	}

	function build($class = '') {
		$v = &$this->data;
		$back = '<button type="submit"';

		$class = array('btn');
		if (isset($v['class'])) {
			if (is_array($v['class'])) {
				$class[] = $v['class'][$i];
			} else {
				$class[] = $v['class'];
			}
		} else {
			$class[] = 'btn-default';
		}
		$back.= ' class="'.implode(' ', $class).'"';
		if (isset($v['onclick'])) {
			$back.= ' onclick="'.addcslashes($v['onclick'], '"').'"';
		}
		$back.= '>'.$v['value']."</button>\n";
		return $back;
	}
}