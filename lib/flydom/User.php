<?

namespace Flydom;

use \Flydom\Db;

class User extends Singleton
{

static function get(): User { return parent::get(); }

function notUpdateSession() {
	session_start();
}

protected $roles = [];

function is($role = null) {

	if (is_null($role)) {
		return $this->roles['i'] ?? 0 > 0;
	}
	if (!is_array($role)) {
		$role = explode(' ', $role);
	}
	return count(array_intersect($role, $this->roles));
}

function test($role = null) {
	return true;
}

protected function __construct() {
    if (Page::get()->console) {
        $this->roles = ['console'];
        return;
    }

	$this->id = 0;
	$this->roles = [];

	if (isset($_COOKIE['mindy_auth']) && ($cdata = @unserialize(base64_decode($_COOKIE['mindy_auth']))) !== false) {
		$user = $cdata[0];
		$password = $cdata[1];

		if ($user == 'root' && md5($password) == 'b3b5ad21d6999c5dacd259c55ee30120') {
			$this->roles = ['user', 'root'];
			$this->id = 1;
		} else {
			$this->roles = ['user'];
			$this->id = 2;
		}
	}
}

// * * * properties

protected $readonly = [];
protected $default = [
    'name'=>'',
    'login'=>'',
];

public function __set($name, $value) {
	if (in_array($name, $this->readonly)) {
		throw new \Exception('Property "'.$name.'" is read-only!');
	}
	$_SESSION[$name] = $value;
}

public function __get($name) {
	if (array_key_exists($name, $_SESSION ?? [])) {
		return $_SESSION[$name];
	}
	if (array_key_exists($name, $this->default)) {
		return $this->default[$name];
	}
	throw new \Exception('Property "'.$name.'" not defined!');
}

public function __isset($name) { return array_key_exists($name, $_SESSION); }
public function __unset($name) {
	if (in_array($name, $this->readonly)) {
		throw new \Exception('Property "'.$name.'" is read-only!');
	}
	unset($_SESSION[$name]);
}

} // class User