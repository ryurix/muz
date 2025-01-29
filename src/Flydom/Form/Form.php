<?php namespace Flydom\Form;

class Form extends Base
{
	static protected $open = true;
	static function setOpen($value) { static::$open = $value; }
	static protected $close = true;
	static function setClose($value) { static::$close = $value; }


	static protected $action = null;
	static function setAction($value) { static::$action = $value; }
	static protected $name = null;
	static function setName($value) { static::$name = $value; }
	static protected $class = null;
	static function setClass($value) { static::$class = $value; }
	static protected $method = 'REQUEST';
	static function method($method = null)
	{
		if ($method == null) { return static::$method; }
		switch ($method) {
			case 'GET':
			case 'POST': static::$method = $method; break;
			default: static::$method = 'REQUEST';
		}
	}

	static function parse($values = null) {
		if (!is_array($values)) {
			switch (static::$method) {
				case 'GET': $values = &$_GET; break;
				case 'POST': $values = &$_POST; break;
				default: $values = &$_REQUEST;
			}
		}
		parent::parse($values);
	}

	static function build($template = null)
	{
		return static::buildOpen().static::buildForm($template).static::buildClose();
	}

	static function buildOpen() {
		if (!static::$open) {
			return '';
		}
		$back = '<form';
		if (!empty(static::$action)) {
			$back.= ' action="'.static::$action.'"';
		}
		if (!empty(static::$name)) {
			$back.= ' name="'.static::$name.'"';
		}
		if (!empty(static::$class)) {
			$back.= ' class="'.static::$class.'"';
		}
		if (static::$method == 'REQUEST') {
			$back.= ' method="POST"';
		} else {
			$back.= ' method="'.static::$method.'"';
			if (static::$method == 'POST') {
				$back.= ' enctype="multipart/form-data"';
			}
		}
		$back.= '>';
		return $back;
	}

	protected static function default($fields) {
		return static::container($fields);
	}

	protected static function template($template, $fields) {
		$search = [];
		$replace = [];
		foreach ($fields as $code=>$f) {
			$search[] = '%'.$code.'%';
			$replace[] = $f->build();
		}
		return str_replace($search, $replace, $template);
	}

	static function buildForm($template, $fields = null) {
		$fields = $fields ?? static::$fields;
		if (empty($template)) {
			return static::default($fields);
		} elseif (is_callable($template)) {
			return $template($fields);
		} elseif (is_file($template)) {
			include $template;
		} else {
			return static::template($template, $fields);
		}
	}

	static function buildClose() {
		return static::$close ? '</form>' : '';
	}

	static function container($fields) {
		$back = '<div class="container">';
		$hidden = '';
		foreach ($fields as $f) {
			if ($f->hidden()) {
				$hidden.= $f->build();
			} else {
				$name = $f->name();
				$back.= '<div class="row my-1"><label class="col-sm-3 col-form-label text-right">'.$name.'</label><div class="col-sm-9">'.$f->build().'</div></div>';
			}
		}
		$back.= '</div>'.$hidden;
		return $back;
	}

	static function send() { return static::$fields['send']->get(); }
}
