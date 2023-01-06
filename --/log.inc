<?

$logfile = fopen(microtime().'.txt', 'w+');

function logs($s) {
	global $logfile;

	fwrite($logfile, $s."\n");
}

?>