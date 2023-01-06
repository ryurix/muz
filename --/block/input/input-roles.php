<?

function parse_roles(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$roles = w('list-roles');
		$value = array();
		foreach ($roles as $k=>$role) {
			if (array_key_exists($k, $v['value'])) {
				$value[] = $k;
			}
		}
		$v['value'] = implode(' ', $value);
	} else {
		if (isset($_REQUEST[$v['code'].'--'])) {
			$v['value'] = '';
		} else {
			$v['value'] = isset($v['default']) ? $v['default'] : '';
		}
	}
}

function input_roles($v) {
	$back = '<table id="input-roles">';

	$roles = is_array($v['value']) ? $v['value'] : explode(' ', $v['value']);

	$list = w('list-roles');
	$i = 0;
	foreach ($list as $k=>$role) {
		$i++;
		if ($i & 1) {
			$back.= '<tr>';
		}
		$back.= '<td><input type="checkbox" id="role-'.$k.'" name="'.$v['code'].'['.$k.']" ';
		if (in_array($k, $roles)) {
			$back.= 'CHECKED';
		}
		$back.= '> <label for="role-'.$k.'">'.$role.'</label></td>';
		if (!($i & 1)) {
			$back.= "</tr>\n";
		}
	}

	$back.= '</table><input type="hidden" name="'.$v['code'].'--" value="">';
	return $back;
}

?>