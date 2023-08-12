<?

namespace Cron;

//13*60*60
class Complex extends Task {
	static function run($args) {
		$data = [];
		$store = [];

		$q = \Flydom\Db::query('SELECT * FROM complex'); // WHERE up=176418
		while ($i = \Flydom\Db::fetch($q)) {
			if (isset($data[$i['up']])) {
				$data[$i['up']][] = $i;
			} else {
				$data[$i['up']] = [$i];
			}
			$store[$i['up']] = 1;
			$store[$i['store']] = 1;
		}
		\Flydom\Db::free($q);

		$store = \Flydom\Db::fetchAll('SELECT i,price,prices,count,complex FROM store WHERE i IN ('.implode(',', array_keys($store)).')', 'i', true);

		// Получаем количество по группам складов, без удалённых складов
		$sync = \Flydom\Db::fetchAll('SELECT sync.store,vendor.typ, SUM(sync.count) cnt FROM sync INNER JOIN vendor ON vendor.i=sync.vendor'
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

		foreach ($data as $key=>$children) {

			if (!isset($store[$key]) || !$store[$key]['complex']) {
				\Flydom\Db::query('DELETE FROM complex WHERE up='.$key);
				continue;
			}

			$up = $store[$key];

			$valid = true;
			$price = 0;
			$prices = Prices::decode('');
			$typcnt = [];

			foreach ($children as $i) {

				if (!isset($store[$i['store']])) {
					\Flydom\Db::query('DELETE FROM complex WHERE up='.$key.' AND store='.$i['store']);
					$valid = false;
					break;
				}

				$child = $store[$i['store']];

				if ($child['price'] == 0) {
					$valid = false;
					break;
				}

				foreach ($store[$i['store']]['sync'] as $typ=>$cnt) {
					$cnt = floor(max(0, $cnt - $i['minus']) / $i['amount']);
					if (isset($typcnt[$typ])) {
						$typcnt[$typ][] = $cnt;
					} else {
						$typcnt[$typ] = [$cnt];
					}
				}

				$price+= round($child['price'] * (100 + $i['sale']) / 100);

				$p = Prices::decode($child['prices']);
				foreach ($prices as $k=>$v) {
					$prices[$k]+= round($p[$k] * (100 + $i['sale']) / 100);
				}

				//$count = max($count, $child['count'] - $i['minus']);
			}

			$count = 0;
			foreach ($typcnt as $typ=>$cnt) {
				if (count($cnt) == count($children)) {
					$count+= min($cnt);
				}
			}

			if ($valid) {
				$prices = implode(',', $prices);
				if ($up['price'] != $price || $up['prices'] != $prices || $up['count'] != $count) {
					\Flydom\Db::update('store', [
						'price'=>$price,
						'prices'=>$prices,
						'count'=>$count,
					], [
						'i'=>$key
					]);

					$updated++;
				}

			} else {
				if ($up['price'] > 0 || $up['count'] > 0) {
					\Flydom\Db::update('store', [
						'price'=>0,
						'count'=>0,
					], [
						'i'=>$key
					]);

					$updated++;
				}
			}
		}

		return $updated.'/'.count($data);
	}
}