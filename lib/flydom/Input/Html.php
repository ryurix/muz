<? namespace Flydom\Input;

class Html extends Input
{
	function parse($values = null) {
		$v = &$this->data;

		if (!isset($v['value']) && isset($v['default'])) {
			$v['value'] = $v['default'];
		}
	}

	function build() {
		$v = &$this->data;
		return isset($v['value']) ? $v['value'] : (isset($v['default']) ? $v['default'] : '');
	}

}