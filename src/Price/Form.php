<?php

namespace Price;

class Form {

	static function load($id)
	{
	}

	static function save()
	{
	}

	static function start($default) {
	}

	static function ups() {
		$values = w('catalog-all');
		$values[0] = 'Любой';
		return $values;
	}

	static function groups() {
		return \Flydom\Cache::get('groups');
	}

	static function brands() {
		$values = \Flydom\Cache::get('brand');
		$values[0] = '[не указан]';
		return $values;
	}

	static function vendors() {
		return \Flydom\Cache::get('vendor');
	}

	protected static function counts() {
		return [
			0=>'',
			1=>'В наличии',
			2=>'Отсутствует',
			3=>'Нет синхронизации',
		];
	}

	protected static function prices() {
		return [
			0=>'',
			1=>'Розничную поставщика',
			5=>'Оптовую поставщика',
			11=>'Минимальную розничную',
			12=>'Среднюю розничную',
			13=>'Максимальную розничную',
			15=>'Минимальную оптовую',
			16=>'Среднюю оптовую',
			17=>'Максимальную оптовую',
			101=>'Обнулить',
		];
	}

	protected static function plan()
	{
		$plan = [
			'code'=>new \Flydom\Input\Integer(['name'=>'Номер', 'width'=>100]),
			'up'=>new \Flydom\Input\Select('Каталог', static::ups()),
			'grp'=>new \Flydom\Input\Multiselect('Группа', static::groups()),
			'brand'=>new \Flydom\Input\Multiselect('Производители', static::brands()),
			'vendor'=>new \Flydom\Input\Multiselect('Поставщик', static::vendors()),
			'count'=>new \Flydom\Input\Select('Наличие', static::counts()),
			'pmin'=>new \Flydom\Input\Integer(['name'=>'Мин. цена', 'width'=>100]),
			'pmax'=>new \Flydom\Input\Integer(['name'=>'Макс. цена', 'width'=>100]),
			'price'=>new \Flydom\Input\Select('Взять цену', static::prices()),
			'sale'=>new \Flydom\Input\Integer(['name'=>'Коррекция цены', 'suffix'=>'%', 'width'=>100]),
			'info'=>new \Flydom\Input\Line('Комментарий'),
			'fin'=>new \Flydom\Input\Checkbox('Финальное правило'),
			'send'=>new \Flydom\Input\Button('', ['save'=>['name'=>'Сохранить', 'class'=>'btn-success'], 'delete'=>['name'=>'Удалить', 'class'=>'btn-default']]),
		];

		$plan['grp']->placeholder('Любая');
		$plan['brand']->placeholder('Любой');
		$plan['vendor']->placeholder('Любой');

		return $plan;
	}
}

class FormData extends \Form\Form
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
}