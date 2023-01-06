<?

w('input-time');
w('input-date');

function parse_datetime(&$v) {
	parse_date($v);
	parse_time($v);
}

function input_datetime($v) {
	return input_time($v).' '.input_date($v);
}

?>