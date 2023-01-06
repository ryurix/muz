<? namespace Flydom\Input;

abstract class Input
{
    protected $data;
    public function __construct($data = []) {
        $this->data = $data;
	}
    abstract public function parse($values = null);
    abstract public function build();

    protected $prop_readonly = [];
    protected $prop_default = [
        'hidden'=>false,
        'name'=>null,
        'readonly'=>false,
        'valid'=>true,
        'value'=>null,
    ];

/*
	public function setCode($value) { $this->data['code'] = $value; }
    public function setDefault($value) { $this->data['default'] = $value; }
    public function isHidden() { return $this->data['hidden'] ?? false; }
    public function setHidden($value) { $this->data['hidden'] = $value; }
    public function getName() { return $this->data['name'] ?? null; }
    public function getValue($value) { return $this->data['value'] ?? null; }
    public function setValue($value) { $this->data['value'] = $value; }
    public function isReadonly() { return $this->data['readonly'] ?? false; }
    public function isValid() { return $this->data['valid'] ?? true; }
*/

    public function validate() {
        $this->data['iv'] = !$this->valid;
    }

	public function __set($name, $value) {
        if (in_array($name, $this->prop_readonly)) {
            throw new \Exception(__CLASS__.' property "'.$name.'" is read-only!');
        }
        $this->data[$name] = $value;
    }

	public function __get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        if (array_key_exists($name, $this->prop_default)) {
            return $this->prop_default[$name];
        }
        throw new \Exception(__CLASS__.' property "'.$name.'" not defined!');
    }

	public function __isset($name) { return array_key_exists($name, $this->data); }
	public function __unset($name) {
        if (in_array($name, $this->prop_readonly)) {
            throw new \Exception(__CLASS__.' property "'.$name.'" is read-only!');
        }
        unset($this->data[$name]);
    }
}