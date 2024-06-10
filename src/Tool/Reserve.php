<?

namespace Tool;

class Reserve {

	static function create($orst, $store, $count) {
		\Db::insert('reserve', [
			'orst'=>$orst,
			'store'=>$store,
			'dt'=>now(),
			'cnt'=>$count
		]);
	}

	static function delete($orderId, $store = null) {
		if (is_null($store)) {
			\Db::delete('reserve', [
				'orst'=>$orderId
			]);
		} else {
			\Db::delete('reserve', [
				'orst'=>$orderId,
				'store'=>$store
			]);
		}
	}

	static function update($orst, $store, $count) {
		\Db::update('reserve', [
			'dt'=>now(),
			'cnt'=>$count,
		], [
			'orst'=>$orst,
			'store'=>$store,
		]);
	}

	static function get($store = []) {
		if (is_array($store)) {
			if (count($store)) {
				return \Db::fetchMap('SELECT store, SUM(cnt) FROM reserve WHERE store IN ('.implode(',', $store).') GROUP BY store');
			} else {
				return \Db::fetchMap('SELECT store, SUM(cnt) FROM reserve GROUP BY store');
			}
		} else {
			$count = \Db::result('SELECT SUM(cnt) FROM reserve WHERE store='.$store);
			return !$count ? 0 : $count;
		}
	}
}