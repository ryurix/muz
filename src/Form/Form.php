<?php

namespace Form;

class Form extends \Flydom\Form\Form
{
	static protected $valid = null;
	static protected $fields = [];
	static protected $default = [];

	static protected $open = true;
	static protected $close = true;
	static protected $action = null;
	static protected $name = null;
	static protected $class = null;
	static protected $method = 'REQUEST';

	static function container($fields) {
		$back = '<div class="container">';
		$hidden = '';
		foreach ($fields as $f) {
			if ($f->hidden()) {
				$hidden.= $f->build();
			} elseif ($f->wide()) {
				$name = $f->name();
				if (!empty($name)) {
					$back.= '<div class="row my-1"><label class="col-sm-3 col-form-label text-right">'.$name.'</label></div>';
				}
				$back.= '<div class="row my-1"><div class="col-sm-12">'.$f->build().'</div></div>';
			} else {
				$back.= '<div class="row my-1"><label class="col-sm-3 col-form-label text-right">'.$f->name().'</label><div class="col-sm-9">'.$f->build().'</div></div>';
			}
		}
		$back.= '</div>'.$hidden;
		return $back;
	}
}
