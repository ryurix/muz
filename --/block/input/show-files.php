<?

function show_files($files, $style = array()) {
	static $icons = array(
		'png'=>'png',
		'pdf'=>'pdf',
		'doc'=>'doc',
		'docx'=>'doc',
		'xls'=>'xls',
		'xlsx'=>'xls',
		'jpg'=>'jpg',
		'jpeg'=>'jpg',
		'txt'=>'txt',
		'zip'=>'zip',
		'rar'=>'rar',
	);

	if (!is_array($files)) {
		if (strlen($files ?? '') == 0) { return; }
		$files = php_decode($files);
	}

	$style = array_merge(array(
		'site'=>'',
		'icon'=>0,
		'name'=>1,
		'class'=>'label',
		'prefix'=>'',
		'suffix'=>'',
	), $style);

	$s = '';
	foreach ($files as $v) {
		if ($style['icon']) {
			$ext = isset($v['ext']) ? $v['ext'] : pathinfo($v['href'], PATHINFO_EXTENSION);
			$ico = '<img src="/design/icon/'.(isset($icons[$ext]) ? $icons[$ext] : 'file').'.png">';
		} else {
			$ico = '';
		}

		$s.= $style['prefix'].'<a href="'.$style['site'].$v['href'].'" class="filelink" target=_blank'
		.($style['class'] == '' ? '' : ' class="'.$style['class'].'"')
		.($style['name'] ? '' : ' title="'.$v['name'].'"').'>'
		.trim($ico.'&nbsp;'.($style['name'] ? $v['name'] : '')).'</a>'.$style['suffix'];
	}
	return $s;
}

?>