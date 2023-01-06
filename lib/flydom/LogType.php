<?

namespace Flydom;

class LogType
{

const DEBUG  =  0;
const ERROR  =  2;
const WARNING = 8;

static function list() {
	return [
		self::DEBUG=>'Отладочная информация',
		self::ERROR=>'Ошибка',
		self::WARNING=>'Предупреждение',
	];
}

} // LogType

/*

CREATE TABLE `log2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `typ` int(11) NOT NULL,
  `code` int(11) DEFAULT NULL,
  `info` text COLLATE utf8_unicode_ci,
  `usr` int(11) DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

*/