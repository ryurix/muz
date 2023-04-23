<?

namespace Cron;

//13*60*60
class Complex extends Task {
	static function run($args) {
		$data = [];
		$store = [];

		$q = \Flydom\Db::query('SELECT * FROM complex');
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

		$store = \Flydom\Db::fetchAll('SELECT i,price,count,complex FROM store WHERE i IN ('.implode(',', array_keys($store)).')', 'i', true);

		foreach ($data as $key=>$children) {
			if (!isset($store[$key]) || !$store[$key]['complex']) {
				\Flydom\Db::query('DELETE FROM complex WHERE up='.$key);
				continue;
			}

			$up = $store[$key];

			$valid = true;
			$price = 0;
			$count = 0;

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

				/*
				if ($child['count'] == 0) {
					$valid = false;
					break;
				}
				*/

				$price+= round($child['price'] * (100 + $i['sale']) / 100);
				$count = max($count, $child['count'] - $i['minus']);
			}

			if ($valid) {
				if ($up['price'] != $price || $up['count'] != $count) {
					\Flydom\Db::update('store', [
						'price'=>$price,
						'count'=>$count,
					], [
						'i'=>$key
					]);
				}

			} else {
				if ($up['price'] > 0 || $up['count'] > 0) {
					\Flydom\Db::update('store', [
						'price'=>0,
						'count'=>0,
					], [
						'i'=>$key
					]);
				}
			}

		}
	}
}