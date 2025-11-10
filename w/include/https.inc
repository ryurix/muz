<?php

if ($_SERVER['REQUEST_URI'] != '/robots.txt' && !\Config::DEBUG) {
	if((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") && strpos($_SERVER['SERVER_NAME'] ?? '', 'muzmart') !== FALSE) {
		$redirect = 'https://' . ($_SERVER['HTTP_HOST'] ?? \Config::DOMAIN) . rtrim($_SERVER['REQUEST_URI'], '/');
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $redirect);
		exit();
	}
}