<?

function parse_droplink(&$r) {
	$r['value'] = isset($r['value']) ? $r['value'] : (isset($r['default']) ? $r['default'] : '');
	$r['valid'] = array_key_exists($r['value'], $r['values']);
}

function input_droplink($r, $css='dropdown') {
	$css = kv($r, 'css', $css);

	$back = '
<div class="'.$css.'">
	<a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="text-muted">'.$r['name'].':</span> <b>'.kv($r['values'], $r['value'], $r['value']).'</b> <i class="fa fa-caret-down" aria-hidden="true"></i></a>
	<ul class="dropdown-menu">';

	foreach ($r['values'] as $k=>$v) {
		if ($k != $r['value']) {
			$back.= '
<li><a href="'.$r['links'][$k].'">'.$v.'</a></li>';
		}
	}

	$back.= '
	</ul>
</div>';
	return $back;
}

?>