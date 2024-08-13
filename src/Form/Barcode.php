<?php

namespace Form;

class Barcode
{

static function start($default = [])
{
	$store = $_SESSION['scan']['store'] ?? 0;
	$code = \Db::result('SELECT code FROM store WHERE i='.$store);
	self::code(implode(', ', \Flydom\Cache::csvc_decode($code)));

	\Flydom\Form\Modal::setTitle('Штрихкод');
	\Flydom\Form\Modal::plan([
		'code'=>new \Flydom\Input\Line(),
		'send'=>new \Flydom\Input\Button([
			'codes'=>1,
			1=>'Сохранить'
		])
	], $default);

	\Flydom\Form\Modal::parse();

	if (isset($_REQUEST['_win'])) {
		echo \Flydom\Form\Modal::build('%code%');
		exit;
	}

	if (self::send() == 1) {
		$code = explode(',', self::code());
		$code = array_map('trim', $code);
		\Db::update('store', ['code'=>\Flydom\Cache::csvc_encode($code)], ['i'=>$store]);
		alert('Штрихкод товара изменён!');
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