<?

namespace Cron;

class Ozon extends Task {

	static function ozon_query($ozon, $url, $args) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api-seller.ozon.ru'.$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Client-Id: '.$ozon['client'],
			'Api-Key: '.$ozon['api'],
			'Content-Type: application/json',
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$result = curl_exec($ch);
		curl_close($ch);

		return json_decode($result, true);
	}

	public static function run($args) {

		//	https://cb-api.ozonru.me/apiref/ru/#t-fbs_list
		// https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetFbsPostingListV3

		return 'Создано '.self::create().', удалено '.self::cancel().' заказов.';

	}

	public static function create() {

		\Flydom\Parallel::lock('ozon-create');

		$count = 0;

		$data = self::list('awaiting_packaging');

		foreach ($data as $order) {

			$where = [
				'mpi="'.addslashes($order['posting_number']).'"',
				'user='.$order['user'],
			];
			// if ($store) { $where[] = 'store='.$store; }

			$exists = db_result('SELECT COUNT(*) FROM orst WHERE '.implode(' AND ', $where));
			if ($exists) { continue; }

			foreach ($order['products'] as $item) {
				$store = clean_09($item['offer_id']);

				$mark = db_get_row('SELECT mark,mark2 FROM user WHERE i='.$order['user']);
				$mark = $mark ? ','.trim($mark['mark'].','.$mark['mark2'], ',').',' : '';

				(new \Model\Order([
					'user'=>$order['user'],
					'staff'=>null,
					'cire'=>34,
					'city'=>'', // Адрес?
					'adres'=>'',
					'dost'=>'self',
					'store'=>$store,
					'name'=>$item['name'],
					'price'=>$item['price'],
					'count'=>$item['quantity'],
					'info'=>'', // Примечание?
					'note'=>count($order['products']) > 1 ? 'парный заказ' : '',
					'mark'=>$mark,
					'mpi'=>$order['posting_number'],
					'mpdt'=>ft_parse($order['shipment_date'], true),
					'sku'=>clean_int($item['sku']),
				]))->create();

				sleep(1); // TODO: make check for concurrent run

				$count++;
			}
		}

		\Flydom\Parallel::unlock('ozon-create');

		return $count;
	}

	public static function cancel() {

		$count = 0;

		$data = self::list('cancelled');

		foreach ($data as $order) {

			foreach ($order['products'] as $item) {
				$store = clean_09($item['offer_id']);

				$where = [
					'mpi="'.addslashes($order['posting_number']).'"',
					'user='.$order['user'],
					'state<35',
				];
				if ($store) {
					$complex = \Db::fetchList('SELECT store FROM complex WHERE up='.$store);
					if (count($complex)) {
						$where[] = 'store IN ('.implode(',', $complex).')';
					} else {
						$where[] = 'store='.$store;
					}
				}

				$ids = \Db::fetchList('SELECT i FROM orst WHERE '.implode(' AND ', $where));


				if (count($ids)) {
					$count+= count($ids);

					foreach ($ids as $id) {
						$o = new \Model\Order($id);
						$o->setState(35);
						$o->update();
					}

					//\Db::update('orst', ['state'=>35], ['i IN ('.implode(',', $ids).')']);
					//foreach ($ids as $id) {
					//	\Tool\Reserve::delete($id);
					//}
				}
			}
		}

		return $count;
	}

	public static function list($status, $days = 3) {

		// https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetFbsPostingListV3

		w('ft');
		w('clean');

		$limit = 10;

		$data = [];

		global $config;
		foreach ($config['ozon'] as $user=>$ozon) {

			$result = [];
			$offset = 0;

			do {
				$new = self::ozon_query($ozon, '/v3/posting/fbs/list', [
					'dir'=>'asc',
					'filter'=>[
						'since'=>date('c', now() - $days*2*24*60*60),
						'to'=>date('c', now()),
						'status'=>$status
					],
					'limit'=>$limit,
					'offset'=>$offset,
				]);

				$offset+= $limit;

				if (!isset($new['result'])) {
					w('log');
					logs(355, $user, $status.' '.json_encode($new));
					break;
				}

				$found = count($new['result']['postings']);
				$result = array_merge($result, $new['result']['postings']);
			} while ($found == $limit);

			foreach ($result as $order) {
				$order['user'] = $user;
				$data[] = $order;
			}
		}

		return $data;

	}

	public static function pack($order) {
		global $config;

		if (!isset($config['ozon']) || !isset($config['ozon'][$order->getUser()])) {
			return;
		}

		$ozon = $config['ozon'][$order->getUser()];

		// Ищем другие заказы того же номера в другом статусе
		//$all = db_fetch_all(db_select('i,count,sku,state,complex', 'orst', ['mpi'=>$order->getMpi()]), 'i');
		$all = db_fetch_all("SELECT MIN(count) count,sku,state FROM orst WHERE mpi='".$order->getMpi()."' GROUP BY sku,state");

		// Если нашлись -- ждём когда все заказы будут в одинаковом статусе
		foreach ($all as $i) {
			if ($i['state'] != $order->getState()) {
				return;
			}
		}

		$items = [];
		foreach ($all as $i) {

			// 06.02.2025 АК: пожелание сделать так чтобы заказы где больше 1 шт не собирались на озоне
			if ($i['count'] > 1) { continue; }

			$items[]= [
	//			'exemplar_info'=>[
	//				'is_gtd_absent'=>true,
	//				'mandatory_mark'=>'',
	//			],
				'product_id'=>$i['sku'],
				'quantity'=>$i['count'],
			];
		}

		$data = [
			'packages'=>[[
				'products'=>$items,
			]],
			'posting_number'=>$order->getMpi(),
			'with'=>[
				'additional_data'=>false,
			]
		];

		$post = self::ozon_query($ozon, '/v4/posting/fbs/ship', $data);

		$success = true;

		if (!isset($post['result'])) {
			\Flydom\Log::add(375, $order->getUser(), json_encode($post).' :: '.json_encode($data));
			$success = false;

			if (is_null($post) || $post == 'null') {
				for ($i = 1; $i<=5; $i++) {

					sleep(1);
					$post = self::ozon_query($ozon, '/v4/posting/fbs/ship', $data);
					\Flydom\Log::add(375, $order->getUser(), 'Repeat '.$i.' :: '.json_encode($post));

					if (!(is_null($post) || $post == 'null')) { $success = true; break; }
				}
			}
		}

		return $success;
	}
}
