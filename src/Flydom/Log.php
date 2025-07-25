<?

namespace Flydom;

class Log
{

protected static $enabled = true;

// Сохраняет лог в базу
static function add($type, $code = 0, $info = '', $user = null) {
	if (self::$enabled) {
		\Db::insert('log', [
			'dt'=>\Config::now(),
			'type'=>$type,
			'code'=>$code,
			'info'=>trim($info),
			'user'=>$user ?? \User::id(),
		]);
	}
}

static function enabled() {
	return self::$enabled;
}

static function enable() {
	self::$enabled = true;
}

static function disable() {
	self::$enabled = false;
}

static function debug($message) {
	if (is_array($message)) {
		$message = \Flydom\Arrau::encode($message);
	}

	self::add(\Type\Log::DEBUG, 0, $message);
}

} // Log
