<?

function first_code($code) {
	$pos = strpos($code, '//');
	if ($pos === false) {
		return $code;
	} else {
		return trim(substr($code, 0, $pos));
	}
}

function send_code($code) {
	return first_code($code);
}

function roll_code($code) {
	return first_code($code);
}

?>