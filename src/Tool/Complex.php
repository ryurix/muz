<?

namespace Tool;

class Complex {
	static function typeCount($complex_id) {

		$components = \Db::result('SELECT COUNT(*) FROM complex WHERE up='.$complex_id);

		// Получаем количество по группам складов, без удалённых складов
		$q = \Db::query('SELECT sync.store,vendor.typ, SUM(sync.count) cnt, complex.amount, complex.minus FROM sync, vendor, complex'
		.' WHERE vendor.typ<>21 AND vendor.i=sync.vendor AND sync.store=complex.store AND complex.up='.$complex_id
		.' GROUP BY sync.store,vendor.typ');

		$typcnt = [];
		while ($i = \Db::fetch($q)) {
			$cnt = floor(max(0, $i['cnt'] - $i['minus']) / $i['amount']);

			if (isset($typcnt[$i['typ']])) {
				$typcnt[$i['typ']][] = $cnt;
			} else {
				$typcnt[$i['typ']] = [$cnt];
			}
		}
		\Db::free($q);

		$count = [];
		foreach ($typcnt as $typ=>$i) {
			if (count($i) == $components) {
				$count[$typ] = min($i);
			}
		}

		return $count;
	}

	static function insert($data) {
		$ids = [];

		$complex = \Db::fetchAll('SELECT c.*, s.price, s.brand, s.model, s.name FROM complex c LEFT JOIN store s ON c.store=s.i WHERE c.up='.$data['store']);

		if (count($complex)) {
			foreach ($complex as $i) {
				$brand = cache_load('brand');
				$child = $data;
				$child['store'] = $i['store'];
				$child['name'] = (isset($brand[$i['brand']]) ? $brand[$i['brand']].' ' : '').(strlen($i['model']) ? $i['model'].' ' : '').$i['name'];
				$child['price'] = round($i['price'] * (100 + $i['sale']) / 100);
				$child['count'] = $i['amount'];
				$child['note'] = trim($data['note'].' составной: '.$data['name']);
				//$child['info'] = trim($data['info'].' составной: '.$data['name']);

				\Db::insert('orst', $child);
				$ids[] = \Db::insert_id();
			}
		} else {
			\Db::insert('orst', $data);
			$ids[] = \Db::insert_id();
		}

		return $ids;
	}
}