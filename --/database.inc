<?php

/*
 *	Copyright flydom.ru
 *	Version 1.6.2016-12-27
 */

function db_connect($url) {
	global $config;

	$url = parse_url($url);

	// Decode url-encoded information in the db connection string
	$url['user'] = urldecode($url['user']);
	// Test if database url has a password.
	if (isset($url['pass'])) {
		$url['pass'] = urldecode($url['pass']);
	}
	else {
		$url['pass'] = '';
	}
	$url['host'] = urldecode($url['host']);
	$url['path'] = trim(urldecode($url['path']), '/');

	// Allow for non-standard MySQL port.
	if (isset($url['port'])) {
		$url['host'] = $url['host'] .':'. $url['port'];
	}

	switch ($url['scheme']) {
		case 'mysql': $opt = ';charset=UTF8mb4'; break;
		default: $opt = '';
	}

	$config['db'] = new PDO($url['scheme'].':host='.$url['host'].';dbname='.$url['path'].$opt, $url['user'], $url['pass']);

	$config['db']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

function db_insert($table, $data) {
	global $config;


	$keys = [];
	$values = array();
	foreach ($data as $key=>$value) {
		$keys[] = $key;
		$values[] = ':'.$key;
	}
	$query = "INSERT INTO $table (".implode(',', $keys).') VALUES ('.implode(',', $values).')';

	$res = $config['db']->prepare($query);
	foreach ($data as $key=>$value) {
		if (is_bool($value)) {
			$res->bindValue(':'.$key, $value, PDO::PARAM_BOOL);
		} else {
			$res->bindValue(':'.$key, $value, PDO::PARAM_STR);
		}
	}

	try {
		return $res->execute();
	} catch (PDOException $ex) {
		echo '<p class="error-db">'.$ex->getMessage().'<br />'.$query.'</p>';
		return FALSE;
	}
}

function db_insert_id() {
	global $config;
	return $config['db']->lastInsertId();
}

function db_update($table, $data, $where) {
	global $config;

	$query = "UPDATE $table SET ";
	if (is_array($data)) {
		$a = array();
		foreach ($data as $key => $value) {
			$a[] = is_int($key) ? $value : $key.'=:'.$key;
		}
		$query.= implode(',', $a);
	} else {
		$query.= $data;
	}
	$query.= ' WHERE ';
	if (is_array($where)) {
		$a = array();
		foreach ($where as $key => $value) {
			$a[] = is_int($key) ? $value : $key.'=:w_'.$key;
		}
		$query.= implode(' AND ', $a);
	} else {
		$query.= $where;
	}

	$res = $config['db']->prepare($query);

	if (is_array($data)) {
		foreach ($data as $key=>$value) {
			if (!is_int($key)) {
				if (is_bool($value)) {
					$res->bindValue(':'.$key, $value, PDO::PARAM_BOOL);
				} else {
					$res->bindValue(':'.$key, $value);
				}
			}
		}
	}

	if (is_array($where)) {
		foreach ($where as $key=>$value) {
			if (!is_int($key)) {
				if (is_bool($value)) {
					$res->bindValue(':w_'.$key, $value, PDO::PARAM_BOOL);
				} else {
					$res->bindValue(':w_'.$key, $value);
				}
			}
		}
	}

	try {
		return $res->execute();
	} catch (PDOException $ex) {
		echo '<p class="error-db">'.$ex->getMessage().'<br />'.$query.'<br />'.var_dump($where).'</p>';
		return FALSE;
	}
}

function db_delete($table, $where = '') {
	$query = 'DELETE FROM '.$table;
	if (is_array($where)) {
		$query.= ' WHERE ';
		foreach ($where as $key => $value) {
			$query.= $key."='".addslashes($value)."' AND ";
		}
		$query = substr($query, 0, strlen($query) - 5);
	} elseif (strlen($where) > 0) {
		$query.= ' WHERE '.addslashes($where);
	}
	return db_query($query);
}

function db_query($query) {
	global $config;

	try {
		$res = $config['db']->prepare($query);
		$res->execute();
		return $res;
	} catch (PDOException $ex) {
		echo '<p class="error-db">'.$ex->getMessage().'<br />'.$query.'</p>';
		return FALSE;
	}
}

function db_fetch($res) {
	return $res->fetch(PDO::FETCH_ASSOC);
}

function db_fetch2($res) {
	return $res->fetch(PDO::FETCH_NUM);
}

function db_select($fields, $table = null, $where = null) {
	if (is_null($where)) {
		if (is_null($table)) {
			$query = $fields;
		} else {
			$where = $table;
			$table = $fields;
			$fields = '*';
		}
	}

	if (!isset($query)) {
		$query = "SELECT $fields FROM $table WHERE ";
		$a = [];
		if (is_array($where)) {
			foreach ($where as $key => $value) {
				$a[] = $key.'="'.addslashes($value).'"';
			}
		} else {
			$a[] = addslashes($where);
		}
		$query.= implode(' AND ', $a);
	}

	return db_query($query);
}

function db_get_row($fields, $table = null, $where = null) {
	$q = db_select($fields, $table, $where);
	if (is_null($q)) { return null; }
	$row = db_fetch($q);
	db_close($q);
	return $row;
}

function db_fetch_all($res, $key = null) {
	if (!is_object($res)) {
		$res = db_query($res);
	}

	$data = [];
	if (is_null($key)) {
		while ($i = db_fetch($res)) {
			$data[] = $i;
		}
		db_close($res);
	} else {
		while ($i = db_fetch($res)) {
			$k = $i[$key];
			unset($i[$key]);
			$data[$k] = $i;
		}
		db_close($res);
	}
	return $data;
}

function db_fetch_array($res) {
	$data = [];

	while ($i = db_fetch2($res)) {
		if (isset($i[1])) {
			$data[$i[0]] = $i[1];
		} else {
			$data[] = $i[0];
		}
	}
	db_close($res);

	return $data;
}

function db_fetch_row($res) {
	if (!is_object($res)) {
		$res = db_query($res);
	}

	$row = db_fetch($res);
	db_close($res);

	return $row;
}

function db_result($select) {
	$res = db_query($select);
	if ($res !== FALSE && $res->rowCount() > 0) {
		$array = $res->fetch(PDO::FETCH_NUM);
		db_close($res);
		return $array[0];
	}
	return FALSE;
}

function db_count($res) {
	return $res->rowCount();
}

function db_close($res) {
	$res->closeCursor();
}

?>