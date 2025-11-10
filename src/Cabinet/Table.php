<?php

namespace Cabinet;

class Table extends \Flydom\Table\Sql
{
	protected static function defaults() {
		return [
			'sort'=>'name',
		];
	}

	protected const FROM = 'cabinet c LEFT JOIN user u ON c.usr=u.i';
	protected const COLUMNS = [
		'i' => ['name'=>'#', 'field'=>'c.usr'],
		'usr' => ['name'=>'Пользователь', 'field'=>'u.name'],
		'name'=> ['name'=>'Название', 'field'=>'c.name'],
		'type' => ['name'=>'Тип', 'field'=>'c.typ'],
		'margin'=>['name'=>'Прибыль, %', 'field'=>'c.margin'],
		'profit'=>['name'=>'Прибыль, руб', 'field'=>'c.profit'],
		'vat'=>['name'=>'НДС', 'field'=>'c.vat'],
	];

	protected static function where() {
		return '';
	}

	protected static function cell($code, $pos, $row) {
		switch ($code) {
			case 'i':
			case 'name':
				return '<td><a href="/setup/cabinet/'.$row[0].'">'.$row[$pos].'</a></td>';
			case 'usr':
				return '<td><a href="/user/'.$row[0].'">'.$row[$pos].'</a></td>';
			case 'type':
				return '<td><a href="/setup/'.\Cabinet\Type::setup($row[$pos]).'?usr='.$row[0].'">'.\Cabinet\Type::name($row[$pos]).'</a></td>';
			default:
				return '<td>'.$row[$pos].'</td>';
		}
	}
}