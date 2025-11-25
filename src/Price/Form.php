<?php

namespace Price;

class Form {

	static function valid() {
		return FormData::isValid();
	}

	static function send() {
		return FormData::send();
	}

	static function load($id)
	{
		$typ = Type::get();
		$row = \Db::fetchRow('SELECT * FROM price2 WHERE i='.$id.' AND typ='.$typ);
		$row['grp'] = \Flydom\Arrau::decode($row['grp']);
		$row['brand'] = \Flydom\Arrau::decode($row['brand']);
		$row['vendor'] = \Flydom\Arrau::decode($row['vendor']);
		return $row;
	}

	static function save()
	{
		$data = FormData::values();
		$data['grp'] = \Flydom\Arrau::encode($data['grp']);
		$data['brand'] = \Flydom\Arrau::encode($data['brand']);
		$data['vendor'] = \Flydom\Arrau::encode($data['vendor']);

		$id = FormData::get('i');
		$pre = FormData::field('i')->default();
		$typ = Type::get();

		$exists = $id ? db_result('SELECT COUNT(*) FROM price2 WHERE i='.$id.' AND typ='.$typ) : 0;
		$exists2 = $id != $pre ? db_result('SELECT COUNT(*) FROM price2 WHERE i='.$pre.' AND typ='.$typ) : 0;

		if ($exists2) {
			\Flydom\Alert::danger('Правило № '.$pre.' уже существует!');
			FormData::field('i')->iv = 1;
		} else {
			if ($exists) {
				db_update('price2', $data, ['i'=>$id, 'typ'=>$typ]);
				\Flydom\Alert::warning('Правило обновлено');
			} else {
				$data['typ'] = $typ;
				db_insert('price2', $data);
				\Flydom\Alert::warning('Правило добавлено');
			}
			\Page::redirect('/price2?typ='.$typ);
		}
	}

	static function delete() {
		$id = FormData::get('i');
		$typ = Type::get();
		\Db::delete('price2', ['i'=>$id, 'typ'=>$typ]);
		\Flydom\Alert::warning('Правило удалено!');
		\Page::redirect('/price2');
	}

	static function start($default) {
		FormData::plan(static::plan(), $default);
		FormData::parse();
		if (!empty(FormData::send())) {
			FormData::validate();
		}
	}

	static function build() {
		return FormData::build();
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

	static function counts() {
		return [
			0=>'',
			1=>'В наличии',
			2=>'Отсутствует',
		];
	}

	static function prices() {
		return [
			1=>'Розничную поставщика',
			5=>'Оптовую поставщика',

			11=>'Минимальную розничную',
			12=>'Среднюю розничную',
			13=>'Максимальную розничную',

			21=>'Минимальную оптовую',
			22=>'Среднюю оптовую',
			23=>'Максимальную оптовую',

			101=>'Обнулить',
		];
	}

	protected static function plan()
	{
		$plan = [
			'i'=>new \Flydom\Input\Integer(['name'=>'Номер', 'width'=>100, 'min'=>1]),
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