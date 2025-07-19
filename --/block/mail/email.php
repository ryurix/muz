<?

/*
 * Version 06.06.2025
 */

function email($to, $toname, $subject, $html, $files = null, $copy = 1)
{
	global $config;
	$from = 'muzmart@muzmart.com';
	$fromname = $config['title'];

	$host = 'smtp.yandex.ru';
	$port = '465';
	$secure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
	$user = $from;
	$password = 'wRi-FFS-sq3-uZK';

	if (!is_array($files)) {
		$files = array($files);
	}

	$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

	//$mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_CONNECTION;
	$mail->isSMTP();
	$mail->Host = $host;
	$mail->SMTPAuth = true;
	$mail->Username = $user;
	$mail->Password = $password;
	$mail->SMTPSecure = $secure;
	$mail->Port = $port;
	$mail->CharSet = 'UTF-8';

	$mail->From = $from;
	$mail->FromName = $fromname;

	$mail->addAddress($to, $toname);
	if ($copy) {
		$mail->addBCC($from, $fromname);
	}

	$mail->Subject = $subject;
	$mail->isHTML();
	$mail->Body = $html;

	foreach ($files as $i) {
		$mail->addAttachment(\Config::ROOT.$i);
	}

	try {
		$mail->send();
	} catch (Exception $e) {
		\Flydom\Log::add(49, 0, $mail->ErrorInfo);
		return $mail->ErrorInfo;
	}

	return '';
} // email

?>