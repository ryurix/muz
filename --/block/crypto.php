<?

function encrypt($s) {
	$s = base64_encode($s);
	$s = implode('', array_reverse(str_split($s, 3)));
	return $s;
}

function decrypt($s) {
	$l = strlen($s) % 3;
	$s = implode('', array_reverse(str_split(substr($s, $l), 3)))
		.substr($s, 0, $l);
	$s = base64_decode($s);
	return $s;
}

?>