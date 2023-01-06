<?

function parse_file0(&$v) {
	global $config;
	$key = $v['code'];

	if (isset($_REQUEST[$key.'-value'])) {
		$value = $_REQUEST[$key.'-value'];
	} else {
		$value = isset($v['value']) ? $v['value'] : (isset($v['default']) ? $v['default'] : '');
	}

	if (strlen($value)) {
		$file = $config['root'].substr($value, 1);
		if (is_file($file)) {
			if (isset($_REQUEST[$key.'-erase'])) {
				unlink($file);
				$value = '';
			}
		} else {
			$value = '';
		}
	}

	if (isset($_FILES[$key]['name']) && $_FILES[$key]['error'] === 0) {
		$tempname = $_FILES[$key]['tmp_name'];

		w('clean');
		$filename = str2url($_FILES[$key]['name']);
		if (strlen($filename) == 0) {
			$filename = str2url($_FILES[$key]['tmp_name']);
		}
	} else {
		$tempname = isset($_REQUEST[$key.'-url']) ? $_REQUEST[$key.'-url'] : '';
		$filename = basename($tempname);
	}

	$v['value'] = $tempname;
	$v['filename'] = $filename;
	$v['valid'] = TRUE;
}

function input_file0($v) {
	$readonly = isset($v['readonly']) && $v['readonly'];

	$back = '';
	if (!$readonly) {
	// echo '<input type=file name="'.$v['code'].'" />'."\n";
		$back.= '<span class="btn btn-info fileinput-button">
	<i class="fa fa-plus"></i> <span>Выбрать файл...</span>
	<input type="file" name="'.$v['code'].'">
	</span> <input type="text" name="'.$v['code'].'-url" class="input-mini form-control" style="width:300px" placeholder="http://...">';
	}
	return $back;
}

?>