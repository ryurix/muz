<?

namespace Tool;

class Barcode {

static function check($barcode) {
	if (!preg_match('/^[0-9]+$/', $barcode)) {
		return false;
	}

	$type = strlen($barcode);
	$a = str_split($barcode);

	if ($type == 8) { // EAN-8

		$sum = 3 * ($a[0] + $a[2] + $a[4] + $a[6]);
		$sum+= $a[1] + $a[3] + $a[5];
		$sum = (10 - $sum % 10) % 10;

		if ($sum == $a[7]) {
			return true;
		}
	}

	if ($type == 12) { // UPC-A

		$sum = 3 * ($a[0] + $a[2] + $a[4] + $a[6] + $a[8] + $a[10]);
		$sum+= $a[1] + $a[3] + $a[5] + $a[7] + $a[9];
		$sum = (10 - $sum % 10) % 10;

		if ($sum == $a[11]) {
			return true;
		}
	}

	if ($type == 13) { // EAN-13

		$sum = 3 * ($a[1] + $a[3] + $a[5] + $a[7] + $a[9] + $a[11]);
		$sum+= $a[0] + $a[2] + $a[4] + $a[6] + $a[8] + $a[10];
		$sum = (10 - $sum % 10) % 10;

		if ($sum == $a[12]) {
			return true;
		}
	}

	return false;
}

static function clean($barcode) {
	return preg_replace('@[^0-9]+@', '', $barcode);
}

} // Barcode