<?

function parse_rate(&$v) {
	$v['valid'] = 1;
	if (isset($v['value']) && !in_array($v['value'], array(1,2,3,4,5))) {
		unset($v['value']);
	} 

	if (!isset($v['value'])) {
		if (isset($v['default']) && $v['default']) {
			$v['value'] = $v['default'];
		} else {
			$v['value'] = 0;
		}
	}
}

function input_rate($v) {
	$key = $v['code'];
	$value = $v['value'];
	$readonly = isset($v['readonly']) && $v['readonly'] ? ' disabled' : '';

	$back = '<fieldset class="rate">
	<input type="radio" id="'.$key.'-s5" name="'.$key.'" value="5"'.($value == 5 ? ' checked' : '').$readonly.'/><label class="full" for="'.$key.'-s5" title="Отлично"></label>
	<input type="radio" id="'.$key.'-s4" name="'.$key.'" value="4"'.($value == 4 ? ' checked' : '').$readonly.'/><label class="full" for="'.$key.'-s4" title="Хорошо"></label>
	<input type="radio" id="'.$key.'-s3" name="'.$key.'" value="3"'.($value == 3 ? ' checked' : '').$readonly.'/><label class="full" for="'.$key.'-s3" title="Средне"></label>
	<input type="radio" id="'.$key.'-s2" name="'.$key.'" value="2"'.($value == 2 ? ' checked' : '').$readonly.'/><label class="full" for="'.$key.'-s2" title="Плохо"></label>
	<input type="radio" id="'.$key.'-s1" name="'.$key.'" value="1"'.($value == 1 ? ' checked' : '').$readonly.'/><label class="full" for="'.$key.'-s1" title="Ужасно"></label>
</fieldset>';

	return $back;
}

?>