<?

w('input-text');

function parse_richi(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = trim($v['value']);
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : '';
	}
	$v['columns'] = 2;
}

/*
function input_richi($v) {
	w('ckeditor');

	return '<div class="rich" contenteditable="true">'.$v['value.'].'</div>';
}
*/

function input_richi($v) {
	w('ckeditor');
	if (!isset($v['id'])) {
		$v['id'] = $v['code'];
	}
	if (isset($v['class'])) {
		$v['class'].= ' richi';
	} else {
		$v['class'] = 'richi';
	}
	if (isset($v['more'])) {
		$v['more'].= ' contenteditable="true"';
	} else {
		$v['more'] = 'contenteditable="true"';
	}

	return input_text($v).'<script>CKEDITOR.inline("'.$v['id'].'");</script>';
}

?>