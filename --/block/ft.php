<?

/*
 *	Copyright flydom.ru
 *	Version 1.2.2015-07-23
 */

function ft($time = null, $show_time = 0, $nbsp = 0) {
	if ($time === '') {
		return '';
	} else if (is_null($time)) {
		$time = now();
	}
	$format = "d.m.Y";
	if ($show_time) {
		if ($show_time == 2) {
			$format = "H:i";
		} elseif ($show_time == 3) {
			$format = "d.m.y H:i";
		} else {
			$format.= " H:i";
		}
	}
	$dt = date($format, $time);
	return $nbsp ? str_replace(' ', '&nbsp;', $dt) : $dt;
}

function ft_month($month, $rod='i') {
	if ($month > 12) {
		$month = date('n', $month);
	}

	static $ft_months = array(
		'ya' => array("января", "февраля", "марта", "апреля",
			"мая", "июня", "июля", "августа",
			"сентября", "октября", "ноября", "декабря"),
		'i' => array("январь", "февраль", "март", "апрель",
			"май", "июнь", "июль", "август",
			"сентябрь", "октябрь", "ноябрь", "декабрь"),
		'e' => array("январе", "феврале", "марте", "апреле",
			"мае", "июне", "июле", "августе",
			"сентябре", "октябре", "ноябре", "декабре"),
	);

	return $ft_months[$rod][$month - 1];
}

function ft_week_ru($week) {
	static $weeks = array("воскресенье", "понедельник", "вторник", "среда",
		"четверг", "пятница", "суббота");

	return $weeks[$week];
}

function ft_ru($time) {
	return date("j ", $time).ft_month(date('n', $time), 'ya').date(' Y', $time);
}

function ft_09($s) {
	$s = preg_replace('@[^0-9]+@', '', $s);
	return $s;
}

function ft_parse($s, $back = false) {
	$sec = 0;
	$min = 0;
	$hour = 0;
	$day = date('d');
	$month = date('n');
	$year = date('Y');

	if ($back) {
		list($x_year, $x_month, $x_day, $x_hour, $x_min, $x_sec) = preg_split('![ \\-T:]!', $s.'-- ::');
	} else {
		list($x_day, $x_month, $x_year, $x_hour, $x_min, $x_sec) = preg_split('![ /,\\-\\.:]!', $s.'.. ::');
	}
	$x_day = ft_09($x_day);
	$x_month = ft_09($x_month);
	$x_year = ft_09($x_year);
	$x_hour = ft_09($x_hour);
	$x_min = ft_09($x_min);
	$x_sec = ft_09($x_sec);

	if ($x_day > 0 && $x_day <= 31) $day = $x_day;
	if ($x_month > 0 && $x_month <= 12) $month = $x_month;
	if ($x_year > 1900) $year = $x_year;
	if ($x_hour >= 0 && $x_hour < 24) $hour = strlen($x_hour) > 0 ? $x_hour : 0;
	if ($x_min >= 0 && $x_min < 60) $min = strlen($x_min) > 0 ? $x_min : 0;
	if ($x_sec >= 0 && $x_sec < 60) $sec = strlen($x_sec) > 0 ? $x_sec : 0;

	return mktime($hour, $min, $sec, $month, $day, $year);
}

function ft_square($time) {
	$mon = date('n', $time);
	$o = '<div class="ftq"><b>'.date('d', $time);
	$o.= '</b><u class="mon'.$mon.'">'.ft_month($mon);
	$o.= '</u><i>'.date('Y', $time).'</i></div>';
	return $o;
}

function ft_diff($sec, $lang = 'ru') {
	if ($lang == 'ru') {
		$dict = array('days'=>'д.', 'hours'=>'ч.', 'min'=>'м.', 'sec'=>'с.');
	} elseif ($lang == 'en') {
		$dict = array('days'=>'d.', 'hours'=>'h.', 'min'=>'m.', 'sec'=>'s.');
	}

	$data = array();
	$data['days'] = floor($sec / (24*60*60));
	$sec = $sec - $data['days'] * 24*60*60;
	$data['hours'] = floor($sec / (60*60));
	$sec = $sec - $data['hours'] * 60*60;
	$data['min'] = floor($sec / 60);
	$sec = $sec - $data['min'] * 60;
	$data['sec'] = $sec;

	$s = '';
	foreach ($data as $k=>$v) {
		if ($v > 0) {
			$s.= ' '.$v.$dict[$k];
		}
	}

	return $sec == 0 ? '0'.$dict['sec'] : substr($s, 1);
}

?>