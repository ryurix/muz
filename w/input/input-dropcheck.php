<?

//w('input-line');
//w('clean');

function parse_dropcheck(&$r) {
	$r['valid'] = 1;
	if (isset($_REQUEST[$r['code'].'--'])) {
		$r['value'] = array();
		if (isset($_REQUEST[$r['code']])) {
			$data = $_REQUEST[$r['code']];
			if (is_array($data)) {
				foreach ($r['values'] as $k=>$v) {
					if (isset($data[$k])) {
						$r['value'][] = $k;
					}
				}
			}
		}
	} else {
		if (isset($r['default'])) {
			$r['value'] = $r['default'];
		} else {
			$r['value'] = array();
			$r['valid'] = 0;
		}
	}
}

function input_dropcheck($r, $class = 'btn-default btn-block') {
	$id = kv($r, 'id', $r['code'].'-dropcheck');
	$placeholder = isset($r['placeholder']) ? $r['placeholder'] : 'Выберите...';
	$disabled = kv($r, 'readonly', 0) ? ' disabled=disabled' : '';

	$s = '
<div class="dropcheck">
	<a class="btn '.$class.' dropdown-toggle" href="#" role="button" id="'.$id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
'.$placeholder.'
	</a>

	<div class="dropdown-menu" aria-labelledby="'.$id.'">';

	$class = isset($r['item-class']) ? ' class='.$r['item-class'] : '';
	foreach ($r['values'] as $k=>$v) {
		$checked = in_array($k, $r['value']) ? ' CHECKED' : '';

		$s.= '
<label class="dropdown-item"><input type="checkbox" name="'.$r['code'].'['.$k.']"'.$class.$checked.$disabled.'> '.$v.'</label>';
	}

    $s.= '
	</div>
	<input type=hidden name="'.$r['code'].'--">
</div>';

	return $s;
}

?>