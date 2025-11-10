<?php

namespace Price;

class Cron {

	static function run($data)
	{
		$price = [];
		$price2 = [];

		$rows = \Db::select(['i', 'price', 'price2'], 'store');

		foreach ($rows as $i) {
			$price[$i['i']] = $i['price'];
			$price2[$i['i']] = $i['price2'];
		}

		$price = static::calc( \Price\Type::RRC, $price);
		$price2 = static::calc( \Price\Type::OPT, $price2);

		$updates = [];
		foreach ($rows as $i) {
			$key = $i['i'];
			$upd = [];
			if (isset($price[$key]) && $price[$key] != $i['price']) {
				$upd['price'] = $price[$key];
			}
			if (isset($price2[$key]) && $price2[$key] != $i['price2']) {
				$upd['price2'] = $price2[$key];
			}
			if (!empty($upd)) {
				$updates[$key] = $upd;
			}
		}

		foreach ($updates as $k=>$v) {
			\Db::update('store', $v, ['i'=>$k]);
		}

		return 'Обновлено '.count($updates).' цен';
	}

	static function calc($type, $prices)
	{
		$rules = self::loadRule($type);
		$stores = self::loadStore(array_keys($prices));
		$syncs = self::loadSync(array_keys($prices));

		$result = $prices;

		foreach ($rules as $rule)
		{
			$rule['grp'] = explode(',', $rule['grp']);
			$rule['brand'] = explode(',', $rule['brand']);
			$rule['vendor'] = explode(',', $rule['vendor']);

			foreach ($prices as $i=>$price)
			{
				if (!self::valid($rule, $stores[$i], $syncs[$i])) { continue; }
				if (isset($result[$i])) { continue; }

				$price = self::one($rule, $stores[$i], $syncs[$i]);
				$price = round($price*(100 + $rule['sale'])/100);

				if ($rule['fin']) {
					$result[$i] = $price;
				} else {
					$prices[$i] = $price;
				}
			}
		}

		return $result + $prices;
	}

	protected static function valid($rule, $store, $sync)
	{
		if ($rule['up']) {
			$children = \Flydom\Cache::get('children-hide');
			if (!in_array($store['up'], $children[$rule['up']])) {
				return false;
			}
		}

		if (!empty($rule['grp']) && !in_array($store['grp'], $rule['grp'])) {
			return false;
		}

		if (!empty($rule['brand']) && !in_array($store['brand'], $rule['brand'])) {
			return false;
		}

		if (!empty($rule['vendor'])) {
			$valid = false;
			foreach ($sync as $i) {
				if (in_array($i['vendor'], $rule['vendor'])) {
					$valid = true;
				}
			}
			if ($valid) {
				return false;
			}
		}

		if ($rule['price']) {
			$count = 0;
			foreach ($sync as $i) {
				$count+= $i['count'];
			}
			if ($count == 0 && $rule['price'] == 1) {
				return false;
			}
			if ($count > 0 && $rule['price'] == 2) {
				return false;
			}
		}

		if ($rule['pmin']) {
			foreach ($sync as $i) {
				if ($i['price'] < $rule['pmin']) {
					return false;
				}
			}
		}

		if ($rule['pmax']) {
			foreach ($sync as $i) {
				if ($i['price'] > $rule['pmax']) {
					return false;
				}
			}
		}

		return true;
	}

	protected static function one($rule, $store, $sync)
	{
		switch ($rule['price']) {
		case 1: return self::vendor($rule, $sync);
		case 5: return self::vendor($rule, $sync, true);
		case 101: return 0;
		}

		$prices = self::syncPrices($sync);
		if (empty($prices)) {
			return 0;
		}

		switch ($rule['price']) {
		case 11: return min($prices);
		case 12: return round(array_sum($prices)/count($prices));
		case 13: return max($prices);
		}
	}

	protected static function loadRule($type) {
		return \Db::fetchAll('SELECT up,grp,brand,vendor,count,price,pmin,pmax,sale,fin FROM prices WHERE typ='.$type.' ORDER BY i');
	}

	protected static function loadStore($ids) {
		return \Db::fetchAll('SELECT i,up,grp,brand FROM store WHERE i IN ('.implode(',', $ids).')');
	}

	protected static function loadSync($ids) {
		return \Db::fetchAll('SELECT store,vendor,price,opt,count FROM sync,vendor WHERE sync.vendor=vendor.i AND sync.dt>('.\Config::now().'-vendor.days*86400) AND store IN ('.implode(',', $ids).')');
	}

	protected static function vendor($rule, $sync, $opt = false) {
		foreach ($sync as $i) {
			if (in_array($i['vendor'], $rule['vendor'])) {
				return $opt ? $sync['price'] : $sync['opt'];
			}
		}
		return 0;
	}

	protected static function syncPrices($sync) {
		$a = [];
		foreach ($sync as $i) {
			$a[] = $sync['price'];
		}
		return $a;
	}
}