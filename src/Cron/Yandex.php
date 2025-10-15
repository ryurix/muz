<?

//	https://yandex.ru/dev/market/partner-api/doc/ru/step-by-step/fbs
//

namespace Cron;

class Yandex extends Task {

	static function post($url, $args, $data = []) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.partner.market.yandex.ru'.$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Api-Key: '.$args['key'],
			'Authorization: Bearer '.$args['token'],
			'Content-Type: application/json'
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$result = curl_exec($ch);
		curl_close($ch);

		return json_decode($result, true);
	}

	static function put($url, $args, $data = []) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.partner.market.yandex.ru'.$url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Api-Key: '.$args['key'],
			'Authorization: Bearer '.$args['token'],
			'Content-Type: application/json'
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$result = curl_exec($ch);
		curl_close($ch);

		return json_decode($result, true);
	}

	static function get($url, $args, $data = null)
	{
		if (!empty($data)) { $url.= '?'.static::urldata($data); }

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.partner.market.yandex.ru'.$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Api-Key: '.$args['key'],
			'Authorization: Bearer '.$args['token'],
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$result = curl_exec($ch);
		curl_close($ch);

		return json_decode($result, true);
	}

	public static function run($args)
	{
		$args+= $args + \Cabinet\Model::load($args['usr']);

		if ($args['form'] == 3) {
			return self::orders($args);
		}
		if ($args['form'] == 10) {
			return self::zero($args);
		}
		if ($args['form'] == 11) {
			return self::stocks($args);
		}
		return 'Неизвестный формат: '.($args['form'] ?? '');
	}

	static function orders($args)
	{
		$result = '';
		$data = ['status'=>['CANCELLED', 'PROCESSING', 'UNPAID']];
		$resp = self::get('/campaigns/'.$args['campaign'].'/orders', $args, $data);
		$result.= static::ordersProcess($resp['orders'], $args['usr']);

		if (is_array($resp['orders'])) {
			while (isset($resp['paging']['nextPageToken'])) {
				$data['page_token'] = $resp['paging']['nextPageToken'];
				$resp = self::get('/campaigns/'.$args['campaign'].'/orders', $args, $data);

				$result.= static::ordersProcess($resp['orders'], $args['usr']);
			}
		}

		return self::resultDecode($result);
	}

	protected static function resultDecode($result) {
		$count = [];
		foreach (str_split($result) as $i) {
			$count[$i] = isset($count[$i]) ? $count[$i] + 1 : 1;
		}

		$info = [];
		foreach ($count as $k=>$v) {
			switch ($k) {
				case '+': $info[] = 'создано '.$v; break;
				case '-': $info[] = 'отменено '.$v; break;
				case '=': $info[] = 'в работу '.$v; break;
			}
		}

		return implode(', ', $info);
	}

	protected static function ordersProcess($orders, $user) {
		$result = '';
		foreach ($orders as $o) {
			if ($o['status'] == 'CANCELLED') {
				$result.= static::orderCancel($o, $user);
			} elseif ($o['status'] == 'UNPAID') {
				if (!\Db::result(\Db::select('*', 'orst', ['mpi'=>$o['id']]))) {
					$result.= static::orderCreate($o, $user, 0);
				}
			} elseif ($o['status'] == 'PROCESSING') {
				$rows = \Db::fetchAll(\Db::select('*', 'orst', ['mpi'=>$o['id']]));
				if (empty($rows)) {
					static::orderCreate($o, $user, 1);
				} else {
					foreach ($rows as $i) {
						$order = new \Order\Model($i);
						if ($order->getState() == 0) {
							$order->setState(1);
							$order->save();
							$result.= '=';
						}
					}
				}
			}
		}
		return $result;
	}

	protected static function orderCreate($order, $user, $state = 0) {
//		$items = [];
//		foreach ($order['items'] as $i) {
//			$items[\Flydom\Clean::uint($i['offerId'])] = $i;
//		}
		$result = '';

		$mark = \Db::fetchRow('SELECT mark,mark2 FROM user WHERE i='.$user);
		$mark = ','.trim($mark['mark'].','.$mark['mark2'], ',').',';

		$delivery = $order['delivery'];
		foreach ($order['items'] as $i) {
			$order = new \Order\Model([
				'user'=>$user,
				'staff'=>null,
				'state'=>$state,
				'cire'=>34,
				'city'=>$delivery['region']['name'],
				'adres'=>'',
				'dost'=>'self',
				'store'=>\Flydom\Clean::uint($i['offerId']),
				'name'=>$i['offerName'],
				'price'=>$i['price'],
				'count'=>$i['count'],
				'info'=>$o['notes'] ?? '',
				'note'=>count($order['items']) > 1 ? 'парный заказ' : '',
				'mark'=>$mark,
				'mpi'=>$order['id'],
				'mpdt'=>\Flydom\Time::parse($delivery['shipments'][0]['shipmentDate']),
			]);
			$order->create();
			$result.= '+';
		}

		return $result;
	}

	protected static function orderCancel($order, $user) {
		$result = '';
		$ids = \Db::fetchList('SELECT i FROM orst WHERE state<30 AND user='.$user.' AND mpi='.$order['id']);
		foreach ($ids as $i) {
			$order = new \Order\Model($i);
			$order->setState(35);
			$order->save();
			$result.= '-';
		}

		return $result;
	}

	static function zero($args) {
		self::post('/campaigns/'.$args['campaign'].'/hidden-offers', $args);
	}

	static function urldata($data) {
		$values = [];
		foreach ($data as $k=>$v) {
			if (is_array($v)) {
				foreach ($v as $i) {
					$values[] = $k.'='.urlencode($i);
				}
			} elseif (is_bool($v)) {
				$values[] = $k.'='.($v ? 1 : 0);
			} else {
				$values[] = $k.'='.urlencode($v);
			}
		}
		return implode('&', $values);
	}

	static function stocks($args)
	{
		$test = \Flydom\Clean::uint($args['test'] ?? 0);

		$ids = [];

		if ($test) {
			$ids[] = $test;
		} else {
			$stocks = self::getStocks($args);
			$ids = array_keys($stocks);
		}

		if (!count($ids)) {
			return 'Не найдены товары на аккаунте';
		}

		$ups = array_keys(\Flydom\Cache::get('pathway', [0]));

		if ($test) {
			$ids = [$test];
		} else {
			foreach ($ids as $k=>$v) {
				$ids[$k] = \Flydom\Clean::int($v);
			}
		}

		$chunks = \Flydom\Arrau::chunks($ids, 500);
		$uploaded = 0;

		foreach ($chunks as $chunk) {

			$where = [
				'hide<=0',
				'i IN ('.implode(',', $chunk).')',
				'up IN ('.implode(',', $ups).')',
			];

			if (is_array($args['vendor']) && count($args['vendor'])) {
				$vendor = ' AND vendor IN ('.implode(',', $args['vendor']).')';
			} else {
				$vendor = '';
			}

			$dt = \Config::now() - 30*24*60*60;
			$select = 'SELECT store.i,ven.count FROM store LEFT JOIN (SELECT store, SUM(count) count FROM sync WHERE dt>='.$dt.$vendor.' GROUP BY store) ven ON ven.store=store.i WHERE '.implode(' AND ', $where);

			$data = ['skus'=>[]];

			$q = \Db::select($select);
			while ($row = \Db::fetch($q)) {
				$uploaded++;

				$count = $row['count'] - $args['minus'];
				if ($count < $args['min']) {
					$count = 0;
				}
				$data['skus'][] = [
					'sku'=>'М'.$row['i'],
					'items'=>[
						[
							'count'=>$count,
							'updatedAt'=>date('c')
						]
					],
				];
			}
			\Db::free($q);

			if (empty($data['skus'])) {
				\Flydom\Log::add(213, 0, 'Empty result: '.$select);
				return 'Ошибка: в базе не найдены товары для выгрузки ('.count($chunk).')';
			}

			$result = self::put('/campaigns/'.$args['campaign'].'/offers/stocks', $args, $data);

			if (($result['status'] ?? '') != 'OK') {
				\Flydom\Log::add(213, 0, \Flydom\Arrau::encode($result));
				return 'Ошибка '.\Flydom\Arrau::encode($result);
			}
		}

		return 'Товаров на аккануте '.count($ids).', выгружено '.$uploaded;
	}

	static function getStocks($args)
	{
		$items = [];

		$filter = [
			'withTurnover'=>false,
			'archived'=>false
		];

		$limit = 200;
		$params = ['limit'=>$limit];

		do {
			$url = '/campaigns/'.$args['campaign'].'/offers/stocks?'.static::urldata($params);

			if (isset($param['page_token'])) { usleep(100); }

			$result = static::post($url, $args, $filter);

			if (!isset($result['result'])) {
				\Flydom\Log::add(213, 0, \Flydom\Arrau::encode($result));
				break;
			}

			$result = $result['result'];

			$count = 0;
			foreach ($result['warehouses'] as $warehouse) {

				foreach ($warehouse['offers'] as $offer) {
					$count++;
					$sum = 0;
					foreach ($offer['stocks'] as $stock) {
						$sum+= $stock['count'];
					}
					$items[$offer['offerId']] = $sum;
				}
			}

			if (isset($result['paging']['nextPageToken'])) {
				$params['page_token'] = $result['paging']['nextPageToken'];
			} else {
				// \Flydom\Log::add(\Flydom\Type\Log::WARNING, 0, 'No nextPageToken: '.\Flydom\Arrau::encode($result));
				$count = 0;
			}
		} while ($count >= $limit);

		return $items;
	}

} // Task