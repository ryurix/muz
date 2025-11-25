<?php

namespace Ozon;

class Stock {

	const TASK_LOCK = 'ozon-stock';
	const TASK_TIMEOUT = 1800;

	static function run($args)
	{
		\Cabinet\Model::load($args['usr']);
		if (!\Cabinet\Model::valid()) {
			return 'Кабинет не найден: '.$args['usr'];
		}

		if (\Flydom\Memcached::locked(static::TASK_LOCK)) {
			return 'Параллельная выгрузка остатков в Озон запрещена!';
		}
		\Flydom\Memcached::lock(static::TASK_LOCK, static::TASK_TIMEOUT);

		$rows = \Db::fetchAll('SELECT c.stock,c.code,c.store,c.price FROM stock c LEFT JOIN store s ON c.store=s.i WHERE c.usr='.$args['usr'], 'store', true);

		$test = \Flydom\Clean::firstUint($args['test']);
		if ($test) {
			if (isset($rows[$test])) {
				$rows = [ $test => $rows[$test] ];
			} else {
				return 'Товар не найден: '.$test;
			}
		}

		$sync = static::sync($args, array_keys($rows));

		$upd = [];
		foreach ($rows as $k=>$v) {

			$stock = 0;
			if (!(isset($args['zero']) && $args['zero']) && $v['price'] > 0 && isset($sync[$k])) {
				$stock = $sync[$k];
			}

			if ($stock != $v['stock'] || !$args['upd']) {
				$upd[$k] = $stock;
			}
		}

		$errors = [];
		if (count($upd)) {
			$a = [];
			foreach ($upd as $k=>$v)
			{
				\Db::update('stock', ['stock'=>$v], [
					'store'=>$k,
					'usr'=>$args['usr']
				]);

				$a[] = [
					'product_id'=>$rows[$k]['code'],
					'offer_id'=>'М'.$k,
					'stock'=>$v,
					'warehouse_id'=>\Cabinet\Model::warehouse(),
				]; // new OzonStock($k, $v);
			}

			$chunks = array_chunk($a, 100);

			foreach ($chunks as $a)
			{
				$post = ['stocks'=>$a];
				$result = \Ozon\Api::post('/v2/products/stocks', $post);

				if (isset($result['result'])) {
					foreach ($result['result'] as $i) {
						if (count($i['errors'])) {
							$errors[] = $i['offer_id'].': '.$i['errors'][0]['message'];
						}
					}
				} else {
					$errors[] = \Flydom\Arrau::encode($result);
				}

				sleep(1);
			}
		}

		\Flydom\Log::add(355, 0, $errors, $args['usr']);

		\Flydom\Memcached::unlock(static::TASK_LOCK);

		return 'Обновлено: '.count($upd).' товаров'.(empty($errors) ? '' : ', ошибка: '.implode(', ', $errors));
	}


	static function sync($args, $ids)
	{
		if (empty($ids)) { return []; }

		$ups = array_keys(\Flydom\Cache::get('pathway', [0]));
		$where = [
			'hide<=0',
			'i IN ('.implode(',', $ids).')',
			'up IN ('.implode(',', $ups).')',
		];

		//if ($test) { $where[] = 'store.i='.$test; }

		$sync_where = [
			'sync.vendor=vendor.i',
			'sync.dt>('.\Config::now().'-vendor.days*86400)'
		];
		if (is_array($args['vendor']) && count($args['vendor'])) {
			$sync_where[] = 'vendor IN ('.implode(',', $args['vendor']).')';
		}
		$select = 'SELECT store.i,sync.count FROM store LEFT JOIN (SELECT sync.store, SUM(sync.count) count FROM sync,vendor WHERE '.implode(' AND ', $sync_where).' GROUP BY store) sync ON sync.store=store.i WHERE '.implode(' AND ', $where);

		$rows = \Db::fetchMap($select);

		$reserve = \Tool\Reserve::get(array_keys($rows));

		foreach ($rows as $k=>$v) {
			$res = isset($reserve[$k]) ? $reserve[$k] : 0;
			$rows[$k] = $v < $args['min'] ? 0 : max(0, $v - $args['minus'] - $res);
		}

		return $rows;
	}
}