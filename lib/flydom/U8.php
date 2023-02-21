<?

namespace Flydom;

class U8
{

static function lower($s) {
	$convert_to = array(
		"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
		"v", "w", "x", "y", "z", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
		"ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "а", "б", "в", "г", "д", "е", "ё", "ж",
		"з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы",
		"ь", "э", "ю", "я"
	);
	$convert_from = array(
		"A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
		"V", "W", "X", "Y", "Z", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï",
		"Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж",
		"З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ы",
		"Ь", "Э", "Ю", "Я"
	);

	return str_replace($convert_from, $convert_to, $s);
}

static function upper($s) {
	$convert_from = array(
		"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u",
		"v", "w", "x", "y", "z", "à", "á", "â", "ã", "ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
		"ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ø", "ù", "ú", "û", "ü", "ý", "а", "б", "в", "г", "д", "е", "ё", "ж",
		"з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы",
		"ь", "э", "ю", "я"
	);
	$convert_to = array(
		"A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U",
		"V", "W", "X", "Y", "Z", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê", "Ë", "Ì", "Í", "Î", "Ï",
		"Ð", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ø", "Ù", "Ú", "Û", "Ü", "Ý", "А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж",
		"З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ъ",
		"Ь", "Э", "Ю", "Я"
	);

	return str_replace($convert_from, $convert_to, $s);
}

static function fopen($file, $enc='windows-1251') {
	$fc = iconv($enc, 'utf-8', file_get_contents($file));
    $handle=fopen("php://memory", "rw");
    fwrite($handle, $fc);
    fseek($handle, 0);
    return $handle;
}

static function len($s) {
	return mb_strlen($s, 'UTF-8');
}

static function sub($s, $first, $count = 0) {
	if ($count > 0) {
		return mb_substr($s, $first, $count, 'UTF-8');
	} else {
		$count = mb_strlen($s, 'UTF-8') - $first;
		return mb_substr($s, $first, $count, 'UTF-8');
	}
}

static function pos($s, $needle, $offset = 0) {
	return mb_strpos($s, $needle, $offset, 'UTF-8');
}

static function rpos($s, $needle, $offset = 0) {
	return mb_strrpos($s, $needle, $offset, 'UTF-8');
}

static function cap($s) {
	$s = trim($s);
	return self::upper(self::sub($s, 0, 1)).self::lower(self::sub($s, 1));
}

static function capitalize($s) {
	$ss = explode(' ', $s);
	foreach ($ss as $k=>$v) {
		$ss[$k] = self::cap($v);
	}
	return implode(' ', $ss);
}

} // class U8