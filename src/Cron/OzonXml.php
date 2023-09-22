<?

namespace Cron;

class OzonXml extends Task {

	public static function run($args) {
		w('clean');
		$test = clean_09(kv($args, 'test', 0));

		if ($args['form'] < 10) {
			$url = 'https://api-seller.ozon.ru/v4/product/info/prices';
			$url2 = 'https://api-seller.ozon.ru/v1/product/import/prices';
		} else {
			//$url = 'https://api-seller.ozon.ru/v3/product/info/stocks';
			$url = 'https://api-seller.ozon.ru/v2/product/list';
			$url2 = 'https://api-seller.ozon.ru/v1/product/import/stocks';
		}

		$post = array(
			'filter'=>[
				'visibility'=>'ALL',
			],
			'limit'=>1000,
			'last_id'=>''
		);

		$items = [];

		$ids = [];
		$prices = [];

		if ($test && false) {
			$ids[] = $test;
			$prices[$test] = 1;
		} else {
			do {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
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
			//		print_pre($json);
					w('log');
					logs(395, 0, $result);
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

			foreach ($items as $i) {
				$id = mb_substr($i['offer_id'], 1);
				if (!is_09($id)) {
					if (kv($args, 'alert', 0)) {
						alert('Неправильный артикул: '.$id);
					}
					continue;
				}

				if ($args['form'] < 10) {
					$prices[$id] = $i['price']['price'];
				}
				$ids[] = $id;
			}
		}

		if (!count($ids)) {
			return;
		}

		//alert(php_encode($items));

		// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

		$where = [
			'hide<=0',
			'i IN ('.implode(',', $ids).')',
		];


		if (is_array($args['vendor']) && count($args['vendor'])) {
			$vendor = ' AND vendor IN ('.implode(',', $args['vendor']).')';
		} else {
			$vendor = '';
		}

		if ($test) {
			$where[] = 'store.i='.$test;
		}

		$dt = now() - 30*24*60*60;
		$select = 'SELECT store.*,ven.count FROM store LEFT JOIN (SELECT store, SUM(count) count FROM sync WHERE dt>='.$dt.$vendor.' GROUP BY store) ven ON ven.store=store.i WHERE '.implode(' AND ', $where);
		//if (kv($args, 'min', 0)) { $select.= ' AND '.$args['min'].'<=ven.count'; }
		//if (kv($args, 'price', 0)) { $select.= ' AND '.$args['price'].'<=price'; }


		// print_pre($select); return;
		//$force = kv($args, 'force', 0);
		$upd = [];

		$count = 0;


		$min = max(0, kv($args, 'min', 0));

		$rows = \Flydom\Db::fetchAll($select, 'i');

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
				$upd[$i['i']] = $i['count'];
			}
		}

		// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

		$upd_count = count($upd);
		$updated = 0;
		while (count($upd)) {

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
						'offer_id'=>'М'.$k,
						'stock'=>max(0, $v * 1),
					]; // new OzonStock($k, $v);
				}
				$post = [
					'stocks'=>$a
				]; // new OzonStocks($a);
			}

			w('log');
			logs(399, 0, \Flydom\Cache::json_encode($post, 0));

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url2);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, \Flydom\Cache::json_encode($post, 0));
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
				alert($url2.'<br>'.\Flydom\Cache::json_encode($post, 0), 'info');
				alert($result, 'success');
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
				logs(395, 0, $result.' | '.\Flydom\Cache::json_encode($post, 0));
				break;
			}

			if (kv($args, 'alert', 0)) {
				foreach ($errors as $i) {
					alert($i);
					logs(395, 0, $i);
				}
			}
		}

		return 'Обновлено '.$updated.' из '.$count.($upd_count > $updated ? ', не обновились: '.($upd_count - $updated) : '');
	}
}