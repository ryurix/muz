<?

function comment_type($i, $type, $body = NULL) {
	db_insert('comment', array(
		'theme'=>$i,
		'type'=>$type,
		'user'=>$_SESSION['i'],
		'dt'=>\Config::now(),
		'body'=>$body,
	));
}

function implode_values($keys, $values, $delim = ',') {
	$back = array();
	foreach ($keys as $k) {
		if (isset($values[$k])) {
			$back[$k] = $values[$k];
		}
	}
	return implode($delim, $back);
}

function changes($plan, $fields) {
	$list = array();
	foreach ($fields as $f) {
		$def = $plan['']['default'][$f];
		$val = $plan[$f]['value'];
		$typ = $plan[$f]['type'];

		if (is_array($def)) { $def = implode_values($def, $plan[$f]['values']); }
		if (is_array($val)) { $val = implode_values($val, $plan[$f]['values']); }

		if ($def != $val) {
			if ($typ == 'combo') {
				$def = kv($plan[$f]['values'], $def, '');
			}
			if ($typ == 'files') {
				$files = php_decode($def);
				$names = array();
				if (is_array($files)) {
					foreach ($files as $i) {
						$names[] = $i['name'];
					}
				}
				$def = implode(', ', $names);
			}
			$name = $plan[$f]['name'];
			$pos = strpos($name, '<');
			$list[] = ($pos ? substr($name, 0, $pos) : $name).' ('.$def.')';
		}
	}
	if (count($list)) {
		return implode(', ', $list);
	} else {
		return NULL;
	}
}

?>