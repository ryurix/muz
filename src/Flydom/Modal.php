<?php

namespace Flydom\Form;

class Modal extends Form
{

static protected $title = null;
static function setTitle($value) { self::$title = $value; }

static function build($template = null)
{
	return '
<div class="modal-header">
	<h5 class="modal-title">'.static::$title.'</h5>
	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>'.static::buildOpen().'
<div class="modal-body">'.static::buildForm($template, array_slice(static::$fields, 0 , -1)).'</div>
'.static::buildClose();
}

static function buildClose() {
	$last = array_key_last(static::fields());
	return '<div class="modal-footer">'.static::field($last)->build().'</div></form>';
}

static function modal($target, $template = null)
{
	return '
<div class="modal fade" id="modal-'.$target.'" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">'.static::build($template).'</div>
		</div>
	</div>
</div>';
}

} // class Modal