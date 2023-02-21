<?php

function exceptions_error_handler($severity, $message, $filename, $lineno) {
	return exceptions_error_logger($severity, $message, $filename, $lineno, false);
}

function exceptions_error_logger($severity, $message, $filename, $lineno, $fatal = false) {

	if (strpos($message, 'exif_read_data') !== FALSE) {
		return;
	}

	if (!\Flydom\Db::connected()) {
		// Получение настроек из основного конфигурационного файла
		// Подключение к БД на основании полученных настроек
		global $config;
		\Flydom\Db::connect($config['database']);

		if (!\Flydom\Db::connected()) {
			return;
		}
	}

	if ($fatal) {
		$pos = strpos($message, '#6');
		if ($pos) {
			$message = substr($message, 0, $pos - 1);
		}
	} else {
		$a = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 6);
		array_shift($a);

		foreach ($a as $k=>$v) {
		    if (!empty($v['file']) && !empty($v['line'])) {
				if ($v['file'] == 'xdebug://debug-eval') { return; }
                $message .= ' #'.($k + 1).' '.$v['file'].'('.$v['line'].')';
            }
		}
	}

	$message = str_replace(dirname(__DIR__), '', $message);
	$message.= ' '.$_SERVER['REQUEST_URI'];

	if (\Flydom\Db::connected()) {

		$type = in_array($severity, [8,32,128,512,1024,2048,8192]) ? \Flydom\LogType::WARNING : \Flydom\LogType::ERROR;
		\Flydom\Log::add($type, $severity, $message);
	}
}

set_error_handler('exceptions_error_handler');

function fatal_error_handler() {
	$e = error_get_last();
	if (is_array($e) && $e['type'] == E_ERROR) {
		exceptions_error_logger($e['type'], $e['message'], $e['file'], $e['line'], true);
	}
}

register_shutdown_function('fatal_error_handler');

function disable_error_log() {
	set_error_handler(function () {});
	register_shutdown_function(function () {});
}

function log_debug($message) {

	if (!\Flydom\Db::connected()) {
		global $config;
		\Flydom\Db::connect($config['database']);

		if (!\Flydom\Db::connected()) {
			return;
		}
	}

	if (is_array($message)) {
		$message = \Flydom\Cache::array_encode($message);
	}

	\Flydom\Log::add(\Flydom\LogType::DEBUG, 0, $message);
}
