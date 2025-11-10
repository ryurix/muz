<?php

namespace Cabinet;

class Stock extends \Flydom\Table\Sql
{
	protected static function defaults() {
		return [
			'sort'=>'stock',
			'desc'=>1,
		];
	}

	protected const FROM = 'stock c LEFT JOIN store s ON c.store=s.i';
	protected const COLUMNS = [
		'i' => ['name'=>'#', 'field'=>'c.store'],
		'name'=> ['name'=>'Название', 'field'=>'s.brand,s.name,s.model', 'fields'=>3, 'sort'=>'s.name'],
		'stock'=>['name'=>'Количество', 'field'=>'c.stock'],
		'price' => ['name'=>'Цена кабинета', 'field'=>'c.price'],
		'pric'=>['name'=>'Цена сайта', 'field'=>'s.price pric', 'sort'=>'pric'],
		'data'=>['name'=>'Данные', 'field'=>'c.data'],
	];

	protected static function where() {
		return 'WHERE c.usr='.\Cabinet\Model::user();
	}

	protected static function cell($code, $pos, $row) {
		static $brand;
		if ($brand === null) {
		 	$brand = \Flydom\Cache::get('brand');
		}
		switch ($code) {
			case 'i':
				return '<td>'.$row[$pos].'</td>';
			case 'name':
				return '<td><a href="/store/'.$row[0].'">'.$row[$pos+1].' '.($brand[$row[$pos]] ?? '').' '.$row[$pos+2].'</a></td>';
			case 'data':
				$data = \Flydom\Arrau::decode($row[$pos]);
				$info = [];
				foreach ($data as $k=>$v) {
					$info[] = $k.': '.$v;
				}
				return '<td>'.implode(', ', $info).'</td>';
			default:
				return '<td>'.$row[$pos].'</td>';
		}
	}
}