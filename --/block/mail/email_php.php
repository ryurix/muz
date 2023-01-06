<?

/*
 * Version 08.08.2015
 */

function email($to, $from, $subject, $html, $files = array(), $copy = 1) {
	global $config;
	
	if (!is_array($files)) {
		$files = array($files);
	}
	
	$subject = strip_tags($subject);
	$message = '<body>'.$html.'</body>';
	$headers  = 'MIME-Version: 1.0' . "\n";

	//$headers .= 'To: '.$to."\n";
	$headers .= 'From: '.$from."\n";
	$headers .= 'Reply-To: '.$from."\n";
	//$headers .= 'Subject: '.$subject."\n";

	if (count($files)) {
		$boundary = '==Boundary-'.md5(time());
		$headers.= "Content-Type: multipart/mixed;\n boundary=\"{$boundary}\"";
		$message = "--{$boundary}\n" . "Content-Type: text/html; charset=utf-8\n"."Content-Transfer-Encoding: 7bit\n\n".$message."\n\n"; 

		foreach ($files as $file){
			$message .= "--{$boundary}\n";
			$data = file_get_contents($file);
			$data = chunk_split(base64_encode($data));
			$message .= "Content-Type: application/octet-stream; name=\"".basename($file)."\"\n"
."Content-Description: ".basename($file)."\n"
."Content-Disposition: attachment;\n"
." filename=\"".basename($file)."\"; size=".filesize($file).";\n"
. "Content-Transfer-Encoding: base64\n\n".$data."\n\n";
        }
		$message.= "--{$boundary}--";
	} else {
		$headers.= 'Content-type: text/html; charset=utf-8' . "\n";
	}

	$back = false;
	try {
		$back = mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $headers);
		if ($copy) {
			@mail($config['backmail'], '[COPY] '.$subject, $message, $headers); // Send copy
		}
	} catch (Exception $ex) {
		// do nothing
	}

	return $back;
} // email

?>