<?

function parse_files(&$r) {
	global $config;
	$r['valid'] = 1;

	$value = isset($r['value']) ? $r['value'] : (
		isset($r['default']) ? $r['default'] : array()
	);
	if (!is_array($value)) {
		$value = strlen($value) ? php_decode($value) : array();
	}

	$key = $r['code'].'-';

	foreach ($value as $k=>$v) {
		if (isset($_REQUEST[$key.$k])) {
			$trash = \Config::ROOT.substr($v['href'], 1);
			if (is_file($trash)) {
				unlink($trash);
			}
			unset($value[$k]);
		}
	}

	$new = array();

	if (isset($_FILES[$key]) && is_array($_FILES[$key]['name'])) {
		foreach ($_FILES[$key]['name'] as $i=>$name) {
			if ($_FILES[$key]['error'][$i] === 0) {
				$new[$name] = $_FILES[$key]['tmp_name'][$i];
			}
		}
	}

	$key.= 'url';
	if (isset($_REQUEST[$key]) && is_array($_REQUEST[$key])) {
		foreach ($_REQUEST[$key] as $i=>$file) {
			if (strlen($file)) {
				$new[basename($file)] = $file;
			}
		}
	}

	if (count($new)) {
		$path = isset($r['path']) ? '/'.trim($r['path'], '/').'/' : '';
		$dir = \Config::ROOT.substr($path, 1);
		if (!is_dir($dir)) {
			mkdir($dir);
		}
		w('clean');

		foreach ($new as $name=>$file) {
			$filename = str2url($name);
			copy($file, $dir.$filename);

			$value[] = array(
				'name'=>$name,
				'href'=>$path.$filename,
			);
		}
	}

	$r['valid'] = 1;
	$r['value'] = php_encode($value);
}

function input_files($r) {
	$value = php_decode($r['value']);

	$s = '<div class="group"><div class="filelist">';
	foreach ($value as $k=>$v) {
		$del = 'Удалить '.$v['name'].'?';
		$s.= ' <a href="'.$v['href'].'" class="label label-default" rel="lightbox">'.$v['name'].'</a>'
		.' <input type="checkbox" name="'.$r['code'].'-'.$k.'" title="'.$del.'"> &nbsp; ';
	}
	$s.= '</div>'
	.'<span class="btn btn-info fileinput-button filesel">'
	.'<i class="icon-plus icon-white"></i> '
	.'<span>Выбрать файл...</span>'
	.'<input type="file" name="'.$r['code'].'-[]" multiple>'
	.'</span>'
	.' <input type="text" name="'.$r['code'].'-url[]" class="input-small form-control filesel" style="width:200px" placeholder="http://...">'
	.' <button class="btn btn-default fileplus"><i class="fa fa-plus"></i></button>'
	.'</div>';

	return $s;
}

?>