<? namespace Flydom\Input;

class Multibox extends Input
{
	function parse($values = null) {
		$r = &$this->data;

		$r['valid'] = 1;
		if (isset($_REQUEST[$r['code'].'--'])) {
			$value = array();
			if (isset($_REQUEST[$r['code']])) {
				$data = $_REQUEST[$r['code']];
				if (is_array($data)) {
					foreach ($r['values'] as $k=>$i) {
						if (isset($data[$k])) {
							$value[] = $k;
						}
					}
				}
			}
			$r['value'] = \Flydom\Cache::array_encode($value);
		} else {
			if (isset($r['default'])) {
				$r['value'] = $r['default'];
			} else {
				$r['value'] = '';
				$r['valid'] = 0;
			}
		}
	}


	function build($class = '') {
		$r = &$this->data;

		$disabled = isset($r['readonly']) && $r['readonly'] ? ' disabled=disabled' : '';

		$cols = isset($r['cols']) ? $r['cols'] : 1;
		if ($cols > 1) {
			$box = 'table';
			$box2 = 'td';
		} else {
			$box = 'div';
			$box2 = 'div';
		}
		$s = "<$box class=\"form-multi\">";
		$i = 0;

		$value = is_array($r['value']) ? $r['value'] : \Flydom\Cache::array_decode($r['value']);

		$values = $cols > 1 ? self::array_for_columns($r['values'], $cols) : $r['values'];
		foreach($values as $k=>$v) {
			$i++;
			if (($i - 1) % $cols == 0 && $cols > 1) $s.= '<tr>';

			$s.= '<'.$box2.'><input type="checkbox" id="'.$r['code'].'-'.$k.'" name="'.$r['code'].'['.$k.']" ';
			if (in_array($k, $value)) {
				$s.= 'CHECKED';
			}
			$s.= $disabled.'> <label for="'.$r['code'].'-'.$k.'">'.$v.'</label></'.$box2.'>';

			if ($i % $cols == 0 && $cols > 1) $s.= "</tr>\n";
		}
		if ($i % $cols != 0) {
			for (;$i % $cols !=0;$i++) {
				$s.= '<td>&nbsp;</td>';
			}
			$s.= "</tr>\n";
		}
		$s.= "</$box>";
		$s.= '<input type=hidden name="'.$r['code'].'--" />';

		return $s;
	}

	static function array_for_columns($array, $columns) {
		$back = array();

		$max = count($array);
		$keys = array_keys($array);

		$len = array();
		for($i=0; $i<$columns; $i++) {
			$len[$i] = floor($max/$columns);
			if ($max%$columns > $i) {
				$len[$i]++;
			}
		}

		for($i=0; $i<$max; $i++) {
			$col = $i%$columns;
			$base = $col == 0 ? 0 : $base + $len[$col-1];
			$j = $base+floor($i/$columns)%$len[$col];
			$key = $keys[$j];
			$back[$key] = $array[$key];
		}

		return $back;
	}
}