<?

function mail_order($user, $order) {
	$q = db_query('SELECT * FROM mail WHERE type=1 AND user='.$user);
	if ($i = db_fetch($q)) {
		db_update('mail', array(
			'dt'=>now(),
			'info'=>$i['info'].",".$order,
		), array(
			'i'=>$i['i'],
			'type'=>1,
		));
	} else {
		db_insert('mail', array(
			'type'=>1,
			'dt'=>now(),
			'user'=>$user,
			'info'=>$order,
		));
	}
}

?>