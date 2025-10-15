<?

function mail_order($user, $order) {

	$enabled = db_result('SELECT note FROM user WHERE i='.$user);

	if ($enabled) {

		$q = db_query('SELECT * FROM mail WHERE type=1 AND user='.$user);
		if ($i = db_fetch($q)) {
			db_update('mail', array(
				'dt'=>\Config::now(),
				'info'=>$i['info'].",".$order,
			), array(
				'i'=>$i['i'],
				'type'=>1,
			));
		} else {
			db_insert('mail', array(
				'type'=>1,
				'dt'=>\Config::now(),
				'user'=>$user,
				'info'=>$order,
			));
		}
	}
}

?>