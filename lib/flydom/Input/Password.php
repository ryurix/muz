<? namespace Flydom\Input;

class Password extends Input
{
	function parse($values = null) {
		$v = &$this->data;
		$v['valid'] = 1;
		if (isset($v['value'])) {
			$v['value'] = trim($v['value']);
		} else {
			$v['value'] = '';
		}
	}

	function build() {
		$v = &$this->data;

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

		$more = isset($v['more']) ? ' '.$v['more'] : '';

		$back.=' value=""'.$more.'>';
		return $back;
	}
}