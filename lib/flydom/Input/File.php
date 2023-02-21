<? namespace Flydom\Input;

class File extends Input
{

	function parse($values = null) {
		$v = &$this->data;

		$key = $v['code'];

		if (isset($values[$key.'-value'])) {
			$value = $values[$key.'-value'];
		} else {
			$value = isset($v['value']) ? $v['value'] : (isset($v['default']) ? $v['default'] : '');
		}

		if (strlen($value)) {
			$file = \Config::$root.substr($value, 1);
			if (is_file($file)) {
				if (isset($values[$key.'-erase'])) {
					unlink($file);
					$value = '';
				}
			} else {
				$value = '';
			}
		}

		if (isset($_FILES[$key]['name']) && $_FILES[$key]['error'] === 0) {
			$tempname = $_FILES[$key]['tmp_name'];

			$filename = \Flydom\Clean::str2url($_FILES[$key]['name']);
			if (strlen($filename) == 0) {
				$filename = \Flydom\Clean::str2url($_FILES[$key]['tmp_name']);
			}
		} else {
			$tempname = isset($values[$key.'-url']) ? $values[$key.'-url'] : '';
			$filename = basename($tempname);
		}

		$v['value'] = $tempname;
		$v['filename'] = $filename;
		$v['valid'] = TRUE;
	}

	function build($class = '') {
		$v = &$this->data;
		$readonly = isset($v['readonly']) && $v['readonly'];

		$back = '';
		if (!$readonly) {
		// echo '<input type=file name="'.$v['code'].'" />'."\n";
			$back.= '<input type="file" class="form-control-file" name="'.$v['code'].'">';
		// <i class="fa fa-plus"></i> <span>Выбрать файл...</span>

		}
		return $back;
	}

}

?>