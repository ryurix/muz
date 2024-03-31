<?

namespace Cron;

//13*60*60
class Complex extends Task {
	static function run($args) {
		global $config;

		$data = [];
		$store = [];

		$q = \Db::query('SELECT * FROM complex'); // WHERE up=176418
		while ($i = \Db::fetch($q)) {
			if (isset($data[$i['up']])) {
				$data[$i['up']][] = $i;
			} else {
				$data[$i['up']] = [$i];
			}
			$store[$i['up']] = 1;
			$store[$i['store']] = 1;
		}
		\Db::free($q);

		$store = \Db::fetchAll('SELECT i,price,prices,count,complex FROM store WHERE i IN ('.implode(',', array_keys($store)).')', 'i', true);

		// Получаем количество по группам складов, без удалённых складов
		$sync = \Db::fetchAll('SELECT sync.store,vendor.typ, SUM(sync.count) cnt FROM sync INNER JOIN vendor ON vendor.i=sync.vendor'
		.' WHERE vendor.typ<>21 AND sync.store IN ('.implode(',', array_keys($store)).') GROUP BY sync.store,vendor.typ');
		foreach ($sync as $i) {
			if (isset($store[$i['store']]['sync'])) {
				$store[$i['store']]['sync'][$i['typ']] = $i['cnt'];
			} else {
				$store[$i['store']]['sync'] = [$i['typ'] => $i['cnt']];
			}
		}
		unset($sync);

		$updated = 0;
		$updt = now();
		$typven = $config['complex-vendors'];

		foreach ($data as $key=>$children) {

			if (!isset($store[$key]) || !$store[$key]['complex']) {
				\Db::query('DELETE FROM complex WHERE up='.$key);
				continue;
			}

			$up = $store[$key];

			$valid = true;
			$price = 0;
			$prices = Prices::decode('');
			$typcnt = [];

			foreach ($children as $i) {

				if (!isset($store[$i['store']])) {
					\Db::query('DELETE FROM complex WHERE up='.$key.' AND store='.$i['store']);
					$valid = false;
					break;
				}

				$child = $store[$i['store']];

				if ($child['price'] == 0) {
					$valid = false;
					break;
				}

				if (isset($store[$i['store']]['sync'])) {
					foreach ($store[$i['store']]['sync'] as $typ=>$cnt) {
						$reserve = \Tool\Reserve::get($i['store']);
						$cnt = floor(max(0, $cnt - $i['minus'] - $reserve) / $i['amount']);
						if (isset($typcnt[$typ])) {
							$typcnt[$typ][] = $cnt;
						} else {
							$typcnt[$typ] = [$cnt];
						}
					}
				}

				$price+= round($child['price'] * (100 + $i['sale']) / 100) * $i['amount'];

				$p = Prices::decode($child['prices']);
				foreach ($prices as $k=>$v) {
					$prices[$k]+= round($p[$k] * (100 + $i['sale']) / 100) * $i['amount'];
				}

				//$count = max($count, $child['count'] - $i['minus']);
			}

			$count = 0;
			foreach ($typcnt as $typ=>$cnt) {
				if (count($cnt) == count($children)) {
					$count+= min($cnt);
				}
			}


			if ($valid && $count) {
				$prices = implode(',', $prices);
				if ($up['price'] != $price || $up['prices'] != $prices || $up['count'] != $count) {
					\Db::update('store', [
						'price'=>$price,
						'prices'=>$prices,
						'count'=>$count,
					], [
						'i'=>$key
					]);

					$updated++;
				}

				foreach ($typcnt as $typ=>$cnt) {
					if (isset($typven[$typ]) && count($cnt) == count($children)) {
						$vendor = $typven[$typ];
						$exists = \Db::fetchRow('SELECT i,dt,count FROM sync WHERE store='.$key.' AND vendor='.$vendor);
						if ($exists) {
							\Db::update('sync', [
								'dt'=>$updt,
								'price'=>$price,
								'opt'=>$price,
								'count'=>($exists['dt'] == $updt ? $exists['count'] : 0) + min($cnt)
							], ['i'=>$exists['i']]);
						} else {
							\Db::insert('sync', [
								'code'=>'',
								'name'=>'',
								'dt'=>$updt,
								'store'=>$key,
								'vendor'=>$vendor,
								'price'=>$price,
								'opt'=>$price,
								'count'=>min($cnt)
							]);
						}
					}
				}

			} else {
				if ($up['price'] > 0 || $up['count'] > 0) {
					\Db::update('store', [
						'price'=>0,
						'count'=>0,
					], [
						'i'=>$key
					]);

					$updated++;
				}
			}
		}

		\Db::delete('sync', [
			'vendor IN ('.implode(',', $typven).')',
			'dt<'.$updt,
		]);

		return $updated.'/'.count($data);
	}
}