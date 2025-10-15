<?

/*
 * Version 09.09.2013
 */

function email($to, $from, $subject, $html, $files = array()) {
	global $config;
	
	if (!is_array($files)) {
		$files = array($files);
	}

	require_once(dirname(__FILE__).'/swift/swift_required.php');

	$message = Swift_Message::newInstance()
	->setSubject($subject)
	->setFrom(array($config['backmail']=>$config['title']))
	->setReplyTo(array($from))
	->setTo(array($to))
	->setBody('<html><head></head><body>'.$html.'</body></html>', 'text/html')
//	->attach(Swift_Attachment::fromPath('my-document.pdf'))
	;

	foreach ($files as $f) {
		$message->attach(Swift_Attachment::fromPath($f));
	}

	$type = $message->getHeaders()->get('Content-Type');

//*
	$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
	->setUsername($config['backmail'])
	->setPassword($config['backpass'])
	;
//*/

//	$transport = Swift_MailTransport::newInstance();
	$mailer = Swift_Mailer::newInstance($transport);
	$result = $mailer->send($message);

	return $result;	
} // email

?>