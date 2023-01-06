<?

function parse_checklink(&$r) {
	$r['value'] = isset($r['value']) ? $r['value'] : kv($r, 'default', 0);
	$r['value'] = $r['value'] ? 1 : 0;
	$r['valid'] = 1;
}

function input_checklink($r, $css='btn btn-default') {
	$css = kv($r, 'css', $css);

	$link = kv($r['links'], $r['value'], kv($r, 'link'));
	$value = kv($r, $r['value']);

	$back = '<a href="'.$link.'" class="'.$css.'">'.$value.'</a>';

	return $back;
}

?>