<?

function parse_button(&$v) {
	$v['valid'] = FALSE;
	$v['value'] = 0;

	if (is_array($v['count'])) {
		$btns = $v['count'];
	} else {
		$btns = array();
		for ($i=1; $i <= $v['count']; $i++) {
			$btns[] = $i;
		}
	}

	foreach ($btns as $i) {
		if (isset($_REQUEST[$v['code'].$i])) {
			$v['valid'] = TRUE;
			$v['value'] = $i;
			break;
		}
	}

	if (!$v['valid'] && isset($v['default'])) {
		$v['valid'] = TRUE;
		$v['value'] = $v['default'];
	}
}

function input_button($v) {
	if (is_array($v['count'])) {
		$btns = $v['count'];
	} else {
		$btns = array();
		for ($i=1; $i <= $v['count']; $i++) {
			$btns[] = $i;
		}
	}

	$back = array();
	foreach ($btns as $i) {
		$b = '<button name="'.$v['code'].$i.'"';
		$class = array('btn');
		if (isset($v['class'])) {
			if (is_array($v['class'])) {
				if (isset($v['class'][$i])) {
					$class[] = $v['class'][$i];
				} else {
					$class[] = 'btn-default';
				}
			} else {
				$class[] = $v['class'];
			}
		} else {
			$class[] = 'btn-default';
		}
		$b.= ' class="'.implode(' ', $class).'"';
		if (isset($v['id'][$i])) {
			$b.= ' id="'.$v['id'][$i].'"';
		}
		if (isset($v['onclick'][$i])) {
			$b.= ' onclick="'.addcslashes($v['onclick'][$i], '"').'"';
		}
		if (isset($v['confirm'][$i])) {
			$b.= ' onclick="return confirm(\''.str_replace(array('"', "'"), '`', $v['confirm'][$i]).'\');"';
		}
		$b.= '>'.$v[$i]."</button>\n";
		$back[] = $b;
	}
	return implode(kv($v, 'glue', "\n"), $back);
}

?>