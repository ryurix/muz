<?

namespace Tool;

class Reserve {

	static function create($orst, $store, $count) {
		\Flydom\Db::insert('reserve', [
			'orst'=>$orst,
			'store'=>$store,
			'dt'=>now(),
			'cnt'=>$count
		]);
	}

	static function delete($orst, $store) {
		\Flydom\Db::delete('reserve', [
			'orst'=>$orst,
			'store'=>$store
		]);
	}

	static function update($orst, $store, $count) {
		\Flydom\Db::update('reserve', [
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
				return \Flydom\Db::fetchMap('SELECT store, SUM(cnt) FROM reserve WHERE store IN ('.implode(',', $store).') GROUP BY store');
			} else {
				return \Flydom\Db::fetchMap('SELECT store, SUM(cnt) FROM reserve GROUP BY store');
			}
		} else {
			$count = \Flydom\Db::result('SELECT SUM(cnt) FROM reserve WHERE store='.$store);
			return !$count ? 0 : $count;
		}
	}
}