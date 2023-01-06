<?

try {


w('google-drive');
$os = null;
$files = array();

$key = 'files';
if (isset($_FILES[$key])) {
	if (!is_array($_FILES[$key]['name'])) {
		$a = array();
	} else {
		$a = $_FILES[$key];
	}

	$count = count($_FILES[$key]['name']);
	for ($i=0; $i<$count; $i++) {
		if ($a['error'][$i] == 0) {
			if (is_null($os)) {
				$os = NewOilService();
			}

			$name = $a['name'][$i];

			$f = $os->NewFile('',
				$name,
				$name,
				$a['tmp_name'][$i],
				$a['type'][$i]
			);
			$os->Rule($f->getId());

			$files[] = '<p><a href="'.$f->getAlternateLink().'" target="_blank">'.$name.'</a>'
			.'<input type=hidden name="file[]" value="'.$f->getId().'"></p>';
		}
	}
}


} catch (Exception $e) {
	$files = array('Error: '.$e->getMessage());
}

echo json_encode($files);

exit;

?>