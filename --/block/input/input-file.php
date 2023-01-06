<?

function parse_file(&$v) {
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

	if (strlen($tempname) && strlen($filename)) {
		$value = $_FILES[$key]['tmp_name'];
		$path = isset($v['path']) ? $v['path'] : '';
		if (strlen($path)) {
			$dir = $config['root'].substr($path, 1);
			if (!is_dir($dir)) {
				mkdir($dir);
			}
			move($value, $dir.$filename); // default file action
			$value = $path.$filename;
		}
	}

	$v['value'] = $value;
	$v['valid'] = TRUE;
}

function input_file($v) {
	$readonly = isset($v['readonly']) && $v['readonly'];

	$back = '';
	if (!$readonly) {
	// echo '<input type=file name="'.$v['code'].'" />'."\n";
		$back.= '<span class="btn btn-info fileinput-button">
	<i class="fa fa-plus"></i> <span>Выбрать файл...</span>
	<input type="file" name="'.$v['code'].'">
	</span> <input type="text" name="'.$v['code'].'-url" class="input-mini form-control" style="width:100px" placeholder="http://...">';
	}
	if (isset($v['value']) && strlen($v['value']) > 0) {
		$back.= '<input type=hidden name="'.$v['code'].'-value" value="'.$v['value'].'" />';
		$back.= ' <a href="'.$v['value'].'" class="btn btn-warning"><i class="fa fa-file-o"></i></a>';
		if (!$readonly) {
			$back.= ' <button type="submit" name="'.$v['code'].'-erase" value="удалить" class="btn btn-default"><i class="fa fa-trash"></i></button>';
		}
	}
	return $back;
}

?>