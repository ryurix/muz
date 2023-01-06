<?

function parse_cire(&$r) {
	global $config;

	w('chosen.js');

	if (isset($r['value'])) {
		$city = isset($r['values']) ? $r['values'] : cache_load('city');

		$value = trim($r['value']);
		$r['value'] = $value;
		$r['valid'] = array_key_exists($value, $city);
	} else {
		$r['value'] = isset($r['default']) ? $r['default'] : 0;
		$r['valid'] = 1;
	}	
}

function input_cire($r) {
	global $config;

	$city = isset($r['values']) ? $r['values'] : cache_load('city');

	$id = isset($r['id']) ? ' id="'.$r['id'].'"' : '';
	$width = isset($r['width']) ? $r['width'] : 350;
	$class = isset($r['class']) ? ' '.$r['class'] : '';
	$more = isset($r['more']) ? ' '.$r['more'] : '';

	$s = '<select name="'.$r['code'].'" data-placeholder="'
	.(isset($r['placeholder']) ? $r['placeholder'] : 'Выберите город')
	.'" class="city_ddl select_sort'.$class.'"'.$id.$more.'>'."\n";

	if (!isset($city[$r['value']])) {
		$s.= '<option value="" SELECTED></option>'."\n";
	}

	//asort($city);
	foreach($city as $k=>$v) {
		$s.= '<option value="'.$k.'"';
		if (strcmp($r['value'], $k) == 0) {
			$s.= ' SELECTED';
		}
		if ($k > 0) {
			$v = ' &nbsp; '.$v;
		}
		$s.= '>'.$v."</option>\n";
	}
	$s.= '</select>';

	return $s;
}

?>