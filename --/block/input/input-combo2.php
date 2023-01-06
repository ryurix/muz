<?

w('input-combo');

function parse_combo2(&$v) {
	parse_combo($v);
}

function input_combo2($v) {
	$width = 400;
	if (isset($v['width'])) {
		$width = $v['width'];
		unset($v['width']);
	}
	$back = '<div class="form-group" style="width:'.$width.'px">';

	$more = isset($v['more']) ? $v['more'] : '';
	if (isset($v['placeholder'])) { $more.= ' data-placeholder="'.$v['placeholder'].'"'; }
	$v['more'] = $more.' tabindex="-1"';

	$v['class'] = isset($v['class']) ? $v['class'].' chosen-select' : 'chosen-select';

	return $back.input_combo($v).'</div>';
}

?>