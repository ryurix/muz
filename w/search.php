<?

/*

	21-01-2016

*/

w('u8');

function search_split($s, $max = 10) {
/*
	$ex = array(
		'х',
		'для',
	);
	$ex = array_flip($ex);
//*/
	$s = u8lower($s);
/*
	$delim = array('+', ',', '/');
	$s = str_replace($delim, ' ', $s);
	$spaces = explode(' ', $s);
//*/
	$spaces = preg_split('$[ +,/-]$', $s);

	$words = array();
	foreach ($spaces as $i) {
		$i = trim($i, '()');

		if (u8len($i) > 1 || preg_match('?^[a-z0-9]+$?', $i)) {
			$words[] = $i;
		}
	}
	return array_slice($words, 0, $max, true);
}

function search_same($a1, $a2) {
	$total = 0;
	$rates = array();
	$count = 14;
	foreach ($a1 as $n=>$i) {
		$len = u8len($i);
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

function search_best($a1, $a2s, $results=10) {
	$same = array();
	foreach ($a2s as $n=>$a2) {
		$rate = search_same($a1, $a2);

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

function search_quick($quick) {
	$quick = strip_tags($quick);
	$quick = u8lower($quick);
	return str_replace(array(
		' ', '-', '&nbsp;', "\n", "\r", '"', "'"
	), '', $quick);
}

function search_like($quick) {
	return search_quick(str_replace(' ', '%', $quick));
}

?>