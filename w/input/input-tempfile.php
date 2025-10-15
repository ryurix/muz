<?

function parse_tempfile(&$v) {
	$v['valid'] = 1;
	$key = $v['code'];
	if (isset($_FILES[$key]['name']) && $_FILES[$key]['error'] === 0) {
		$v['filename'] = $_FILES[$key]['name'];
		$value = $_FILES[$key]['tmp_name'];
	} else {
		$value = isset($_REQUEST[$key.'-url']) ? $_REQUEST[$key.'-url'] : '';
		$v['filename'] = basename($value);
	}
	$v['value'] = $value;
}

function input_tempfile($v) {
	if (!$disabled) {
		return '<span class="btn fileinput-button btn-'.(isset($v['iv']) && $v['iv'] ? 'danger' : 'info').'">'
.'<i class="icon-plus icon-white"></i> '
.'<span>Выбрать файл...</span>'
.'<input type="file" name="'.$v['code'].'">'
.'</span>'
.' <input type="text" name="'.$v['code'].'-url" class="input-large form-control" style="width:100px" placeholder="http://...">';
	} else {
		return '';
	}
}

?>