<?

if (!isset($_REQUEST['modal'])) {
	redirect('/auto');
}

$q = db_query('SELECT orst.*,user.name fio,user.phone FROM orst,user WHERE orst.i='.$config['args'][0].' AND orst.user=user.i');
if ($row = db_fetch($q)) {
	$config['row'] = $row;
} else {
	redirect('/auto');
}

$config['design'] = 'none';

?>