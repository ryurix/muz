<?php namespace Flydom;

class Form
{
    protected $fields = [];
    function getField($name) { return array_key_exists($name, $this->fields) ? $this->fields[$name] : null; }
    function __call($name, $args) { return $this->getField($name); }
    function getKeys() { return array_keys($this->fields); }
    function getFields() { return $this->fields; }
	function getValues($valid = null) {
		$a = [];
		foreach ($this->fields as $k=>$v) {
			if (!is_null($valid) && $valid != $v->valid) {
				continue;
			}
			$a[$k] = $v->value;
		}
		return $a;
	}

    protected $default;
    protected $method = 'REQUEST';
    protected $open = true;
    function setOpen($value) { $this->open = $value; }
    protected $close = true;
    function setClose($value) { $this->close = $value; }

    protected $action = null;
    function setAction($value) { $this->action = $value; }
    protected $name = null;
    function setName($value) { $this->name = $value; }
	protected $class = null;
	function setClass($value) { $this->class = $value; }
    protected $valid = true;
    function isValid() { return $this->valid; }

    function __construct($fields = [], $default = [])
    {
        foreach ($fields as $k=>$v) {
            if (is_array($v)) {
                $type = $v['type'] ?? 'html';
                unset($v['type']);

                if ($type[0] !== '\\') {
                    $type = '\\Flydom\\Input\\'.ucfirst($type);
                }

                $v = new $type($v);
            } elseif (!is_object($v)) {
                $v = new \Flydom\Input\Html(['value'=>$v]);
            }
            $v->code = $k;

            $this->fields[$k] = $v;
        }
        $this->setDefault($default);
    }

    function setDefault($default)
    {
        foreach ($default as $k=>$v)
        {
            if (isset($this->fields[$k])) {
                $this->fields[$k]->default = $v;
            }
        }
    }

    function setMethod($method)
    {
        switch ($method) {
            case 'GET':
            case 'POST': $this->method = $method; break;
            default: $this->method = '_REQUEST';
        }
    }

    function parse(&$values = null)
    {
		if (!is_array($values)) {
			switch ($this->method) {
				case 'GET': $values = &$_GET; break;
				case 'POST': $values = &$_POST; break;
				default: $values = &$_REQUEST;
			}
		}
        foreach ($this->fields as $k=>$f) {
            if (!$f->readonly) {
                if (isset($values[$k])) {
                    $f->value = $values[$k];
                }
            }
            $f->parse($values);
            $this->valid = $this->valid && $f->valid;
        }
    }

    function build($template = null)
    {
        $back = '';

        if ($this->open) {
            $back.= '<form';
            if (!empty($this->action)) {
                $back.= ' action="'.$this->action.'"';
            }
			if (!empty($this->class)) {
				$back.= ' class="'.$this->class.'"';
			}
            if ($this->method == 'REQUEST') {
                $back.= ' method="POST"';
            } else {
                $back.= ' method="'.$this->method.'"';
                if ($this->method == 'POST') {
                    $back.= ' enctype="multipart/form-data"';
                }
            }
            if (!empty($this->name)) {
                $back.= ' name="'.$this->name.'"';
            }
            $back.= '>';
        }

        if (empty($template)) {
            $back.= $this->buildContainer();
		} elseif ($template == 'inline') {
			$back.= $this->buildLine();
        } elseif (is_callable($template)) {
            $back.= $template($this->fields);
        } elseif (is_file($template)) {
            include $template;
        } else {
            $search = [];
            $replace = [];
            foreach ($this->fields as $code=>$f) {
                $search[] = '%'.$code.'%';
                $replace[] = $f->build();
            }
            $back.= str_replace($search, $replace, $template);
        }

        if ($this->close) {
            $back.= '</form>';
        }

        return $back;
    }

    function validate()
    {
        foreach ($this->fields as $f) {
            $f->validate();
        }
    }

    function buildContainer()
    {
        $back = '<div class="container">';
        $hidden = '';
        foreach ($this->fields as $f) {
            if ($f->hidden) {
                $hidden.= $f->build();
            } else {
                $name = $f->name;
                $back.= '<div class="row my-1"><label class="col-sm-3 col-form-label text-right">'.$name.'</label><div class="col-sm-9">'.$f->build().'</div></div>';
            }
        }
        $back.= '</div>'.$hidden;
        return $back;
    }

	function buildLine()
	{
		$back = '<div class="form-inline">';
        $hidden = '';
        foreach ($this->fields as $f) {
            if ($f->hidden) {
                $hidden.= $f->build();
            } else {
                $name = $f->name;
                $back.= '<div class="form-group">';
				if (strlen($name)) {
					$back.= '<label class="col-sm-3 col-form-label text-right">'.$name.'</label>';
				}
				$back.= '<div class="col-sm-9 pl-0 pr-1">'.$f->build().'</div></div>';
            }
        }
        $back.= '</div>'.$hidden;
        return $back;
	}

    public function __set($name, $value) { $this->fields[$name]->value = $value; }
    public function __get($name) { return $this->fields[$name]->value; }
    public function __isset($name) { return array_key_exists($name, $this->fields); }
    //	public function __unset($name) { unset($this->fields[$name]); }
}