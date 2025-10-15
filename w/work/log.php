<?

// Сохраняет лог в базу
function logs($type, $code = 0, $info = null, $user = null) {
	db_insert('log', array(
		'type'=>$type,
		'dt'=>\Config::now(),
		'user'=>is_null($user) ? $_SESSION['i'] : $user,
		'info'=>mb_substr($info, 0, 65535),
		'code'=>$code,
	));
}

// Формирует запись из плана по заданным полям
function log_plan($plan, $fields) {
	$log = array();
	foreach ($fields as $i) {
		$log[] = log_value($plan[$i]);
	}
	return implode(', ', $log);
}

// Формирует лог из плана по изменённым полям
function log_delta($plan, $fields) {
	$changed = array();

	foreach ($fields as $i) {
		$def = isset($plan['']['default']) ? $plan['']['default'][$i] : $plan[$i]['default'];
		if ($plan[$i]['value'] != $def) {
			$changed[] = $i;
		}
	}

	return log_plan($plan, $changed);
}

// Формирует запись из значения по типу
function log_value($f) {
	$n = strip_tags($f['name']);
	$v = $f['value'];

	if (function_exists('log_'.$f['type'])) {
		$v = call_user_func('log_'.$f['type'], $f);
	}
/*
	switch ($f['type']) {
		case 'combo': $v = $f['values'][$v]; break;
	}
*/
	return $n.': '.$v;
}

?>