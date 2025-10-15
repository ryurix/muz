<?

function parse_city(&$r) {
	global $config;

	w('chosen.js');

	if (isset($r['value'])) {
		$city = cache_load('city');

		$value = trim($r['value']);
		$r['value'] = $value;
		$r['valid'] = array_key_exists($value, $city);
	} else {
		$r['value'] = isset($r['default']) ? $r['default'] : 0;
		$r['valid'] = 1;
	}	
}

function input_city($r) {
	global $config;
	$s = '<div class="form-group" style="width:'.(isset($r['width']) ? $r['width'] : 300).'px">';

	$city = cache_load('city');

	$width = isset($r['width']) ? $r['width'] : 350;
	$class = isset($r['class']) ? ' '.$r['class'] : '';
	$more = isset($r['more']) ? ' '.$r['more'] : '';

	$s.= '<select name="'.$r['code'].'" data-placeholder="'
	.(isset($r['placeholder']) ? $r['placeholder'] : 'Выберите город')
	.'" style="width:'.$width.'px;" class="chosen-select'.$class.'" tabindex="-1"'.$more.'>'."\n";

	if (!isset($city[$r['value']])) {
		$s.= '<option value="" SELECTED></option>'."\n";
	}

	//asort($city);
	foreach($city as $k=>$v) {
		if ($k < 0) { continue; }
		$s.= '<option value="'.$k.'"';
		if ($r['value'] == $k) {
			$s.= ' SELECTED';
		}
		$s.= '>'.$v."</option>\n";
	}
	$s.= '</select></div>';

	return $s;
}

?>