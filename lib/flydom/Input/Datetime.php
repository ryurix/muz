<? namespace Flydom\Input;

class Datetime extends Input
{
	function parse($values = null) {
		$v = &$this->data;
		$code = $v['code'];

		if (isset($values[$code.'-dt'])) {
			$v['value'] = Date::ft_parse($values[$code.'-dt']);
		}

		$dt = isset($v['value']) ? $v['value'] : $v['default'];

		if (!isset($v['hours'])) {
			$hours = array();
			for ($i=0; $i<24; $i++) {
				$hours[$i] = strlen($i) < 2 ? '0'.$i : $i;
			}
			$v['hours'] = $hours;
		}

		$hh = ((isset($values[$code.'-hh']) ? $values[$code.'-hh'] : date('H', $dt)) - date('H', 0));
		if ($hh < 0) { $hh+= 24; }
		if (!isset($v['hours'][$hh])) {
			reset($v['hours']);
			$hh = key($v['hours']);
		}

		$step = isset($v['step']) ? $v['step'] : 5;
		if (!isset($v['minutes'])) {
			$minutes = array();
			for ($i=0; $i<60; $i+=$step) {
				$minutes[$i] = strlen($i) < 2 ? '0'.$i : $i;
			}
			$v['minutes'] = $minutes;
		}

		$mm = isset($values[$code.'-mm']) ? $values[$code.'-mm'] : round(date('i', $dt) / $step) * $step;
		if (!isset($v['minutes'][$mm])) {
			reset($v['minutes']);
			$mm = key($v['minutes']);
		}

		$v['value'] = mktime($hh, $mm, 00, date('n', $dt), date('d', $dt), date('Y', $dt)) + date('H', 0)*60*60;
		$v['valid'] = TRUE;
	}

	function build() {
		$v = &$this->data;

		$date = new Date($v);

		$hh = date('H', $v['value']) * 1;
		$step = isset($v['step']) ? $v['step'] : 5;
		$mm = round(date('i', $v['value']) / $step) * $step;
		$dt = date('Y-m-d', $v['value']);

		if (!isset($v['hours'])) { $v['hours'] = []; }
		if (!isset($v['hours'][$hh])) { $v['hours'][$hh] = strlen($hh) < 2 ? '0'.$hh : $hh; ksort($v['hours']); }

		if (!isset($v['minutes'])) { $v['minutes'] = []; }
		if (!isset($v['minutes'][$mm])) { $v['minutes'][$mm] = strlen($mm) < 2 ? '0'.$mm : $mm; ksort($v['minutes']); }

		$code = $v['code'];

		$hour = new Select(['code'=>$code.'-hh', 'value'=>$hh, 'values'=>$v['hours'], 'width'=>70, 'readonly'=>isset($v['readonly']) ? $v['readonly'] : 0]);
		$minute = new Select(['code'=>$code.'-mm', 'value'=>$mm, 'values'=>$v['minutes'], 'width'=>70, 'readonly'=>isset($v['readonly']) ? $v['readonly'] : 0]);
		$date = new Line(['code'=>$code.'-dt', 'value'=>$dt, 'subtype'=>'date']);

		return '<div class="form-inline">'.$hour->build().' '.$minute->build().' '.$date->build().'</div>';
	}
}