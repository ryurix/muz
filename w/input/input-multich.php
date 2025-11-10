<?

//w('input-line');
//w('clean');

function parse_multich(&$r) {
	$r['valid'] = 1;
	if (isset($_REQUEST[$r['code'].'--'])) {
		$value = array();
		if (isset($r['value']) && is_array($r['value'])) {
			foreach ($r['value'] as $i) {
				if (isset($r['values'][$i])) {
					$value[] = $i;
				}
			}
		}
		$r['value'] = $value;
	} else {
		if (isset($r['default'])) {
			$r['value'] = $r['default'];
		} else {
			$r['value'] = array();
			$r['valid'] = 0;
		}
	}
}

function input_multich($r) {
	$placeholder = isset($r['placeholder']) ? $r['placeholder'] : 'Выберите город...';

	$s = '<div class="form-group"';
	$style = array();
	if (isset($v['width'])) { $style[] = 'width:'.$v['width'].(ctype_digit($v['width'].'') ? 'px' : '');	}
	if (count($style)) { $s.=' style="'.implode(';', $style).';"'; }
	//style="width:'.(isset($r['width']) ? $r['width'] : 366).'px">';
	$s.= '><input type=hidden name="'.$r['code'].'--">';
	$s.= '<select data-placeholder="'.$placeholder.'" class="chosen'.(isset($r['class']) ? ' '.$r['class'] : '').'" multiple name="'.$r['code'].'[]"'
	.(isset($r['width']) ? ' style="width:'.$r['width'].'px"':'').'>';

	foreach($r['values'] as $k=>$v) {
		$s.= '<option value="'.$k.'"';
		if (is_array($r['value']) && in_array($k, $r['value'])) {
			$s.= ' SELECTED';
		}
		$s.= '>'.$v.'</option>';
	}

	$s.= '</select></div>
<script type="text/javascript">$(".chosen").chosen();</script>';

	return $s;
}

?>