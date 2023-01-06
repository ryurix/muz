<?

function array_for_columns($array, $columns) {
	$back = array();

	$max = count($array);
	$keys = array_keys($array);

	$len = array();
	for($i=0; $i<$columns; $i++) {
		$len[$i] = floor($max/$columns);
		if ($max%$columns > $i) {
			$len[$i]++;
		}
	}

	for($i=0; $i<$max; $i++) {
		$col = $i%$columns;
		$base = $col == 0 ? 0 : $base + $len[$col-1];
		$j = $base+floor($i/$columns)%$len[$col];
		$key = $keys[$j];
		$back[$key] = $array[$key];
	}

	return $back;
}

?>