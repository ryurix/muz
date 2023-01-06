<?

w('ft');
w('input-line');

function parse_date(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = strlen($v['value']) ? ft_parse($v['value']) : null;
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : now();
	}	
}

function input_date($v) {
	$readonly = isset($v['readonly']) && $v['readonly'];

	if (!isset($v['id'])) {
		$v['id'] = $v['code'].'-dt';
	}
	$v['class'] = isset($v['class']) ? $v['class'].' input-date' : 'input-date';
	if (!$readonly) { $v['class'].= ' stick-right'; }

	if (!isset($v['placeholder'])) {
		$v['placeholder'] = 'ДД.ММ.ГГГГ';
	}
	$v['value'] = is_null($v['value']) ? '' : ft($v['value']);

	if (!$readonly && kv($v, 'context', 0)) {
		$more = isset($v['more']) ? $v['more'].' ' : '';
		$more.= 'oncontextmenu="Calendar(\''.$v['code'].'-dt\'); return false;"';
		$v['more'] = $more;
	}

	$back = input_line($v);

	if (!$readonly && kv($v, 'button', 1)) {
		$back.= '<button class="btn btn-default btn-xs stick-left" type="button" onclick="Calendar(\''.$v['code']
.'-dt\'); return false;"><i class="fa fa-calendar"></i></button>';
	}
	return $back;
}

?>