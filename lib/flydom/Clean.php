<? namespace Flydom;

class Clean
{

// Проверка является ли строка беззнаковым целым
static function is_uint($s) {
//	return preg_match('/^[0-9]+$/', $s);
	return ctype_digit($s.'');
}

// Очищает лишние символы беззнакового целого числа
static function uint($s) {
	$s = preg_replace('@[^0-9]+@', '', $s);
	$s = ltrim($s, 0);
	return strlen($s) ? $s : 0;
}

// Удаляет лишние символы из имени файла
static function file($s) {
	$s = Clean::line($s);
	$s = preg_replace('*[^0-9a-zA-Zа-яА-Я_\-\+=()\[\] .,{}!№#;%~`@$\^&]*', '', $s);
	return $s;
}

// Удаляет символы перевода строк
static function line($s) {
	$s = trim(str_replace(array("\r\n", "\r", "\n"), ' ', $s));
	return $s;
}

// Проверяет корректность адреса электронной почты
static function is_mail($mail) {
	$user = '[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']{1,100}';
	$domain = '(?:(?:[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.?){2,100}';
	$ipv4 = '[0-9]{1,3}(\.[0-9]{1,3}){3}';
	$ipv6 = '[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}';

	return preg_match("/^$user@($domain|(\[($ipv4|$ipv6)\]))$/", $mail);
}

// Очищает лишние символы в адресе электронной почты
static function mail($s) {
	$s = Clean::line($s);
	$s = strtolower($s);
	$s = preg_replace('#[^0-9a-z_@.\-\+=]#', '', $s);
	return $s;
}

// Проверяет является ли число целым
static function is_int($s) {
	return preg_match('/^-?[0-9]+$/', $s);
}

// Удаляет лишние символы в целом числе
static function int($s) {
	$minus = strpos($s, '-');
	$s = Clean::uint($s);
	if (strlen($s) == 0) {
		$s = 0;
	} elseif ($minus !== FALSE) {
		$s = '-'.$s;
	}

	return (int)$s;
}

// Проверяет является ли строка денежной суммой (не более 2 знаков после запятой)
static function is_money($s) {
	return preg_match('/^[0-9]+([\.\,][0-9]{0,2})?$/', $s);
}

// Проверяет и удаляет лишние символы в сумме денег (не более 2 знаков после запятой)
static function money($s) {
	if (Clean::is_money($s)) {
		return str_replace(',', '.', $s);
	}
	$s = preg_replace('@[^\.\,0-9]+@', '', $s);
	$point = strpos($s, '.');
	if ($point === FALSE) {
		$point = strpos($s, ',');
	}
	$s = str_replace(array('.', ','), '', $s);
	if ($point !== FALSE && $point < strlen($s)) {
		$s = substr($s, 0, $point).'.'.substr($s, $point, 2);
	}
	return $s;
}

// Проверяет является ли строка числом
static function is_number($s) {
	return preg_match('/^\-?[0-9]+([\.\,][0-9]+)?$/', $s);
}

// Проверяет и удаляет лишние символы числа с точкой или запятой в качестве разделителя дробной части
static function number($s) {
	if (Clean::is_number($s)) {
		return str_replace(',', '.', $s);
	}
	$minus = strpos($s, '-');
	$s = preg_replace('@[^\.\,0-9]+@', '', $s);
	$point = strpos($s, '.');
	if ($point === FALSE) {
		$point = strpos($s, ',');
	}
	$s = str_replace(array('.', ','), '', $s);
	if ($point !== FALSE && $point < strlen($s)) {
		$s = substr($s, 0, $point).'.'.substr($s, $point);
	}
	if ($minus !== FALSE) {
		$s = '-'.$s;
	}
	return $s;
}

// Проверяет корректность url
static function is_url($url, $absolute = FALSE) {
	$allowed_characters = '[a-z0-9\/:_\-_\.\?\$,;~=#&%\+]';
	if ($absolute) {
		return preg_match("/^(http|https|ftp):\/\/". $allowed_characters ."{5,255}$/i", $url);
	} else {
		return preg_match("/^". $allowed_characters ."{5,255}$/i", $url);
	}
}

// Проверяет телефон ли это, 10 или 11 чисел
static function is_phone($s) {
	$l = strlen(Clean::uint($s));
	return 9 < $l && $l < 12 && preg_match('/^[0-9\\-\\(\\)\\+ ]+$/', $s);
}

// Очищает телефон, возвращая 11 чисел с первой семёркой
static function phone($s) {
	$s = Clean::uint($s);
	if (strlen($s) == 10) {
		return '7'.$s;
	}
	if (strlen($s) == 11) {
		if (substr($s, 0, 1) == '8') {
			return '7'.substr($s, 1);
		}
		if (substr($s, 0, 1) == '7') {
			return $s;
		}
	}
	return false;
}

// Проверяет html на корректность тегов
static function html($html) {
	$split = explode('<', $html);
	$stack = array();
	$pure = '';

	foreach ($split as $value) {
		$text_pos = strpos($value, '>');
		$params = '';
		if ($text_pos === FALSE) {
			$text = $value;
			$name = '';
		} else {
			$name_pos = strpos($value, ' ');
			$text = substr($value, $text_pos + 1);
			if ($name_pos === FALSE || $name_pos > $text_pos) {
				$name_pos = $text_pos;
			} else {
				$params = trim(substr($value, $name_pos, $text_pos - $name_pos));
			}
			$name = substr($value, 0, $name_pos);
		}

		if ($name != '') {
			if ($name{0} == '/') {
				$name = substr($name, 1);
				if (in_array($name, $stack)) {
					do {
						$pure.= '</'.$stack[0].'>';
					} while (array_shift($stack) != $name);
				}
			} else {
				if ($params == '') {
					$pure.= "<$name>";
					array_unshift($stack, $name);
				} else {
					if (substr($params, -1, 1) != '/') {
						array_unshift($stack, $name);
					}
					$pure.= "<$name $params>";
				}
			}
		}

		$pure.= $text;
	}

	while (count($stack) > 0) {
		$pure.= '</'.array_shift($stack).'>';
	}

	return $pure;
}

static function rus2translit($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '',    'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    return strtr($string, $converter);
}

// Переводит строку в url
static function str2url($str) {
    // переводим в транслит
    $str = Clean::rus2translit($str);
    // в нижний регистр
    $str = strtolower($str);
    // заменям все ненужное нам на "-"
    $str = preg_replace('~[^-a-z0-9_.]+~u', '-', $str);
	// удаляем повторяющиеся '-'
	$str = strtr($str, '--', '-');
    // удаляем начальные и конечные '-'
    $str = trim($str, "-");
    return $str;
}

// Переводит строку в список тегов
static function str2tag($str) {
	$str = U8::lower($str);
	$str = preg_replace('~[^-a-zа-я0-9_. !]+~u', ' ', $str);
	$str = preg_replace('~( ){2,}~u', ' ', $str);
	$str = trim($str);

	return $str;
}

// Возвращает первое целое число в строке
static function first_uint($str) {
	if (!preg_match('~^[0-9]+~', $str, $num)) {
		return 0;
	} else {
		return $num[0];
	}
}

// Разбирает текст на массив строк
static function explode_rows($text) {
	$rows = explode("\n", $text);
	$clean = array();
	foreach ($rows as $v) {
		$line = trim($v);
		if (strlen($line) > 0) {
			$clean[] = $line;
		}
	}
	return $clean;
}

}