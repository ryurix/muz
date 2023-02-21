<?

/*
 *	Copyright flydom.ru
 *	Version 1.0.2021-11-21
 */

namespace Flydom;

class Search
{

static function split($s, $max = 10) {
/*
	$ex = array(
		'х',
		'для',
	);
	$ex = array_flip($ex);
//*/
	$s = U8::lower($s);
/*
	$delim = array('+', ',', '/');
	$s = str_replace($delim, ' ', $s);
	$spaces = explode(' ', $s);
//*/
	$spaces = preg_split('$[ +,/-]$', $s);

	$words = array();
	foreach ($spaces as $i) {
		$i = trim($i, '()');

		if (U8::len($i) > 1 || preg_match('?^[a-z0-9]+$?', $i)) {
			$words[] = $i;
		}
	}
	return array_slice($words, 0, $max, true);
}

static function same($a1, $a2) {
	$total = 0;
	$rates = array();
	$count = 14;
	foreach ($a1 as $n=>$i) {
		$len = U8::len($i);
		if (preg_match('?^[a-z0-9]*$?', $i) && $len > 1) {
			$len = $len*3;
		}
		$len+= $count;
		$count = $count > 0 ? $count-2 : 0;
//		$len = $count < 3 ? $len + 10 : $len;
//		$len = $count < 7 ? $len + 10 : $len;
//		$count = $count++;
		$total+= $len;
		$rates[$n] = $len;
	}
	foreach ($rates as $n=>$i) {
		$rates[$n] = $i/$total*100;
	}
	$rate = 0;
	foreach ($a1 as $n=>$i) {
		foreach ($a2 as $j) {
			if (strcmp($i, $j) == 0) {
				$rate+= $rates[$n];
			}
		}
	}
	//return $total;
	return $rate > 100 ? 100 : ceil($rate);
}

static function best($a1, $a2s, $results=10) {
	$same = array();
	foreach ($a2s as $n=>$a2) {
		$rate = self::same($a1, $a2);

		if ($rate > 0) {
			$same[$n] = $rate;
/*
			if (count($same) > $results*2+100) {
				arsort($same, SORT_NUMERIC);
				$same = array_slice($same, 0, $results, true);
			}
*/
		}
	}
	arsort($same, SORT_NUMERIC);
	return array_slice($same, 0, $results, true);
}

static function quick($quick) {
	$quick = strip_tags($quick);
	$quick = U8::lower($quick);
	return str_replace([
		' ', '-', '&nbsp;', "\n", "\r", '"', "'", ',', '.', '/', '\\'
	], '', $quick);
}

static function like($quick) {
	return self::quick(str_replace(' ', '%', $quick));
}

} // class Search