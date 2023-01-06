<? namespace Flydom\Input;

class Hidden extends Input
{
	protected $prop_default = [
        'hidden'=>true,
        'readonly'=>true,
        'valid'=>true,
    ];

	function parse($values = null) {
		$v = &$this->data;

		$v['valid'] = 1;
		if (!isset($v['value'])) {
			$v['value'] = isset($v['default']) ? $v['default'] : '';
		}
	}

	function build() {
		$v = &$this->data;

		return '<input type="hidden" name="'.$v['code'].'" value="'.$v['value'].'" />';;
	}
}