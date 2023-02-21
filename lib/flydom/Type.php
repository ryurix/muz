<?

/*
 *	Copyright flydom.ru
 *	Version 1.0.2021-11-21
 */

namespace Flydom;

class Type
{
	static function data_value($data, $field, $code = null, $default = null) {
		if (is_null($code)) {
			//return array_map(fn($v): string => $v[$field], $data);
			$a = [];
			foreach ($data as $k=>$v) {
				$a[$k] = $v[$field];
			}
		}
		return isset($data[$code]) ? $data[$code][$field] : $default;
	}
}