<?php

namespace Order;

class Api {
	static function php() {

	}

	static function html() {

	}

	static function json($order) {
		$order['dt'] = \Flydom\Time::date($order['dt']);
		$order['last'] = \Flydom\Time::date($order['last']);
		$order['mpdt'] = \Flydom\Time::date($order['mpdt']);

		$order['state'] = [
			'key'=>$order['state'],
			'value'=>\Order\State::name($order['state']),
		];

		$mark = array_flip(explode(',', trim($order['mark'], ',')));
		$mark = array_intersect_key(\Flydom\Cache::get('mark-name'), $mark);
		$order['mark'] = self::array_keyvalue($mark);

		$cire = array_intersect_key(\Flydom\Cache::get('city'), [$order['cire'] => 0]);
		$order['region'] = self::array_keyvalue($cire);

		$vendors = \Flydom\Cache::get('vendor');
		$sync = db_fetch_all('SELECT name,dt,vendor,price,opt,count FROM sync WHERE store='.$order['store'].' ORDER BY count DESC');

		foreach ($sync as $k=>$v) {
			$sync[$k]['dt'] = \Flydom\Time::date($v['dt']);
			$sync[$k]['vendor_name'] = $vendors[$v['vendor']] ?? $v['vendor'];
		}
		$order['sync'] = $sync;

		return \Flydom\Json::encode($order);
	}

	static function array_keyvalue($array) {
		$a = [];
		foreach ($array as $k=>$v) {
			$a[] = [
				'key'=>$k,
				'value'=>$v
			];
		}
		return $a;
	}

	static function one_keyvalue($key, $array = null) {
		if (is_null($array)) {
		} else {
			if (is_array($key)) {
			}
		}
	}
}