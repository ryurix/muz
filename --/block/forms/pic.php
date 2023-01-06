<?

/*
 *	Copyright flydom.ru
 *	Version 1.5.2018-07-04
 */

/*

1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP,
7 = TIFF(intel byte order), 8 = TIFF(motorola byte order),
9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM

*/

function pic_load($filename) {

	$imageInfo = @getimagesize($filename);
	if (isset($imageInfo['channels'])) {
		$memoryNeeded = round(($imageInfo[0] * $imageInfo[1] * $imageInfo['bits'] * $imageInfo['channels'] / 8 + Pow(2,16)) * 1.65);
		$memoryUsed = memory_get_usage();
		$memoryLimit = rtrim(ini_get('memory_limit'), 'M') *pow(1024, 2);

		if ($memoryLimit < ($memoryUsed + $memoryNeeded)) {
			return FALSE;
		}
	}

	$arrContextOptions=array(
		"ssl"=>array(
			"verify_peer"=>false,
			"verify_peer_name"=>false,
		),
	);
	$data = @file_get_contents($filename, false, stream_context_create($arrContextOptions));
	$src = @imagecreatefromstring($data);
	if ( $src === FALSE ) {
		return FALSE;
	}

	$exif = @exif_read_data($filename);
	if (!empty($exif['Orientation'])) {
		switch ($exif['Orientation']) {
			case 3: $src = imagerotate($src, 180, 0); break;
			case 6: $src = imagerotate($src, -90, 0); break;
			case 8: $src = imagerotate($src, 90, 0); break;
		}
	}

	return array(
		'x'=>imagesx($src),
		'y'=>imagesy($src),
		'image'=>$src,
//		'filename'=>$filename,
	);
}

// Вписывает картинку в заданный размер c сохранением пропорций
function pic_into($pic, $max_x, $max_y) {
	$ratio_x = $max_x / $pic['x'];
	$ratio_y = $max_y / $pic['y'];
	$ratio = min($ratio_x, $ratio_y);
	$x = ceil($ratio * $pic['x']);
	$y = ceil($ratio * $pic['y']);

	$pic2 = array(
		'x' => $max_x,
		'y' => $max_y,
		'image' => imagecreatetruecolor($max_x, $max_y),
	);

	$white = imagecolorallocate($pic2['image'], 255, 255, 255);
	imagefill($pic2['image'], 0, 0, $white);

	$shift_x = ceil(($max_x-$x) / 2);
	$shift_y = ceil(($max_y-$y) / 2);

	imagecopyresampled($pic2['image'], $pic['image'],
		0 + $shift_x,
		0 + $shift_y, 0, 0,
		$x, $y, $pic['x'], $pic['y']);
	return $pic2;
}

// Врезает картинку в заданный размер, лишнее удаляется
function pic_crop($pic, $max_x, $max_y, $mode = 2, $enlarge = 1) {
	if (!$enlarge && $pic['x'] < $max_x && $pic['y'] < $max_y) {
		return $pic;
	}

	$ratio_x = $max_x / $pic['x'];
	$ratio_y = $max_y / $pic['y'];

	$pic2 = array(
		'x' => $max_x,
		'y' => $max_y,
		'image' => imagecreatetruecolor($max_x, $max_y)
	);

	$white = imagecolorallocate($pic2['image'], 255, 255, 255);
	imagefill($pic2['image'], 0, 0, $white);

	if ($ratio_x > $ratio_y) {
		if ($mode === 1) {$y = 0;}
		elseif ($mode === 3) {$y = $pic['y'] - $pic2['y'];}
		else {$y = floor(($pic['y'] - $pic2['y'] / $ratio_x) / 2);}

		$height = $max_y / $ratio_x;
		imagecopyresampled($pic2['image'], $pic['image'], 0, 0, 0, $y,
			$pic2['x'], $pic2['y'], $pic['x'], $height);
	} else {
		if ($mode === 1) {$x = 0;}
		elseif ($mode === 3) {$x = $pic['x'] - $pic2['x'];}
		else {$x = floor(($pic['x'] - $pic2['x'] / $ratio_y) / 2);}

		$width = $max_x / $ratio_y;
		imagecopyresampled($pic2['image'], $pic['image'], 0, 0, $x, 0,
			$pic2['x'], $pic2['y'], $width, $pic['y']);
	}
	return $pic2;
}

// Вписывает картинку не превышая заданный размер
function pic_fit($pic, $max_x, $max_y, $enlarge = 0) {
	if (!$enlarge && $pic['x'] < $max_x && $pic['y'] < $max_y) {
		return $pic;
	}

	$ratio_x = $max_x / $pic['x'];
	$ratio_y = $max_y / $pic['y'];

   	$ratio = min($ratio_x, $ratio_y);
	$pic2 = array(
		'x' => ceil($ratio * $pic['x']),
		'y' => ceil($ratio * $pic['y'])
	);

	$pic2['image'] = imagecreatetruecolor($pic2['x'], $pic2['y']);
	$white = imagecolorallocate($pic2['image'], 255, 255, 255);
	imagefill($pic2['image'], 0, 0, $white);

	imagecopyresampled($pic2['image'], $pic['image'], 0, 0, 0, 0,
		$pic2['x'], $pic2['y'], $pic['x'], $pic['y']);
	return $pic2;
}

function pic_jpeg(&$pic, $filename, $quality = 70, $destroy = TRUE) {
	if ($pic['image'] !== FALSE) {
		if (file_exists($filename)) {
			unlink($filename);
		}
		imagejpeg($pic['image'], $filename, $quality);
		if ($destroy) {
			imagedestroy($pic['image']);
		}
	}
}

function pic_close(&$pic) {
	if ($pic['image'] !== FALSE) {
		imagedestroy($pic['image']);
	}
}

function pic_change_ext($filename, $ext) {
	$ext2 = pathinfo($filename, PATHINFO_EXTENSION);
	if ($ext != $ext2) {
		if ($ext2 != '') {
			$filename = substr($filename, 0, -strlen('.'.$ext2));
		}
		$filename.= $ext;
	}
	return $filename;
}

function pic_ext($filename, $point = TRUE) {
	$ext = image_type_to_extension(exif_imagetype($filename), $point);
	return $ext == '.jpeg' ? '.jpg' : $ext;
}

function pic_stamp($pic, $stamp, $x = 0, $y = 0) {
	if ($x < 0) { $x = $pic['x'] - $stamp['x'] + $x; }
	if ($x == 0) { $x = round(($pic['x'] - $stamp['x']) / 2); }
	if ($y < 0) { $y = $pic['y'] - $stamp['y'] + $y; }
	if ($y == 0) { $y = round(($pic['y'] - $stamp['y']) / 2); }
	imagecopy($pic['image'], $stamp['image'], $x, $y, 0, 0, $stamp['x'], $stamp['y']);
}

?>