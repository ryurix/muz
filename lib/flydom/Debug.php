<?

namespace Flydom;

class Debug
{

static $console = false;
static $enabled = true;

public static function print($var) {
    if (!self::$enabled) { return; }
    if (!self::$console) { echo '<pre>'; }
    print_r($var);
    if (!self::$console) { echo '</pre>'; }
}

public static function line($line) {
    if (!self::$console) { echo '<p>'; }
    echo $line;
    if (self::$console) { echo "\n"; } else { echo '</p>'; }
}

public static function format($var, $level = 0) {
	$space = '';
	for ($i=0; $i<$level; $i++) {
		$space.= '&nbsp; ';
	}

	if (is_array($var)) {
		$count = count($var);
		if ($count == 0) { return '[]'; }

		$is_flat = TRUE;

		$a = '';
		$i = 0;
		foreach ($var as $k => $v) {
			if ($i !== $k) {
				$is_flat = FALSE;
				break;
			}
			$a.= ",\n$space&nbsp; ".self::format($v, $level + 1);
			$i++;
		}

		if (!$is_flat) {
			$a = '';
			foreach ($var as $k => $v) {
				$a.=",\n$space&nbsp; ".self::format($k, $level + 1).' => '.self::format($v, $level + 1);
			}
		}
		return "[\n".substr($a, 2)."\n$space]";

	} elseif (is_object($var)) {
		$vars = get_object_vars($var);
		$a = '';
		foreach ($vars as $k => $v) {
			$a.=",\n$space&nbsp; ".self::format($k, $level + 1).' => '.self::format($v, $level + 1);
		}
		return "[\n".substr($a, 2)."\n$space]";
	} elseif (is_string($var)) {
		return '\''.addcslashes($var, "'\\").'\'';
	} elseif (is_null($var)) {
		return 'NULL';
	} elseif (is_bool($var) && !$var) {
		return '0';
	}
	return $var;
}

} // Debug