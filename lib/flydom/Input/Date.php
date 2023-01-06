<? namespace Flydom\Input;

class Date extends Line
{
	protected $prop_default = [
        'hidden'=>false,
		'name'=>null,
		'placeholder'=>'ДД.ММ.ГГГГ',
        'readonly'=>false,
        'valid'=>true,
    ];

	static function ft_parse($time) {
		$day = date('d');
		$month = date('n');
		$year = date('Y');

		if (preg_match('|^([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{1,4})$|', $time, $matches)) {
			$x_day = $matches[1];
			$x_month = $matches[2];
			$x_year = $matches[3];
		} elseif (preg_match('|^([0-9]{1,4})\-([0-9]{1,2})\-([0-9]{1,2})$|', $time, $matches)) {
			$x_day = $matches[3];
			$x_month = $matches[2];
			$x_year = $matches[1];
		} elseif (preg_match('|^[0-9]+$|', $time, $matches)) {
			$x_day = date('d', $time);
			$x_month = date('n', $time);
			$x_year = date('Y', $time);
		} else {
			return null;
		}

		if ($x_day > 0 && $x_day <= 31) $day = $x_day;
		if ($x_month > 0 && $x_month <= 12) $month = $x_month;
		if ($x_year > 1900) $year = $x_year;

		return mktime(0, 0, 0, $month, $day, $year);
	}

	function parse($values = null) {
		$v = &$this->data;

		$v['valid'] = 1;
		if (isset($v['value'])) {
			$value = Date::ft_parse($v['value']);
			if (empty($value)) {
				$v['valid'] = 0;
			} else {
				$v['value'] = $value;
			}
		} else {
			$v['value'] = isset($v['default']) ? Date::ft_parse($v['default']) : time();
		}
		if (!empty($v['value'])) {
			$v['value'] = date('d.m.Y', $v['value']);
		}
	}

	function build($class = '') {
		$v = &$this->data;

		if (!isset($v['id'])) {
			$v['id'] = $v['code'].'-dt';
		}
		$v['class'] = isset($v['class']) ? $v['class'].' input-date' : 'input-date';

		return parent::build($v);
	}
}