<?

function user_name($i, $name, $color = 0) {
	$rgb = '777';
	switch ($color) {
		case 1: $rgb = '0c0'; break;
		case 2: $rgb = 'ec0'; break;
		case 3: $rgb = 'f00'; break;
		case 4: $rgb = '000'; break;
	}
	return '<a href="/user/'.$i.'" style="color:#'.$rgb.'">'.$name.'</a>';
}

?>