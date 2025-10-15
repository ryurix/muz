<?

if (!isset($_REQUEST['search']) && isset($_REQUEST['search2'])) {
	$_REQUEST['search'] = $_REQUEST['search2'];
}

?>