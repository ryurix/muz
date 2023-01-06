<? namespace Flydom\Input;


class Datetime extends Line
{
	protected $prop_default = [
        'hidden'=>false,
		'name'=>null,
		'placeholder'=>'ДД.ММ.ГГГГ ЧЧ:ММ',
        'readonly'=>false,
        'valid'=>true,
    ];

	static function ft_parse($s, $back = false) {
		$sec = 0;
		$min = 0;
		$hour = 0;
		$day = date('d');
		$month = date('n');
		$year = date('Y');

		if ($back) {
			list($x_year, $x_month, $x_day, $x_hour, $x_min, $x_sec) = preg_split('![ \\-T:]!', $s.'-- ::');
		} else {
			list($x_day, $x_month, $x_year, $x_hour, $x_min, $x_sec) = preg_split('![ /,\\-\\.:]!', $s.'.. ::');
		}
		$x_day = ft_09($x_day);
		$x_month = ft_09($x_month);
		$x_year = ft_09($x_year);
		$x_hour = ft_09($x_hour);
		$x_min = ft_09($x_min);
		$x_sec = ft_09($x_sec);

		if ($x_day > 0 && $x_day <= 31) $day = $x_day;
		if ($x_month > 0 && $x_month <= 12) $month = $x_month;
		if ($x_year > 1900) $year = $x_year;
		if ($x_hour >= 0 && $x_hour < 24) $hour = strlen($x_hour) > 0 ? $x_hour : 0;
		if ($x_min >= 0 && $x_min < 60) $min = strlen($x_min) > 0 ? $x_min : 0;
		if ($x_sec >= 0 && $x_sec < 60) $sec = strlen($x_sec) > 0 ? $x_sec : 0;

		return mktime($hour, $min, $sec, $month, $day, $year);
	}

	function parse($values = null) {
		$v = &$this->data;

		$v['valid'] = 1;
		if (isset($v['value'])) {
			$value = DateTime::ft_parse($v['value']);
			if (empty($value)) {
				$v['valid'] = 0;
			} else {
				$v['value'] = $value;
			}
		} else {
			$v['value'] = isset($v['default']) ? DateTime::ft_parse($v['default']) : time();
		}
		if (!empty($v['value'])) {
			$v['value'] = date('d.m.Y H:i', $v['value']);
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