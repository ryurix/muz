<?

namespace Ozon;

class Xml {

	public static function run($args) {
		w('clean');
		$test = clean_09(kv($args, 'test', 0));
		$warehouse = \Flydom\Clean::uint($args['warehouse'] ?? 0);

		if ($args['form'] < 10) {
			$url2 = 'https://api-seller.ozon.ru/v1/product/import/prices';
		} else {
			$url2 = 'https://api-seller.ozon.ru/v2/products/stocks';
		}

		$ids = [];
		$pids = [];
		$prices = [];

		if ($test) {
			$ids[] = $test;
			$prices[$test] = 1;
		} else {

			if ($args['form'] < 10) {
				$prices = self::getPrices($args);
				$ids = array_keys($prices);
			} else {
				$pids = self::getPids($args);
				$ids = array_keys($pids);
			}
		}

		if (!count($ids)) {
			return;
		}

		//\Flydom\Alert::warning(php_encode($items));

		// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

		$ups = array_keys(cache_load('pathway', [0]));
		$where = [
			'hide<=0',
			'i IN ('.implode(',', $ids).')',
			'up IN ('.implode(',', $ups).')',
		];


		if (is_array($args['vendor']) && count($args['vendor'])) {
			$vendor = ' AND vendor IN ('.implode(',', $args['vendor']).')';
		} else {
			$vendor = '';
		}

		if ($test) {
			$where[] = 'store.i='.$test;
		}

		$dt = \Config::now() - 30*24*60*60;
		$select = 'SELECT store.*,ven.count FROM store LEFT JOIN (SELECT store, SUM(count) count FROM sync WHERE dt>='.$dt.$vendor.' GROUP BY store) ven ON ven.store=store.i WHERE '.implode(' AND ', $where);
		//if (kv($args, 'min', 0)) { $select.= ' AND '.$args['min'].'<=ven.count'; }
		//if (kv($args, 'price', 0)) { $select.= ' AND '.$args['price'].'<=price'; }


		// print_pre($select); return;
		//$force = kv($args, 'force', 0);
		$upd = [];

		$count = 0;


		$min = max(0, kv($args, 'min', 0));

		$rows = \Db::fetchAll($select, 'i');

		if ($test) {
			return \Flydom\Json::encode($rows, 0);
		}

		$reserve = \Tool\Reserve::get(array_keys($rows));

		//$q = db_query($select);
		//while ($i = db_fetch($q)) {
		foreach ($rows as $i) {

			$count++;

			if ($i['count']<$min) {
				$i['count'] = 0;
			}

			if ($args['form'] < 10) {

				if (kv($args, 'price', 0) > 0) {
					$decoded = \Cron\Prices::decode($i['prices']);
					if (isset($decoded[$args['price'] - 1])) {
						$price = $decoded[$args['price'] - 1];
						if (!strlen($price)) {
							$price = 0;
						}
					} else {
						continue;
					}
				} else {
					$price = $i['price'];
				}

				if (!kv($args, 'zero', 1) && !$price) {
					continue;
				}
				//if ($price == $prices[$i['i']]) {
				//	continue;
				//}
				$upd[$i['i']] = $price;
			} else {
				if ($args['form'] == 19) {
					$i['count'] = 0;
				} else {
					$i['count']-= kv($args, 'minus', 0);
				}

				$i['count']-= kv($reserve, $i['i'], 0);

				if (!kv($args, 'zero', 1) && !$i['count']) {
					continue;
				}
				$upd[$i['i']] = $i['count'] ?? 0;
			}
		}

		// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

		$upd_count = count($upd);
		$updated = 0;
		while (count($upd)) {

			if ($updated > 0) { sleep(1); }

			$upd2 = array_slice($upd, 0, 100, true);
			$upd = array_slice($upd, 100, count($upd), true);

			if ($args['form'] < 10) {
				$a = [];
				foreach ($upd2 as $k=>$v) {
					$a[] = [
						'offer_id'=>'М'.$k,
						'price'=>$v,
						'old_price'=>0,
						'min_price'=>strval(max(0, ceil($v * 0.98))),
					]; // new OzonPrice($k, $v);
				}
				$post = [
					'prices'=>$a
				]; // new OzonPrices($a);
			} else {
				$a = [];
				foreach ($upd2 as $k=>$v) {
					$a[] = [
						'product_id'=>$pids[$k] ?? '',
						'offer_id'=>'М'.$k,
						'stock'=>max(0, $v * 1) ?? 0,
						'warehouse_id'=>$warehouse,
					]; // new OzonStock($k, $v);
				}
				$post = [
					'stocks'=>$a
				]; // new OzonStocks($a);
			}

			w('log');
			logs(399, 0, \Flydom\Json::encode($post, 0));

			if ($updated > 0) {
				sleep(1);
			}

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url2);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, \Flydom\Json::encode($post, 0));
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Client-Id: '.$args['client'],
				'Api-Key: '.$args['api'],
				'Content-Type: application/json',
			]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$result = curl_exec($ch);
			curl_close($ch);

			if ($test) {
				return $url2.'<BR>'.\Flydom\Json::encode($post, 0).'<BR>'.$result;
			}

			$a = json_decode($result, true);

			$errors = [];
			if (isset($a['result'])) {
				foreach ($a['result'] as $i) {
					if (count($i['errors'])) {
						$errors[] = $i['offer_id'].': '.$i['errors'][0]['message'];
					}
				}
				$updated+= count($upd2);
			} else {
				w('log');
				logs(395, 0, $result.' | '.\Flydom\Json::encode($post, 0));
				break;
			}

			if (kv($args, 'alert', 0)) {
				foreach ($errors as $i) {
					\Flydom\Alert::warning($i);
					logs(395, 0, $i);
				}
			}
		}

		return 'Обновлено '.$updated.' из '.$count.($upd_count > $updated ? ', не обновились: '.($upd_count - $updated) : '');
	}

	static function getPrices($args)
	{
		$items = [];
		$prices = [];
		$post = ['filter'=>['visibility'=>'ALL'], 'limit'=>1000];

		do {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api-seller.ozon.ru/v5/product/info/prices');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post, JSON_FORCE_OBJECT));
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Client-Id: '.$args['client'],
				'Api-Key: '.$args['api'],
				'Content-Type: application/json',
			]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$result = curl_exec($ch);
			curl_close($ch);

			$json = json_decode($result, 1);

			if (!isset($json['items'])) {
				\Flydom\Log::add(395, 0, $result);
				break;
			}

			$chunk = $json['items'] ?? [];
			if (empty($chunk)) {
				\Flydom\Log::add(395, 0, $result);
				break;
			}
			$items = array_merge($items, $chunk);
			$post['cursor'] = $json['cursor'];
		} while (count($chunk) == $post['limit']);

		foreach ($items as $i) {
			$id = mb_substr($i['offer_id'], 1);
			if (!is_09($id)) {
				if (kv($args, 'alert', 0)) {
					\Flydom\Alert::warning('Неправильный артикул: '.$id);
				}
				continue;
			}

			$prices[$id] = $i['price']['price'];
		}

		return $prices;
	}

	static function getPids($args)
	{
		$items = [];
		$post = array(
			'filter'=>[
				'visibility'=>'ALL',
			],
			'limit'=>1000,
			'last_id'=>''
		);

		do {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api-seller.ozon.ru/v3/product/list');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post, JSON_FORCE_OBJECT));
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Client-Id: '.$args['client'],
				'Api-Key: '.$args['api'],
				'Content-Type: application/json',
			]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$result = curl_exec($ch);
			curl_close($ch);

			$json = json_decode($result, 1);

			if (!isset($json['result'])) {
				\Flydom\Log::add(395, 0, $result);
				break;
			}
			$post['last_id'] = $json['result']['last_id'];
			$chunk = kv($json['result'], 'items', []);
			if (empty($chunk)) {
				w('log');
				logs(395, 0, $result);
				break;
			}
			$items = array_merge($items, $chunk);
		} while (count($chunk) == $post['limit']);

		$pids = [];
		foreach ($items as $i) {
			$id = mb_substr($i['offer_id'], 1);
			if (!is_09($id)) {
				if (kv($args, 'alert', 0)) {
					\Flydom\Alert::warning('Неправильный артикул: '.$id);
				}
				continue;
			}

			$pids[$id] = $i['product_id'];
		}

		return $pids;
	}
}
