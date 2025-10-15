<?

w('input-text');

function parse_rich(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = trim($v['value']);
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : '';
	}
	$v['columns'] = 2;
}

//w('summernote');
function input_rich($v) {
//	w('ckeditor');
	w('tinymce');
	if (isset($v['class'])) {
		$v['class'].= ' rich';
	} else {
		$v['class'] = 'rich';
	}

	return input_text($v);
}

?>