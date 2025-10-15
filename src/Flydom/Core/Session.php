<?php


namespace Flydom\Core;

use SessionHandlerInterface;

class Session implements SessionHandlerInterface
{
	function open($path, $name): bool {
		return true;
	}

	function close(): bool {
		return true;
	}

	function read($key): string
	{
		$headers = getallheaders();
		$bearer = $headers['Authorization'] ?? $headers['authorization'] ?? '';
		if (substr($bearer, 0, 6) == 'Bearer') {
			$key = trim(substr($bearer, 7));
			self::$save = false;
		}

		$rows = \Db::fetchAll('SELECT * FROM session WHERE i='.crc32($key));
		foreach ($rows as $i) {
			if ($i['id'] == $key) {
				return $i['data'];
			}
		}
		return '';
	}

	protected static $newSession = false;
	protected static $save = true;
	static function isNew() { return self::$newSession; }
	static function new($value = true) { self::$newSession = $value; }

	function write($key, $data): bool {

		if (isset($_SESSION['i']) && self::$save)
		{
			$uid = $_SESSION['i'];
			if (self::$newSession) {
				$ip = \User::ip();
				\Db::insert('session', [
					'i'=>crc32($key),
					'id'=>$key,
					'ip'=>$ip,
					'dt'=>time(),
					'data'=>$data,
					'usr'=>$uid,
				]);
			} else {
				\Db::update('session', [
					'dt'=>time(),
					'data'=>$data,
				], [
					'i'=>crc32($key),
					'id'=>$key,
					'usr'=>$uid,
				]);
			}
		}
		return true;
	}

	function destroy($sess): bool {
		\Db::delete('session', ['id'=>$sess]);
		return true;
	}

	function gc($life): int {
		return 0;
	}

} // class Session