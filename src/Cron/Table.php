<?php

namespace Cron;

class Table extends \Flydom\Table\Sql
{
	protected static $cabinetType = 0;
	static function cabinetType($value) {
		self::$cabinetType = $value;
	}
	protected static function filter() {
		if (empty(self::$cabinetType)) {
			return [];
		}
		$cabinets = [0=>''] + \Db::fetchMap('SELECT usr,name FROM cabinet WHERE typ='.self::$cabinetType.' ORDER BY name');
		return [
			'usr'=>new \Flydom\Input\Select(['values'=>$cabinets, 'class'=>'auto']),
			'send'=>new \Flydom\Input\Submit('', 'Фильтровать')
		];
	}

	protected static $types = [];
	static function types($minList = 0, $max = 0) {
		static::$types = \Cron\Type::names($minList, $max);
	}

	protected static $root = '/plan/';
	static function root($url) {
		static::$root = $url;
	}

	protected static function defaults() {
		return [
			'usr'=>0,
			'sort'=>'name',
		];
	}

	protected const FROM = 'cron p LEFT JOIN cabinet c ON p.usr=c.usr';

	protected const COLUMNS = [
		'i'=>['name'=>'#', 'field'=>'p.i'],
		'usr'=>['name'=>'Кабинет', 'field'=>'p.usr,c.name', 'fields'=>2],
		'typ'=>['name'=>'Тип', 'field'=>'p.typ'],
		'name'=>['name'=>'Название', 'field'=>'p.name'],
		'last'=>['name'=>'Предыдущий', 'field'=>'p.last'],
		'dt'=>['name'=>'Следующий', 'field'=>'p.dt'],
		'info'=>['name'=>'Результат', 'field'=>'p.info'],
	];

	protected static function where() {
		if (empty(static::$types)) {
			return '';
		}
		$where = 'WHERE p.typ IN ('.implode(',', array_keys(static::$types)).')';
		$usr = self::get('usr');
		if (!empty($usr)) { $where.= ' AND p.usr='.$usr; }
		return $where;
	}

	protected static function cell($code, $pos, $row) {
		switch ($code) {
			case 'i':
			case 'name':
				return '<td><a href="'.static::$root.'/'.$row[0].'">'.$row[$pos].'</a></td>';
			case 'usr':
				return '<td><a href="/setup/cabinet/'.$row[$pos].'">'.$row[$pos+1].'</a></td>';
			case 'typ':
				return '<td>'.static::$types[$row[$pos]].'</td>';
			case 'dt':
				$dt = $row[$pos] < \Config::now() ? 'скоро' : \Flydom\Time::dateTime($row[$pos]);
				return '<td>'.$dt.'</td>';
			case 'last':
				return '<td>'.(empty($row[$pos]) ? '' : \Flydom\Time::dateTime($row[$pos])).'</td>';
			default:
				return '<td>'.$row[$pos].'</td>';
		}
	}
}