<?

/*
 * Version 16.05.2018
 */

function email($to, $toname, $subject, $html, $files = array(), $copy = 1) {
	global $config;
	
	if (!is_array($files)) {
		$files = array($files);
	}

	require_once(dirname(__FILE__).'/swift/swift_required.php');

	$fromname = $config['title'];
	$from = 'muzmart@muzmart.com';
//	$pass = 'Gertydedtrip';
//	$pass = 'pastila970494';
	$pass = 'wRi-FFS-sq3-uZK';

	$message = Swift_Message::newInstance()
	->setSubject($subject)
	->setFrom(array($from=>$fromname))
//	->setReplyTo(array($from=>$fromname))
	->setTo(array($to=>$toname))
	->setBody('<html><head></head><body>'.$html.'</body></html>', 'text/html')
//	->attach(Swift_Attachment::fromPath('my-document.pdf'))
	;

	if ($copy) {
		$message->setBcc($from);
	}

	foreach ($files as $f) {
		$message->attach(Swift_Attachment::fromPath($f));
	}

//	$type = $message->getHeaders()->get('Content-Type');

//*
	$transport = Swift_SmtpTransport::newInstance('smtp.yandex.ru', 465, "ssl")
	->setUsername($from)
	->setPassword($pass)
	;
//*/

//	$transport = Swift_MailTransport::newInstance();
	$mailer = Swift_Mailer::newInstance($transport);
	$result = $mailer->send($message);

	return $result;	
} // email

?>