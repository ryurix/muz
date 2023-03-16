<?

function phpexcel($file, $filename = null, $sheet = 0) {
	if (is_null($filename)) {
		$filename = $file;
	}

	$ext = mb_strtolower(mb_substr($filename, -3));

	if ($ext == 'xls') {
		//include_once __DIR__.'/simplexls/src/SimpleXLS.php';

		if ($xls = \Shuchkin\SimpleXLS::parse($file)) {
			return $xls->rows($sheet);
		} else {
			alert(\Shuchkin\SimpleXLS::parseError());
		}
	} elseif ($ext == 'lsx') {
		//include_once __DIR__.'/simplexlsx/src/SimpleXLSX.php';

		if ($xls = \Shuchkin\SimpleXLSX::parse($file)) {
			return $xls->rows($sheet);
		} else {
			alert(\Shuchkin\SimpleXLSX::parseError());
		}
	} else {
		alert('Неизвестный тип файла: '.$ext);
	}

	return [];
}