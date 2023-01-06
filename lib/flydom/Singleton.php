<?

namespace Flydom;

class Singleton
{

// * * * static

protected function __construct() {}
protected function __clone() {}
public function __wakeup() {
	throw new \Exception("Cannot unserialize a singleton.");
}

private static $instances = [];
public static function get(): Singleton
{
    $cls = static::class;

	if (!isset(self::$instances[$cls])) {
        self::$instances[$cls] = new static();
    }

    return self::$instances[$cls];
}

//* * * properties

protected $data = [];
protected $readonly = [];
protected $default = [];

public function __set($name, $value) {
	if (in_array($name, $this->readonly)) {
		throw new \Exception('Property "'.$name.'" is read-only!');
	}
	$this->data[$name] = $value;
}

public function __get($name) {
	if (array_key_exists($name, $this->data)) {
		return $this->data[$name];
	}
	if (array_key_exists($name, $this->default)) {
		return $this->default[$name];
	}
	throw new \Exception('Property "'.$name.'" not defined!');
}

public function __isset($name) { return array_key_exists($name, $this->data); }
public function __unset($name) {
	if (in_array($name, $this->readonly)) {
		throw new \Exception('Property "'.$name.'" is read-only!');
	}
	unset($this->data[$name]);
}

} // class Singleton