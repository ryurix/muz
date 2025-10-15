<?

function parse_checkbox2(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = kv($v, 'checked', 1);
	} else {
		if (isset($_REQUEST[$v['code'].'--'])) {
			$v['value'] = 0;
		} else {
			if (isset($v['default']) && $v['default']) {
				$v['value'] = $v['default'];
			} else {
				$v['value'] = 0;
			}
		}
	}
}

function input_checkbox2($v, $class = '') {
	$key = $v['code'];
	$id = kv($v, 'id', 'box-'.$key);

	$back = '<div class="custom-control custom-checkbox'.(strlen($class) ? ' '.$class : '').'"><input type=checkbox name="'.$key.'" id="'.$id.'"';
	if (isset($v['readonly']) && $v['readonly']) {
		$back.= ' readonly';
	}

	$checked = isset($v['checked']) ? $v['checked'] : 1;
	if ($v['value'] == $checked) { $back.= ' CHECKED'; }
	$back.= ' class="custom-control-input"';
	if (isset($v['more'])) { $back.= ' '.$v['more']; }
	$back.= '>';

	if (isset($v['label'])) {
		$class = array('custom-control-label');
		if (isset($v['class'])) { $class[] = $v['class']; }
		$back.= '<label class="'.implode(' ', $class).'" for="'.$id.'">'.$v['label'].'</label>';
	}
	$back.= '<input type=hidden name="'.$key.'--" /></div>';
	return $back;
}

?>