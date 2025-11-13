<?php

namespace Price;

class Cron {

	static function run($data)
	{
		return 'Обновлено '.static::calc().' цен';
	}

	static function calc($type = null, $where = '') {
		$store = static::loadStore($where);
		$sync = static::loadSync(array_keys($store));

		$types = \Price\Type::list();
		if (!is_null($type)) {
			$types = [ $type=>$types[$type] ];
		}

		$typePrices = [];
		foreach ($types as $k=>$v) {
			$rules = static::loadRules($k);
			$typePrices[$k] = static::prices($rules, $store, $sync) + static::getPrices($k, $store);
		}

		$updates = [];
		foreach ($store as $k=>$v) {
			$upd = [];
			if (isset($typePrices[\Price\Type::RRC]) && $typePrices[\Price\Type::RRC][$k] != $v['price']) {
				$upd['price'] = $typePrices[\Price\Type::RRC][$k];
			}
			if (isset($typePrices[\Price\Type::OPT]) && $typePrices[\Price\Type::OPT][$k] != $v['price2']) {
				$upd['price2'] = $typePrices[\Price\Type::OPT][$k];
			}
			$list = is_null($type) ? [] : \Flydom\Arrau::explode($v['prices']);
			foreach ($types as $typ=>$dummy) {
				if ($typ == \Price\Type::RRC || $typ == \Price\Type::OPT) {
					continue;
				}
				if (isset($typePrices[$typ])) {
					$list[$typ - 1] = \Flydom\Clean::uint($typePrices[$typ][$k]);
				}
			}
			$list = \Flydom\Arrau::encode($list);
			if ($list != $v['prices']) {
				$upd['prices'] = $list;
			}
			if (!empty($upd)) {
				$updates[$k] = $upd;
			}
		}

		foreach ($updates as $k=>$v) {
			\Db::update('store', $v, ['i'=>$k]);
		}

		return count($updates);
	}

	static function loadStore($where = '') {
		return \Db::fetchAll(\Db::select(['i', 'up', 'brand', 'grp', 'price', 'price2', 'prices'], 'store', $where), 'i', true);
	}

	static function loadSync($ids) {
		$rows = \Db::fetchAll('SELECT s.store,s.vendor,s.price,s.opt,s.count FROM sync s,vendor v WHERE s.vendor=v.i AND s.dt>('.\Config::now().'-v.days*86400) AND s.store IN ('.implode(',', $ids).')');
		$sync = [];
		foreach ($ids as $i) {
			$sync[$i] = [];
		}
		foreach ($rows as $i) {
			$store = $i['store'];
			$sync[$store][] = $i;
		}
		return $sync;
	}

	static function loadRules($type) {
		$rules = \Db::fetchAll('SELECT up,grp,brand,vendor,count,price,pmin,pmax,sale,fin FROM price2 WHERE typ='.$type.' ORDER BY i');
		foreach ($rules as $k=>$v) {
			$rules[$k]['grp'] = \Flydom\Arrau::explode($v['grp']);
			$rules[$k]['brand'] = \Flydom\Arrau::explode($v['brand']);
			$rules[$k]['vendor'] = \Flydom\Arrau::explode($v['vendor']);
		}
		return $rules;
	}

	static function getPrices($type, $store) {
		$price = [];
		foreach ($store as $k=>$v) {
			if ($type === \Price\Type::RRC) {
				$price[$k] = $v['price'];
			} elseif ($type === \Price\Type::OPT) {
				$price[$k] = $v['price2'];
			} else {
				$prices = \Flydom\Arrau::explode($v['prices']);
				if (isset($prices[$type - 1])) {
					$price[$k] = $prices[$type - 1];
				} else {
					$price[$k] = 0;
				}
			}
		}
		return $price;
	}

	protected static function prices($rules, $store, $sync)
	{
		$fin = [];
		$prices = [];

		foreach ($rules as $rule)
		{
			foreach ($store as $k=>$v)
			{
				if (isset($fin[$k])) { continue; }
				if (!self::valid($rule, $v, $sync[$k])) { continue; }

				$price = self::one($rule, $sync[$k]);
				$price = round($price*(100 + $rule['sale'])/100);

				if ($rule['fin']) {
					$fin[$k] = $price;
				} else {
					$prices[$k] = $price;
				}
			}
		}

		return $fin + $prices;
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
			if (!$valid) {
				return false;
			}
		}

		if ($rule['count']) {
			$count = 0;
			foreach ($sync as $i) {
				if (empty($rule['vendor']) || in_array($i['vendor'], $rule['vendor'])) {
					$count+= $i['count'];
				}
			}
			if ($count == 0 && $rule['count'] == 1) {
				return false;
			}
			if ($count > 0 && $rule['count'] == 2) {
				return false;
			}
		}

		if ($rule['pmin'] && $store['price'] < $rule['pmin']) {
			return false;
		}

		if ($rule['pmax'] && $store['price'] > $rule['pmax']) {
			return false;
		}

		return true;
	}

	protected static function one($rule, $sync)
	{
		switch ($rule['price']) {
		case 1: return self::vendor($rule, $sync);
		case 5: return self::vendor($rule, $sync, true);
		case 101: return 0;
		}

		$prices = self::syncPrices($rule, $sync, $rule['price'] > 20);
		if (empty($prices)) {
			return 0;
		}

		switch ($rule['price']) {
		case 11:
		case 21:
			return min($prices);
		case 12:
		case 22:
			return round(array_sum($prices)/count($prices));
		case 13:
		case 23:
			return max($prices);
		default:
			return 0;
		}
	}

	protected static function vendor($rule, $sync, $opt = false) {
		foreach ($sync as $i) {
			if (in_array($i['vendor'], $rule['vendor'])) {
				return $opt ? $i['opt'] : $i['price'];
			}
		}
		return 0;
	}

	protected static function syncPrices($rule, $sync, $opt = false) {
		$a = [];
		foreach ($sync as $i) {
			if ($rule['count'] != 1 || ($rule['count'] == 1 && $i['count'])) {
				$a[] = $opt ? $i['opt'] : $i['price'];
			}
		}
		return $a;
	}
}