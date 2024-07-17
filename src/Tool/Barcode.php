<?

namespace Tool;

class Barcode {

static function check($s) {
	if (!preg_match('/^[0-9]+$/', $s)) {
		return false;
	}

	$type = strlen($s);

	if ($type == 12) { // UPC-A
		$a = str_split($s);
		$sum = 3 * ($a[0] + $a[2] + $a[4] + $a[6] + $a[8] + $a[10]);
		$sum+= $a[1] + $a[3] + $a[5] + $a[7] + $a[9];
		$sum = $sum % 10;
		if ($sum > 0) { $sum = 10 - $sum; }

		if ($sum == $a[11]) {
			return true;
		}
	}

	if ($type == 13) { // EAN-13
		$a = str_split($s);
		$sum = 3 * ($a[1] + $a[3] + $a[5] + $a[7] + $a[9] + $a[11]);
		$sum+= $a[0] + $a[2] + $a[4] + $a[6] + $a[8] + $a[10];
		$sum = $sum % 10;
		if ($sum > 0) { $sum = 10 - $sum; }

		if ($sum == $a[12]) {
			return true;
		}
	}

	return false;
}

} // Barcode