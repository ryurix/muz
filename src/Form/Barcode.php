<?php

namespace Form;

class Barcode
{

static function start($default = [])
{
	$store = $_SESSION['scan']['store'] ?? 0;
	$code = \Db::result('SELECT code FROM store WHERE i='.$store);
	$code = \Flydom\Arrau::decodec($code);
	self::code(implode(', ', $code).(count($code) ? ', ' : ''));

	\Flydom\Form\Modal::setTitle('Штрихкод');
	\Flydom\Form\Modal::plan([
		'code'=>new \Flydom\Input\Line(['id'=>'autofocus']),
		'send'=>new \Flydom\Input\Button('', 'Сохранить')
	], $default);

	\Flydom\Form\Modal::parse();

	if (isset($_REQUEST['_win'])) {
		echo \Flydom\Form\Modal::build('%code%');
		exit;
	}

	if (self::send() == 'send') {
		$code = explode(',', self::code());
		$code = array_map('trim', $code);
		\Db::update('store', ['code'=>\Flydom\Arrau::encodec($code)], ['i'=>$store]);
		\Flydom\Alert::warning('Штрихкод товара изменён!');
	}
}

static function send($value = null) {
	return \Flydom\Form\Modal::getSet('send', $value);
}

static function code($value = null) {
	return \Flydom\Form\Modal::getSet('code', $value);
}

static function button($name, $url, $class = '') {
	return '<button data-load="'.$url.'" class="'.$class.'" type="button" data-toggle="modal" data-target="#modal-help">'.$name.'</button>';
}

} // class Barcode