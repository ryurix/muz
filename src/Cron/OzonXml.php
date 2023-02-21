<?

namespace Cron;

class OzonPrices { function __construct ($data) { $this->prices = $data; } public $prices; }
class OzonPrice {
	function __construct ($id, $price) {
		$this->offer_id = 'М'.$id;
		$this->price = $price;
		$this->old_price = "0";
		$this->min_price = strval(max(0, ceil($price * 0.9)));
	}
	public $offer_id, $price, $old_price, $min_price;
}

class OzonStocks { function __construct ($data) { $this->stocks = $data; } public $stocks; }
class OzonStock {
	function __construct ($id, $stock) {
		$this->offer_id = 'М'.$id;
		$this->stock = max(0, $stock * 1);
	}
	public $offer_id, $stock;
}

class OzonXml extends Task {

	public static function run($args) {
		$test = kv($args, 'test', 0);

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

			//var_dump($result);
			if ($test) {
				alert($url.'<br>'.json_encode($post, JSON_FORCE_OBJECT), 'primary');
				alert($result, 'info');
			}

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

		$ids = array();
		$prices = array();

		w('clean');
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

		if (!count($ids)) {
			return;
		}

		//alert(php_encode($items));

		// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

		if (is_array($args['vendor']) && count($args['vendor'])) {
			$vendor = ' AND vendor IN ('.implode(',', $args['vendor']).')';
		} else {
			$vendor = '';
		}

		$where = [
			'hide<=0',
			'i IN ('.implode(',', $ids).')',
		];

		if ($test) {
			$where[] = 'store.i='.$test;
		}

		$dt = now() - 30*24*60*60;
		$select = 'SELECT store.*,ven.count FROM store LEFT JOIN (SELECT store, SUM(count) count FROM sync WHERE dt>='.$dt.$vendor.' GROUP BY store) ven ON ven.store=store.i WHERE '.implode(' AND ', $where);
		//if (kv($args, 'min', 0)) { $select.= ' AND '.$args['min'].'<=ven.count'; }
		//if (kv($args, 'price', 0)) { $select.= ' AND '.$args['price'].'<=price'; }


		// print_pre($select); return;
		$upd = array();

		$count = 0;
		$q = db_query($select);

		$min = max(0, kv($args, 'min', 0));

		while ($i = db_fetch($q)) {

			$count++;

			if ($i['count']<$min) {
				$i['count'] = 0;
			}

			if ($args['form'] < 10) {

				if (kv($args, 'price', 0) > 0) {
					$decoded = \Cron\Prices::decode($i['prices']);
					if (isset($decoded[$args['price'] - 1])) {
						$price = $decoded[$args['price'] - 1];
					} else {
						continue;
					}
				} else {
					$price = $i['price'];
				}

				if (!kv($args, 'zero', 1) && !$price) {
					continue;
				}
				if ($price == $prices[$i['i']]) {
					continue;
				}
				$upd[$i['i']] = $price;
			} else {
				if ($args['form'] == 12) {
					$i['count'] = 0;
				} else {
					$i['count']-= kv($args, 'minus', 0);
				}

				if (!kv($args, 'zero', 1) && !$i['count']) {
					continue;
				}
				$upd[$i['i']] = $i['count'];
			}
		}

		// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

		$updated = count($upd);
		while (count($upd)) {

			$upd2 = array_slice($upd, 0, 100, true);
			$upd = array_slice($upd, 100, count($upd), true);

			if ($args['form'] < 10) {
				$a = array();
				foreach ($upd2 as $k=>$v) {
					$a[] = new OzonPrice($k, $v);
				}
				$post = new OzonPrices($a);
			} else {
				$a = array();
				foreach ($upd2 as $k=>$v) {
					$a[] = new OzonStock($k, $v);
				}
				$post = new OzonStocks($a);
			}

			w('log');
			logs(399, 0, json_encode($post));

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url2);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Client-Id: '.$args['client'],
				'Api-Key: '.$args['api'],
				'Content-Type: application/json',
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$result = curl_exec($ch);
			curl_close($ch);

			if ($test) {
				alert($url2.'<br>'.json_encode($post), 'primary');
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
			} else {
				w('log');
				logs(395, 0, $result);
				break;
			}

			if (kv($args, 'alert', 0)) {
				foreach ($errors as $i) {
					alert($i);
					logs(395, 0, $i);
				}
			}
		}

/*
		$more = '';
		if (isset($args['follow']) && is_array($args['follow']) && count($args['follow'])) {
			$q = db_query('SELECT * FROM cron WHERE typ='.\Type\Cron::OZON_XML.' AND i IN ('.implode(',', $args['follow']).') ORDER BY name');
			$datas = array();
			while ($row = db_fetch($q)) {
				$data = array_decode($row['data']);
				$data['follow'] = array();
				$datas[] = $data;
			}
			db_close($q);

			foreach ($datas as $data) {
				sleep(2);
				$more.= ', '.w2('ozon', $data);

				$class = \Type\Cron::class(\Type\Cron::OZON_XML);
				if ($class) {
					try {
						$info = call_user_func($class, $data);
					} catch (\Exception $ex) {
						$info = $ex->getMessage();
					}
				} else {
					$info = 'Тип задачи не опознан: '.$row['typ'];
				}

				$more.= $info;
			}
		}
*/

/*
		$more = '';
		if (isset($args['follow']) && is_array($args['follow']) && count($args['follow'])) {
			$rows = db_fetch_all('SELECT * FROM cron WHERE typ='.\Type\Cron::WILDBERRIES.' AND i IN ('.implode(',', $args['follow']).') ORDER BY name');

			foreach ($rows as $row) {
				sleep(2);

				$class = \Type\Cron::class(\Type\Cron::WILDBERRIES);
				if ($class) {
					try {
						$info = call_user_func($class, array_decode($row['data']));
					} catch (\Exception $ex) {
						$info = $ex->getMessage();
					}
				} else {
					$info = 'Тип задачи не опознан: '.$row['typ'];
				}

				$more.= ', '.$info;
			}
		}
*/

		return $updated.'/'.$count;
	}
}