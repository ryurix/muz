<?

namespace Flydom;

class Log
{

protected static $enabled = true;

// Сохраняет лог в базу
static function new($type, $code = 0, $info = null, $user = null) {
	if (self::$enabled && \Flydom\Db::connected()) {
		\Flydom\Db::insert('log2', [
			'dt=NOW()',
			'typ'=>$type,
			'code'=>$code,
			'info'=>trim($info),
			'usr'=>$user,
		]);
	}
}

static function enable() {
	self::$enabled = true;
}

static function disable() {
	self::$enabled = false;
}

} // Log

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