<?

if (isset($_POST['code']) && isset($_POST['name']) && isset($_POST['send1'])) {
	$codes = $_POST['code'];
	$names = $_POST['name'];

//	print_pre($_POST);
	$data = array();
	foreach ($codes as $k=>$v) {
		if (strlen($v)) {
			$data[$v] = $names[$k];
		}
	}
	cache_save('groups', $data);
	alert('Группы сохранены!');
}

?>