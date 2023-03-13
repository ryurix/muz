<?

function parse_pics(&$r) {
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
			if (isset($v['href'])) {
				$trash = $config['root'].substr($v['href'], 1);
				unlink($trash);
			}
			if (isset($v['mini'])) {
				$trash = $config['root'].substr($v['mini'], 1);
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
		$dir = $config['root'].substr($path, 1);
		if (!is_dir($dir)) {
			mkdir($dir);
		}
		w('clean');
		w('pic');

		foreach ($new as $name=>$file) {
			$filename = str2url($name);

			$pic = pic_load($file);
			if ($pic) {
				$stamp = pic_load($config['root'].'design/watermark.png');

				$pic2 = pic_into($pic, 600, 600);
				pic_stamp($pic2, $stamp, -12, -12);
				imagefilter($pic2['image'], IMG_FILTER_BRIGHTNESS, 5);
				pic_jpeg($pic2, $dir.$filename, 90);
	//			copy($file, $dir.$filename);
				$value[] = array(
					'name'=>$name,
	//				'mini'=>$path.$mini,
					'href'=>$path.$filename,
				);
			}
		}
	}

	$r['valid'] = 1;
	$r['value'] = php_encode($value);
}

function input_pics($r) {
	$value = php_decode($r['value']);

	$s = '<div class="group"><div class="filelist">';
	foreach ($value as $k=>$v) {
		$del = 'Удалить '.$v['name'].'?';
		$s.= ' <a href="'.$v['href'].'" class="label label-default" rel="lightbox">'.$v['name'].'</a>'
		.' <input type="checkbox" name="'.$r['code'].'-'.$k.'" title="'.$del.'"> &nbsp; ';
	}
	$s.= '</div>
<span class="btn btn-info fileinput-button filesel">
	<i class="icon-plus icon-white"></i>
	<span>Выбрать файл...</span>
	<input type="file" name="'.$r['code'].'-[]" multiple>
</span>
	 <input type="text" name="'.$r['code'].'-url[]" class="input-small form-control filesel" style="width:300px" placeholder="http://...">
	 <button class="btn btn-default fileplus"><i class="fa fa-plus"></i></button>
</div>';

	return $s;
}

?>