<?

/*
 *	Copyright flydom.ru
 *	Version 1.0.2020-12-01
 *  mysqli version
 */

namespace Flydom;

class Db
{

protected static $db;
protected static $res;

public static $host;
public static $user;
public static $password;
public static $database;
public static $port;

static function connect($url = null, $user = null, $password = null, $database = null, $port = null) {

	if (is_null($url)) {
		self::$db = mysqli_connect();
		return;
	} elseif (!is_null($user)) {
		self::$host = $url;
		self::$user = $user;
		self::$password = $password;
		self::$database = $database;
		self::$port = is_null($port) ? 3306 : $port;

		self::$db = mysqli_connect(self::$host, self::$user, self::$password, self::$database, self::$port);
		mysqli_set_charset(self::$db, 'utf8');
		return;
	} elseif (is_a($url, 'mysqli')) {
		self::$db = $url;
		return;
	}

	// Decode url-encoded information in the db connection string
	$url = parse_url($url);

	self::$host = urldecode($url['host']);
	self::$user = urldecode($url['user']);
	self::$password = isset($url['pass']) ? urldecode($url['pass']) : '';
	self::$database = trim(urldecode($url['path']), '/');
	self::$port = is_null($port) ? 3306 : $url['port'];

	self::$db = mysqli_connect(self::$host, self::$user, self::$password, self::$database, self::$port);
	mysqli_set_charset(self::$db, 'utf8');
}

static function reconnect() {
	self::close();
	self::$db = mysqli_connect(self::$host, self::$user, self::$password, self::$database, self::$port);
}

static function connected() {
	return is_null(self::$db) ? false : mysqli_ping(self::$db);
}

static function close() {
	if (self::connected()) {
		mysqli_close(self::$db);
		self::$db = null;
	}
}

protected static function append_bind($value, &$types, &$keys, &$values) {
	$keys[] = '?';
	$values[] = $value;
	if (is_int($value)) {
		$types.= 'i';
	} elseif (is_double($value)) {
		$types.= 'd';
	} else {
		$types.= 's';
	}
}

static function insert($table, $data) {
	$keys = [];
	$types = '';
	$values = [];

	$query = "INSERT INTO $table SET ";

	if (is_array($data)) {
		$a = [];
		foreach ($data as $key => $value) {
			if (is_int($key)) {
				$a[] = $value;
			} else {
				$a[] = $key.'=?';
				self::append_bind($value, $types, $keys, $values);
			}
		}
		$query.= implode(',', $a);
	} else {
		$query.= $data;
	}

	try {
		$stmt = mysqli_stmt_init(self::$db);
		mysqli_stmt_prepare($stmt, $query);
		mysqli_stmt_bind_param($stmt, $types, ...$values);
		mysqli_stmt_execute($stmt);
		if (isset($stmt->errno) && $stmt->errno) {
			echo '<p class="error-db">'.$stmt->error.'<br />'.$query.'</p>';
		}
		return mysqli_affected_rows(self::$db);
	} catch (\Exception $ex) {
		echo '<p class="error-db">'.$ex->getMessage().'<br />'.$query.'</p>';
		return FALSE;
	}
}

static function insert_id() {
	return mysqli_insert_id(self::$db);
}

static function replace($table, $data) {
	$keys = [];
	$types = '';
	$values = [];
	$fields = implode(',', array_keys($data));

	$query = "INSERT INTO $table ($fields) VALUES (";

	$a = [];
	foreach ($data as $key => $value) {
		if (is_int($key)) {
			$a[] = $value;
		} else {
			$a[] = '?';
			self::append_bind($value, $types, $keys, $values);
		}
	}
	$query.= implode(',', $a);

	$query.= ") ON DUPLICATE KEY UPDATE ";

	$a = [];
	foreach ($data as $key => $value) {
		if (is_int($key)) {
			$a[] = $value;
		} else {
			$a[] = $key.'=?';
			self::append_bind($value, $types, $keys, $values);
		}
	}
	$query.= implode(',', $a);


	try {
		$stmt = mysqli_stmt_init(self::$db);
		mysqli_stmt_prepare($stmt, $query);
		mysqli_stmt_bind_param($stmt, $types, ...$values);
		mysqli_stmt_execute($stmt);
		if (isset($stmt->errno) && $stmt->errno) {
			echo '<p class="error-db">'.$stmt->error.'<br />'.$query.'</p>';
		}
		return mysqli_affected_rows(self::$db);
	} catch (\Exception $ex) {
		echo '<p class="error-db">'.$ex->getMessage().'<br />'.$query.'</p>';
		return FALSE;
	}
}

static function update($table, $data, $where) {
	$keys = [];
	$types = '';
	$values = [];

	$query = "UPDATE $table SET ";

	if (is_array($data)) {
		$a = [];
		foreach ($data as $key => $value) {
			if (is_int($key)) {
				$a[] = $value;
			} else {
				$a[] = $key.'=?';
				self::append_bind($value, $types, $keys, $values);
			}
		}
		$query.= implode(',', $a);
	} else {
		$query.= $data;
	}

	$query.= ' WHERE ';

	if (is_array($where)) {
		$a = [];
		foreach ($where as $key => $value) {
			if (is_int($key)) {
				$a[] = $value;
			} elseif (is_array($value)) {
				$a[] = $key.' IN ('.self::implode($value).')';
			} else {
				$a[] = $key.'=?';
				self::append_bind($value, $types, $keys, $values);
			}
		}
		$query.= implode(' AND ', $a);
	} else {
		$query.= $where;
	}

	try {
		$stmt = mysqli_stmt_init(self::$db);
		mysqli_stmt_prepare($stmt, $query);
		mysqli_stmt_bind_param($stmt, $types, ...$values);
		mysqli_stmt_execute($stmt);
		if (isset($stmt->errno) && $stmt->errno) {
			echo '<p class="error-db">'.$stmt->error.'<br />'.$query.'</p>';
		}
		return mysqli_affected_rows(self::$db);
	} catch (\Exception $ex) {
		echo '<p class="error-db">'.$ex->getMessage().'<br />'.$query.'<br />'.var_dump($where).'</p>';
		return FALSE;
	}
}

static function select($table, $data, $where = '', $more = '') {
	$keys = [];
	$types = '';
	$values = [];

	$query = 'SELECT ';

	if (is_array($data)) {
		$query.= implode(',', $data);
	} else {
		$query.= $data;
	}

	$query.= ' FROM ';

	if (is_array($table)) {
		$name = [];
		$join = [];

		foreach ($table as $i) {
			if (strpos(strtolower($i), ' join ')) {
				$join[] = $i;
			} else {
				$name[] = $i;
			}
		}

		$query.= implode(',', $name).' '.implode(' ', $join);
	} else {
		$query.= $table;
	}

	if (is_array($where)) {
		$a = [];
		foreach ($where as $key => $value) {
			if (is_int($key)) {
				$a[] = $value;
			} elseif (is_array($value)) {
				$a[] = $key.' IN ('.self::implode($value).')';
			} else {
				$a[] = $key.'=?';
				self::append_bind($value, $types, $keys, $values);
			}
		}
		$query.= ' WHERE '.implode(' AND ', $a);
	} elseif (strlen($where)) {
		$query.= ' WHERE '.$where;
	}

	if (is_array($more)) {
		$query.= ' '.implode(' ', $more);
	} elseif (strlen($more)) {
		$query.= ' '.$more;
	}

	try {
		$stmt = mysqli_stmt_init(self::$db);
		mysqli_stmt_prepare($stmt, $query);
		mysqli_stmt_bind_param($stmt, $types, ...$values);
		mysqli_stmt_execute($stmt);
		if (isset($stmt->errno) && $stmt->errno) {
			echo '<p class="error-db">'.$stmt->error.'<br />'.$query.'</p>';
		}
		if (is_object(self::$res)) {
			mysqli_free_result(self::$res);
		}
		self::$res = mysqli_stmt_get_result($stmt);
		return self::$res;
	} catch (\Exception $ex) {
		echo '<p class="error-db">'.$ex->getMessage().'<br />'.$query.'<br />'.var_dump($where).'</p>';
		return FALSE;
	}
}

static function delete($table, $where = '') {
	$keys = [];
	$types = '';
	$values = [];

	$query = "DELETE FROM $table WHERE ";

	if (is_array($where)) {
		$a = [];
		foreach ($where as $key => $value) {
			if (is_int($key)) {
				$a[] = $value;
			} else {
				$a[] = $key.'=?';
				self::append_bind($value, $types, $keys, $values);
			}
		}
		$query.= implode(' AND ', $a);
	} elseif (strlen($where) > 0) {
		$query.= $where;
	}

	$stmt = mysqli_prepare(self::$db, $query);
	mysqli_stmt_bind_param($stmt, $types, ...$values);

	try {
		mysqli_stmt_execute($stmt);
		return mysqli_affected_rows(self::$db);
	} catch (\Exception $ex) {
		echo '<p class="error-db">'.$ex->getMessage().'<br />'.$query.'<br />'.var_dump($where).'</p>';
		return FALSE;
	}
}

static function query($query) {
	if (is_object(self::$res)) {
		mysqli_free_result(self::$res);
	}
	self::$res = mysqli_query(self::$db, $query, MYSQLI_USE_RESULT);
	return self::$res;
}

// Для параллельного выполнения запросов
static function query2($query) {
	$res = mysqli_query(self::$db, $query, MYSQLI_USE_RESULT);
	return $res;
}

static function free($res = null) {
	if (is_null($res)) {
		if (is_object(self::$res)) {
			mysqli_free_result(self::$res);
			self::$res = null;
		}
	} elseif (is_object($res)) {
		mysqli_free_result($res);
	}
}

static function fetch($res = null) {
	if (is_null($res)) {
		$res = self::$res;
	}
	return is_object($res) ? mysqli_fetch_assoc($res) : false;
}

static function fetchAll($query = null, $key = null, $unset_key = false) {
	if (is_null($query)) { $q = self::$res;
	} elseif (is_object($query)) { $q = $query;
	} else { $q = self::query2($query); }

	$a = [];
	if (is_null($key)) {
		while ($i = self::fetch($q)) {
			$a[] = $i;
		}
	} else {
		while ($i = self::fetch($q)) {
			$k = $i[$key];
			if ($unset_key) { unset($i[$key]); }
			$a[$k] = $i;
		}
	}
	if (is_object(self::$res)) {
		self::free($q);
	}
	return $a;
}

static function fetchMap($query = null) {
	if (is_null($query)) { $q = self::$res;
	} elseif (is_object($query)) { $q = $query;
	} else { $q = self::query2($query); }

	$array = [];
	if (!$q) {
		echo '<p class="error-db">'.$query.'</p>';
	}
	while ($i = mysqli_fetch_array($q)) {
		$array[$i[0]] = $i[1];
	}
	self::free($q);
	return $array;
}

static function fetchArray($query = null) {
	if (is_null($query)) { $q = self::$res;
	} elseif (is_object($query)) { $q = $query;
	} else { $q = self::query2($query); }

	$array = [];
	if (!$q) {
		echo '<p class="error-db">'.$query.'</p>';
	}
	while ($i = mysqli_fetch_array($q)) {
		$array[] = $i[0];
	}
	self::free($q);
	return $array;
}

static function fetchRow($query = null) {
	if (is_null($query)) { $q = self::$res;
	} elseif (is_object($query)) { $q = $query;
	} else { $q = self::query2($query); }

	$row = self::fetch($q);
	self::free($q);
	return $row;
}

static function result($select) {
	$res = self::query2($select);
	if (is_object($res)) {
		$array = mysqli_fetch_array($res);
		self::free($res);
		return $array[0] ?? false;
	}
	return false;
}

static function count($res = null) {
	if (is_null($res)) {
		$res = self::$res;
	}
	return mysqli_num_rows($res);
}

static function rows() {
	return mysqli_affected_rows(self::$db);
}

protected static function implode($array) {
	$s = '';
	foreach($array as $i) {
		if ($s !== '') {
			$s.= ',';
		}

		if (ctype_digit($i.'')) {
			$s.= $i;
		} else {
			$s.= '"'.addcslashes($i, '"').'"';
		}
	}
	return $s;
}

} // class Db
