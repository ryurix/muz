<?

w('clean');

function parse_adres(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = trim($v['value']);
		if (isset($v['exp'])) {
			$v['valid'] = preg_match('/^'.$v['exp'].'$/ui', $v['value']) ? 1 : 0;
		}
		if (isset($v['min']) && mb_strlen($v['value'])<$v['min']) {
			$v['valid'] = 0;
		}
		$code = $v['code'];
		$lat = clean_number(kv($_REQUEST, $code.'-lat'));
		$lon = clean_number(kv($_REQUEST, $code.'-lon'));
		if ($v['valid'] && $lat && $lon) {
			$v['lat'] = $lat;
			$v['lon'] = $lon;
		}
	} else {
		if (isset($v['default'])) {
			$v['value'] = $v['default'];
		} else {
			$v['value'] = '';
			$v['valid'] = !isset($v['exp']) && !isset($v['min']);
		}
	}
}

function input_adres($v, $class = '') {
	global $config;

	$code = $v['code'];

	$back = '<input type="text" name="'.$v['code'].'" id="adres-txt"';

	$class = strlen($class) ? array($class) : array();
	$class[] = 'form-control';
	if (isset($v['class'])) { $class[] = $v['class']; }
	if (isset($v['iv']) && $v['iv']) { $class[] = 'is-invalid'; }
	if (count($class)) {
		$back.=' class="'.implode(' ', $class).'"';
	}

	$style = array();
	if (isset($v['width'])) { $style[] = 'width:'.$v['width'].(ctype_digit($v['width'].'') ? 'px' : '');	}
	if (count($style)) {
		$back.=' style="'.implode(';', $style).';"';
	}

	if (isset($v['placeholder'])) { $back.=' placeholder="'.$v['placeholder'].'"'; }
	if (isset($v['readonly']) && $v['readonly']) { $back.=' disabled="disabled"'; }
	if (isset($v['required']) && $v['required']) { $back.= ' required aria-required="true"'; }
	if (isset($v['more'])) { $back.= ' '.$v['more']; }

	$value = str_replace('"', '&quot;', $v['value']);
	$more = isset($v['more']) ? ' '.$v['more'] : '';

	$back.=' value="'.$value.'"'.$more.'>
<input type="hidden" name="'.$code.'-lat" id="adres-lat">
<input type="hidden" name="'.$code.'-lon" id="adres-lon">
<link href="https://cdn.jsdelivr.net/npm/suggestions-jquery@19.4.2/dist/css/suggestions.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/suggestions-jquery@19.4.2/dist/js/jquery.suggestions.min.js"></script>
<script>
$("#adres-txt").suggestions({
	token: "'.kv($config, 'dadata-token').'",
	type: "ADDRESS",

	onSelect: function(suggestion) {
		console.log(suggestion);
		$("#adres-lat").val(suggestion.data.geo_lat);
		$("#adres-lon").val(suggestion.data.geo_lon);
	}
});
</script>';
	return $back;
}

?>