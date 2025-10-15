<?

if (!isset($_REQUEST['modal'])) {
	\Page::redirect('/auto');
}

$q = db_query('SELECT orst.*,user.name fio,user.phone FROM orst,user WHERE orst.i='.\Page::arg().' AND orst.user=user.i');
if ($row = db_fetch($q)) {
	$config['row'] = $row;
} else {
	\Page::redirect('/auto');
}

$config['design'] = 'none';

?>