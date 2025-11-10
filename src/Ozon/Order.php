<?

namespace Ozon;

class Order {

	public static function run($args) {

		//	https://cb-api.ozonru.me/apiref/ru/#t-fbs_list
		// https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetFbsPostingListV3


		\Cabinet\Model::load($args['usr']);

		return 'Создано '.self::create().', удалено '.self::cancel().' заказов.';
	}

	public static function create() {

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
				$store = \Flydom\Clean::firstUint($item['offer_id']);

				$mark = db_get_row('SELECT mark,mark2 FROM user WHERE i='.$order['user']);
				$mark = $mark ? ','.trim($mark['mark'].','.$mark['mark2'], ',').',' : '';

				(new \Order\Model([
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
					'mpdt'=>\Flydom\Time::parseYmd($order['shipment_date']),
					'sku'=>\Flydom\Clean::firstUint($item['sku']),
				]))->create();

				sleep(1);

				$count++;
			}
		}

		return $count;
	}

	public static function cancel() {

		$count = 0;

		$data = self::list('cancelled');

		foreach ($data as $order) {

			foreach ($order['products'] as $item) {
				$store = \Flydom\Clean::firstUint($item['offer_id']);

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
						$o = new \Order\Model($id);
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

	public static function list($status, $days = 3)
	{
		// https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetFbsPostingListV3

		$data = [];

		$result = [];
		$offset = 0;
		$limit = 10;

		do {
			$new = \Ozon\Api::post('/v3/posting/fbs/list', [
				'dir'=>'asc',
				'filter'=>[
					'since'=>date('c', \Config::now() - $days*2*24*60*60),
					'to'=>date('c', \Config::now()),
					'status'=>$status
				],
				'limit'=>$limit,
				'offset'=>$offset,
			]);

			$offset+= $limit;

			if (!isset($new['result'])) {
				\Flydom\Log::add(355, \Cabinet\Model::user(), $status.' '.json_encode($new));
				break;
			}

			$found = count($new['result']['postings']);
			$result = array_merge($result, $new['result']['postings']);
		} while ($found == $limit);

		foreach ($result as $order) {
			$order['user'] = \Cabinet\Model::user();
			$data[] = $order;
		}

		return $data;

	}

	public static function pack($order) {

		// Ищем другие заказы того же номера в другом статусе
		$all = \Db::fetchAll("SELECT MIN(count) count,sku,state FROM orst WHERE mpi='".$order->getMpi()."' GROUP BY sku,state");

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

		$post = \Ozon\Api::post('/v4/posting/fbs/ship', $data);

		$success = true;

		if (!isset($post['result'])) {
			\Flydom\Log::add(375, $order->getUser(), json_encode($post).' :: '.json_encode($data));
			$success = false;

			if (is_null($post) || $post == 'null') {
				for ($i = 1; $i<=5; $i++) {

					sleep(1);
					$post = \Ozon\Api::post('/v4/posting/fbs/ship', $data);
					\Flydom\Log::add(375, $order->getUser(), 'Repeat '.$i.' :: '.json_encode($post));

					if (!(is_null($post) || $post == 'null')) { $success = true; break; }
				}
			}
		}

		return $success;
	}
}
