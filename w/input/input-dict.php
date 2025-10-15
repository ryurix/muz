<?

w('clean');

function parse_dict(&$r) {
	$r['valid'] = 1;

	$code = $r['code'];

	if (isset($_REQUEST[$code.'k']) && is_array($_REQUEST[$code.'k'])
	&&	isset($_REQUEST[$code.'v']) && is_array($_REQUEST[$code.'v'])
	&& !(isset($r['readonly']) && $r['readonly'])) {
		$a = array();
		foreach ($_REQUEST[$code.'k'] as $k=>$v) {
			if (strlen($v)) {
				$a[$v] = $_REQUEST[$code.'v'][$k];
			}
		}
		$r['value'] = $a;
	} else {
		$default = isset($r['default']) ? $r['default'] : '';
		if (!is_array($default)) {
			$default = strlen($default) > 5 ? php_decode($default) : array();
		}
		$r['value'] = $default;
	}
}

function input_dict($r) {
	$s = '<div';

	$code = $r['code'];

	$class = array('input-dict');
	if (isset($r['class'])) { $class[] = $r['class']; }
	if (!$r['valid']) { $class[] = 'is-invalid'; }
	$s.= ' class="'.implode(' ', $class).'"';

	$id = isset($r['id']) ? $r['id'] : $code.'i';
	$s.= ' id="'.$id.'"';
	if (isset($r['more'])) { $s.= ' '.$r['more']; }
	$s.= '>';
	$value = is_array($r['value']) ? $r['value'] : php_decode($r['value']);
	if (isset($r['readonly']) && $r['readonly']) {
		$plus1 = '';
		$plus2 = '';
	} else {
		$plus1 = '<button type="button" onclick=\'document.getElementById("'.$id.'").appendChild(document.getElementById("'.$id.'").childNodes[';
		$plus2 = '].cloneNode(true));\'><i class="fa fa-plus-circle"></i></button>';
		$value = $value + array(''=>'');
	}

	$count = 0;
	foreach ($value as $k=>$v) {
		$s.= '<div>
<input name="'.$code.'k[]" value="'.addcslashes($k, '"').'"><input name="'.$code.'v[]" value="'.addcslashes($v, '"').'">
'.$plus1.$count.$plus2.'
</div>';
		$count++;
	}

	return $s;
}

?>