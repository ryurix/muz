<?

$select = 'SELECT orst.i i'
.',orst.dt dt'
.',orst.last last'
.',orst.user user'
.',orst.staff staff'
.',orst.state state'
.',orst.lat lat'
.',orst.lon lon'
.',orst.city city'
.',orst.adres adres'
.',orst.store store'
.',orst.name name'
.',orst.price price'
.',orst.sale sale'
.',orst.count count'
.',orst.info info'
.',orst.docs docs'
.',orst.files files'
.',orst.money0 money0'
.',orst.money money'
.',orst.pay pay'
.',orst.money2 money2'
.',orst.pay2 pay2'
.',orst.dost dost'
.',orst.vendor vendor'
.',user.name uname'
.',user.city city'
.',user.pay upay'
.',user.ul ul'
.',user.phone phone'
.',user.email email'
.',user.login login'
.',s.name sname'
.' FROM orst'
.' LEFT JOIN user ON orst.user=user.i'
.' LEFT JOIN user s ON orst.staff=s.i'
.' WHERE orst.i="'.$config['args'][0].'"';

$q = db_query($select);
$root = '/order';

//alert($select);

if ($row = db_fetch($q)) {
	w('ft');
	$config['name'] = 'Заказ №'.$row['i'].' от '.ft($row['dt'], 1).' &mdash; '.ft($row['last'], 1);

	$config['row'] = $row;
} else {
	redirect($root);
}

?>