<?

namespace Cron;

use stdClass;

// https://openapi.wildberries.ru/

class Wildberries extends Task {

	public static function run($args) {

		global $config;

		$client = kv($args, 'client', 16838);
		$con = $config['wildberries'][$client];

		$form = $args['form'];

		$per = 500;

		$exclude = explode(' ', kv($args, 'exclude', ''));

		if ($form == 1 || $form == 10) { // Обновление (обнуление) количества
			$back = self::stock($form, $args, $con, $exclude, $per);
		}

		if ($form == 3) { // Обновление цен
			$back = self::price($args, $con, $exclude, $per);
		}

		if ($form == 20) { // Получение заказов
			$back = self::order_new($args);
		}

		if ($form == 25) { // Получение отмен заказов
			$back = self::order_cancel($args);
		}

		return $back;
	}

	static function stock($form, $args, $con, $exclude, $per = 1000) {

		$where = ['wb.client='.$con['user']];

		if (kv($args, 'price', 0)) {
			$where[]= 'store.price >= '.$args['price'];
		}

		if (strlen(kv($args, 'test', ''))) {
			$where[]= '(wb.barcode="'.addcslashes($args['test'], '"\\').'" OR store.i="'.clean_09($args['test']).'")';
		}


		$select = 'SELECT wb.*,store.price s_price FROM wb LEFT JOIN store ON wb.store=store.i WHERE '.implode(' AND ', $where);

		$store = [];
		$q = db_query($select);
		while ($row = db_fetch($q)) {
			if (!in_array($row['barcode'], $exclude)) {
				$key = $row['store'];
				unset($row['store']);
				$store[$key] = $row;
			}
		}

		if (count($store)) {


			$where = [
				'dt>='.(now() - 30*24*60*60), // актуальность синхронизации
				'store IN ('.implode(',', array_keys($store)).')',
			];

			if (is_array($args['vendor']) && count($args['vendor'])) {
				$where[] = 'vendor IN ('.implode(',', $args['vendor']).')';
			}

			$select = 'SELECT store, SUM(count) count FROM sync WHERE '.implode(' AND ', $where).' GROUP BY store';

			$q = db_query($select);
			while ($i = db_fetch($q)) {

				$count = max(0, $i['count'] - kv($args, 'minus', 0));

				if ($count < kv($args, 'min', 0)) {
					$count = 0;
				}

				$store[$i['store']]['s_count'] = $count;
			}
			db_close($q);

		}

		$force = kv($args, 'force', 0);

		$rows = [];
		$updates = [];

		$reserve = \Tool\Reserve::get(array_keys($store));

		foreach ($store as $k=>$i) {
			if (empty($i['barcode'])) {
				continue;
			}

			$upd = $force == 100;

			if (!isset($i['s_count'])) {
				if ($force == 1) {
					continue;
				} else {
					$i['s_count'] = 0;
				}
			}

			if (is_null($i['s_price'])) {
				$i['s_count'] = 0;
			}

			if (isset($reserve[$k])) {
				$i['s_count'] = max(0, $i['s_count'] - $reserve[$k]);
			}

			if ($form == 10) {
				$i['s_count'] = 0;
			}

			if ($i['quantity'] != $i['s_count']) {
				$upd = true;

				$updates[$i['i']] = [
					'quantity'=>is_null($i['s_count']) ? 0 : $i['s_count'],
				];
			}

			if ($upd) {

				//$rows[] = [
				//	'sku'=>$i['barcode'],
				//	'amount'=>is_null($i['s_count']) ? 0 : (int) $i['s_count'],
				//];

				//$obj = new stdClass();
				//$obj->sku = $i['barcode'];
				//$obj->amount = is_null($i['s_count']) ? 0 : $i['s_count'];

				$rows[] = [
					'sku'=>$i['barcode'],
					'amount'=>is_null($i['s_count']) ? 0 : $i['s_count'],
				];

				//	'nmId'=>(int) $i['i'],
				//	'chrtId'=>(int) $i['chrt'],
				//	'warehouseId'=>(int) $con['storeId'],
			}
		}

		if (count($rows)) {
			$url = 'https://marketplace-api.wildberries.ru/api/v3/stocks/'.$con['storeId'];

			$page = 0;
			while ($page*$per < count($rows)) {
				$post = array_slice($rows, $page*$per, $per);
				if ($page) { sleep(1); }
				$page++;

				$payload = \Flydom\Json::encode(['stocks'=>$post]);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				curl_setopt($ch, CURLOPT_HTTPHEADER, [
					'Authorization: '.$con['authorization'],
					'Content-Type: application/json',
					'Content-Length: '.strlen($payload)
				]);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				$result = curl_exec($ch);
				$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);

				if (strlen(kv($args, 'test', ''))) {
					w('log');
					logs(401, $code, $result.' | '.$payload);
				}

				if (($code != 200 && $code != 204) && strlen($result)) {
					break;
				}
			}
		} else {
			$code = 200;
		}

		if ($code == 200 || $code == 204) {
			$back = ($form == 10 ? 'Обнулено' : 'Обновлено').'&nbsp;'.count($rows);

			foreach ($updates as $k=>$v) {
				db_update('wb', $v, ['i'=>$k]);
			}
		} else {
			$back = 'Ошибка: '.$result;

			w('log');
			logs(401, $code, $result.' | '.$payload);
		}

		return $back;
	}

	static function price($args, $con, $exclude, $per = 1000) {

		$select = 'SELECT wb.*,store.price s_price,store.prices s_prices FROM wb LEFT JOIN store ON wb.store=store.i WHERE wb.client='.$con['user'];
		//if (kv($args, 'price', 0)) { $select.= ' AND store.price >= '.$args['price']; }
		//$store = db_fetch_all($select, 'store');

		if (strlen(kv($args, 'test', ''))) {
			$select.= ' AND (wb.barcode="'.addcslashes($args['test'], '"\\').'" OR store.i="'.clean_09($args['test']).'")';
		}

		$store = [];
		$q = db_query($select);
		while ($row = db_fetch($q)) {
			if (!in_array($row['i'], $exclude)) {
				$key = $row['store'];
				unset($row['store']);
				$store[$key] = $row;
			}
		}

		$force = kv($args, 'force', 0);

		$rows = [];
		$updates = [];

		$now = now();

		foreach ($store as $i) {
			//if (14*24*60*60 > ($now - $i['dt'])) { continue; }
			if ($now < $i['dt']) { continue; }

			if (kv($args, 'type', 0) > 0) {
				$decoded = \Cron\Prices::decode($i['s_prices']);
				if (isset($decoded[$args['type'] - 1])) {
					$price = $decoded[$args['type'] - 1];
					if (!strlen($price)) {
						$price = 0;
					}
				} else {
					continue;
				}
			} else {
				$price = $i['s_price'];
			}

			$upd = $force == 100;

			if (is_null($price)) {
				$price = $i['price'];
			}

			if (($args['price'] ?? 0) && ($price < $args['price'])) {
				continue;
			}

			if ($i['price'] != $price) {
				$upd = true;

				$updates[$i['chrt']] = [
					'price'=>$price
				];
			}

			if ($upd) {
				$rows[$i['chrt']] = [
					'nmId'=>$i['i'],
					'price'=>$price,
					'discount'=>0
				];
			}
		}

		$back = '';
		$updated = 0;

		if (count($rows)) {
			//$url = 'https://marketplace-api.wildberries.ru/public/api/v1/prices';
			$url = 'https://discounts-prices-api.wildberries.ru/api/v2/upload/task';

			$page = 0;

			while ($page*$per < count($rows)) {
				$post = array_slice($rows, $page*$per, $per, true);
				if ($page) { sleep(1); }
				$page++;

				$payload = \Flydom\Json::encode(['data'=>array_values($post)]);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Authorization: '.$con['authorization'],
					'Content-Type: application/json',
				));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				$result = curl_exec($ch);
				$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);

				if (strlen(kv($args, 'test', ''))) {
					w('log');
					logs(403, $code, $result.' | '.$payload);
				}

				if ($code != 200) {
					if (strpos($result, "все номенклатуры с ценами из списка уже загружены")) {
						$code = 200;
					}
				}

				if ($code == 200) {
					$updated+= count($post);

					foreach ($post as $k=>$p) {
						if (isset($updates[$k])) {
							$v = $updates[$k];
							db_update('wb', $v, ['chrt'=>$k]);
						}
					}
				} else {
					$back.= ' '.$page.':'.$result;

					w('log');
					logs(403, 0, $result.' | '.$payload);
				}
			}
		}

		if ($updated) {
			$back = 'Обновлено&nbsp;'.$updated.' '.$back;
		}

		return trim($back);
	}

	static function order_new($args)
	{
		global $config;
		$con = $config['wildberries'][$args['client']];

		\Flydom\Parallel::lock('wildberries-create');

		$not_found = [];

		$count = 0;
		$page = 0;
		do {

			$url = 'https://marketplace-api.wildberries.ru/api/v3/orders/new';

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: '.$con['authorization'],
				'Content-Type: application/json',
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$result = curl_exec($ch);
			curl_close($ch);

			$brand = cache_load('brand');
			$user = $con['user'];
			$mark = db_get_row('SELECT mark,mark2 FROM user WHERE i='.$user);
			$mark = ','.trim($mark['mark'].','.$mark['mark2'], ',').',';

			w('log');
			logs(420, 0, $result);

			$result = kv(json_decode($result, 1), 'orders', []);
			foreach ($result as $order) {

				if ($order['warehouseId'] != $con['storeId']) {
					continue;
				}

				$exists = db_result('SELECT COUNT(*) FROM orst WHERE mpi="'.$order['id'].'" AND user='.$user);
				if ($exists) { continue; }

				$mpdt = date_create($order['createdAt'])->getTimestamp();

		//		'count'=>1,
		//		'price'=>$item['total_price'] / 100,
		//		'sku'=>$item['rid'],

				//$q = db_query('SELECT wb.chrt,store.name,store.model,store.brand,store.i store FROM store,wb WHERE wb.store=store.i AND wb.chrt="'.$order['chrtId'].'"');
				//$i = db_fetch($q);
				//db_close($q);
				$store = \Db::fetchRow('SELECT wb.chrt,store.name,store.model,store.brand,store.i FROM store,wb WHERE wb.store=store.i AND wb.chrt="'.$order['chrtId'].'"');
				if (!$store) {
					$i = self::parse_article($order['article']);
					if ($i) {
						$store = \Db::fetchRow('SELECT wb.chrt,store.name,store.model,store.brand,store.i FROM store,wb WHERE wb.store=store.i AND store.i='.$i);
					}
				}

				if ($store) {
					$order['name'] = trim(kv($brand, $store['brand'], '').' '.$store['model'].' '.$store['name']);
					$order['store'] = $store['i'];
				} else {
					// TODO: Логировать ошибку поиска товара
					$not_found[] = \Flydom\Cache::encode($order);
					continue;
				}

				$count++;
				(new \Model\Order([
					'dt'=>now(),
					'last'=>now(),
					'user'=>$user,
					'staff'=>null,
					'state'=>1,
					'cire'=>34,
					'city'=>'', // Адрес?
					'adres'=>'',
					'dost'=>'self',
					'store'=>$order['store'],
					'name'=>$order['name'],
					'price'=>$order['convertedPrice'] / 100,
					'count'=>1,
					'info'=>'', // Примечание?
					'note'=>'',
					'mark'=>$mark,
					'mpi'=>$order['id'],
					'mpdt'=>$mpdt + 48*60*60,
					'sku'=>$order['rid'],
				]))->create();
			}

			$page++;
		} while ($page < 1);

		$error = '';
		if (count($not_found)) {
			$error = ', не найдены товары: '.implode(', ', $not_found);
		}

		\Flydom\Parallel::unlock('wildberries-create');

		return 'Загружено '.$count.' заказов'.$error;
	}

	static function order_cancel($args, $per = 500) {

		global $config;
		$con = $config['wildberries'][$args['client']];
		$user = $con['user'];

		$count = 0;

		$orders = \Db::fetchList("SELECT mpi FROM orst WHERE state<30 AND user=$user");

		$page = 0;
		while ($page*$per < count($orders)) {
			$post = array_slice($orders, $page*$per, $per);
			$page++;

			$url = 'https://marketplace-api.wildberries.ru/api/v3/orders/status';

			$payload = \Flydom\Json::encode(['orders'=>$post]);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: '.$con['authorization'],
				'Content-Type: application/json',
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$result = curl_exec($ch);
			curl_close($ch);

			w('log');
			logs(425, 0, $result);

			$result = kv(json_decode($result, 1), 'orders', []);
			foreach ($result as $order) {


				if ($order['wbStatus'] != 'canceled'
				&& $order['wbStatus'] != 'canceled_by_client'
				&& $order['wbStatus'] != 'declined_by_client') {
					continue;
				}

				$found = db_fetch_all('SELECT * FROM orst WHERE state<>35 AND mpi="'.$order['id'].'" AND user='.$user);

				foreach ($found as $orst) {

					$order = new \Model\Order($orst);
					$order->setState(35);
					$order->update();

					\Tool\Reserve::delete($order->getId());

					$count++;
				}
			}

		}

		return 'Отменено '.$count.' заказов';
	}

	static function parse_article($artikul) {
		$delim = mb_substr($artikul, 0, 1);
        $store = explode($delim, $artikul)[1] ?? 0;
        if (!$store || !ctype_digit($store)) {
        	return false;
        }

		$pathway = cache_load('pathway');
		if (db_result('SELECT COUNT(*) FROM store WHERE i='.$store.' AND up IN ('.implode(',', array_keys($pathway)).')')) {
			return $store;
		} else {
			return false;
		}
	}
}
