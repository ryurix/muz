<?

/*
 *	Copyright flydom.ru
 *	Version 1.1.2016-05-23
 */

function parse_pic(&$v) {
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
		if (isset($v['filename'])) {
			$filename = $v['filename'];
		}
		w('pic');
		$filename = pic_change_ext($filename, pic_ext($tempname));

		$value = $_FILES[$key]['tmp_name'];
		$path = isset($v['path']) ? $v['path'] : '';
		if (strlen($path)) {
			$dir = $config['root'].substr($path, 1);
			if (!is_dir($dir)) {
				mkdir($dir);
			}
	// Pic 
			$pic = pic_load($tempname);
			if (!isset($v['x']) || ($pic['x'] == $v['x'] && $pic['y'] == $v['y'])) {
				move($tempname, $dir.$filename);
			} else {
				if (isset($v['background'])) {
					$pic = pic_into($pic, $v['x'], $v['y'], $v['background']);
				} else {
					if (isset($v['mode']) && $v['mode'] == 'fit') {
						$pic = pic_fit($pic, $v['x'], $v['y'], isset($v['enlarge']) ? $v['enlarge'] : 0);
					} elseif (isset($v['mode']) && $v['mode'] == 'crop') {
						$pic = pic_crop($pic, $v['x'], $v['y'],
							isset($v['crop']) ? $v['crop'] : 2,
							isset($v['enlarge']) ? $v['enlarge'] : 0);
					} else {
						$pic = pic_into($pic, $v['x'], $v['y']);
					}
				}
				$filename = pic_change_ext($filename, '.jpg');

				pic_jpeg($pic, $dir.$filename, isset($v['quality']) ? $v['quality'] : 90, FALSE);
			}

			if (isset($v['mini'])) { // Mini
				$mini = pic_crop($pic, $v['mini-x'], $v['mini-y']);
				$delim = substr($v['mini'], -1);
				$minifile = $dir.$v['mini'].($delim == '_' || $delim == '-' ? pic_change_ext($filename, '.jpg') : '.jpg');
				pic_jpeg($mini, $minifile, 70);
			}
			$value = $path.$filename;
		}

		$v['filename'] = $filename;
	}

	$v['value'] = $value;
	$v['valid'] = TRUE;
}

function input_pic($v) {
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
		$back.= ' <a href="'.$v['value'].'" class="btn btn-warning"><i class="fa fa-file-image-o"></i></a>';
		if (!$readonly) {
			$back.= ' <button type="submit" name="'.$v['code'].'-erase" value="удалить" class="btn btn-default"><i class="fa fa-trash"></i></button>';
		}
	}
	return $back;
}

?>