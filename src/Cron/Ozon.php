<?

namespace Cron;

class Ozon extends Task {

	static function ozon_query($ozon, $url, $args) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api-seller.ozon.ru'.$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Client-Id: '.$ozon['client'],
			'Api-Key: '.$ozon['api'],
			'Content-Type: application/json',
		));
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

		/*
		w('ft');
		w('clean');

		$count = 0;
		$limit = 10;

		global $config;
		foreach ($config['ozon'] as $user=>$ozon) {

			$result = [];
			$offset = 0;

			do {
				$new = self::ozon_query($ozon, '/v3/posting/fbs/unfulfilled/list', [
					'dir'=>'asc',
					'filter'=>[
						'cutoff_from'=>date('c', now() - 3*24*60*60),
						'cutoff_from'=>date('c', now()),
						'status'=>'awaiting_packaging'
					],
					'limit'=>$limit,
					'offset'=>$offset,
				]);

				$offset+= $limit;

				if (!isset($new['result'])) {
					w('log');
					logs(355, $user, json_encode($new));
					break;
				}

				$found = count($new['result']['postings']);
				$result = array_merge($result, $new['result']['postings']);
			} while ($found == $limit);

			$mark = db_get_row('SELECT mark,mark2 FROM user WHERE i='.$user);
			$mark = $mark ? ','.trim($mark['mark'].','.$mark['mark2'], ',').',' : '';

			foreach ($result as $order) {

				foreach ($order['products'] as $item) {
					$store = clean_09($item['offer_id']);

					$where = [
						'mpi="'.addslashes($order['posting_number']).'"',
						'user='.$user,
					];
					if ($store) {
						$where[] = 'store='.$store;
					}

					$exists = db_result('SELECT COUNT(*) FROM orst WHERE '.implode(' AND ', $where));
					if ($exists) { continue; }

					\Tool\Complex::insert([
						'dt'=>now(),
						'last'=>now(),
						'user'=>$user,
						'staff'=>null,
						'state'=>1,
						'cire'=>34,
						'city'=>'', // Адрес?
						'lat'=>null,
						'lon'=>null,
						'adres'=>'',
						'dost'=>'self',
						'vendor'=>0,
						'store'=>$store,
						'name'=>$item['name'],
						'price'=>$item['price'],
						'count'=>$item['quantity'],
						'money0'=>0,
						'pay'=>0,
						'money'=>0,
						'pay2'=>0,
						'money2'=>0,
						'bill'=>null,
						'sale'=>null,
						'info'=>'', // Примечание?
						'note'=>count($order['products']) > 1 ? 'парный заказ' : '',
						'docs'=>null,
						'files'=>null,
						'mark'=>$mark,
						'kkm'=>0,
						'kkm2'=>0,
						'mpi'=>$order['posting_number'],
						'mpdt'=>ft_parse($order['shipment_date'], true),
						'sku'=>clean_int($item['sku']),
					]);

					$count++;
				}
			}
		}

		return 'Создано '.$count.'.';
		*/

	}

	public static function create() {

		$count = 0;

		$data = self::list('awaiting_packaging');

		foreach ($data as $order) {

			foreach ($order['products'] as $item) {
				$store = clean_09($item['offer_id']);

				$where = [
					'mpi="'.addslashes($order['posting_number']).'"',
					'user='.$order['user'],
				];
				if ($store) {
					$where[] = 'store='.$store;
				}

				$exists = db_result('SELECT COUNT(*) FROM orst WHERE '.implode(' AND ', $where));
				if ($exists) { continue; }

				$mark = db_get_row('SELECT mark,mark2 FROM user WHERE i='.$order['user']);
				$mark = $mark ? ','.trim($mark['mark'].','.$mark['mark2'], ',').',' : '';

				\Tool\Complex::insert([
					'dt'=>now(),
					'last'=>now(),
					'user'=>$order['user'],
					'staff'=>null,
					'state'=>1,
					'cire'=>34,
					'city'=>'', // Адрес?
					'lat'=>null,
					'lon'=>null,
					'adres'=>'',
					'dost'=>'self',
					'vendor'=>0,
					'store'=>$store,
					'name'=>$item['name'],
					'price'=>$item['price'],
					'count'=>$item['quantity'],
					'money0'=>0,
					'pay'=>0,
					'money'=>0,
					'pay2'=>0,
					'money2'=>0,
					'bill'=>null,
					'sale'=>null,
					'info'=>'', // Примечание?
					'note'=>count($order['products']) > 1 ? 'парный заказ' : '',
					'docs'=>null,
					'files'=>null,
					'mark'=>$mark,
					'kkm'=>0,
					'kkm2'=>0,
					'mpi'=>$order['posting_number'],
					'mpdt'=>ft_parse($order['shipment_date'], true),
					'sku'=>clean_int($item['sku']),
				]);

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
				$store = clean_09($item['offer_id']);

				$where = [
					'mpi="'.addslashes($order['posting_number']).'"',
					'user='.$order['user'],
					'state<35',
				];
				if ($store) {
					$complex = \Flydom\Db::fetchArray('SELECT store FROM complex WHERE up='.$store);
					if (count($complex)) {
						$where[] = 'store IN ('.implode(',', $complex).')';
					} else {
						$where[] = 'store='.$store;
					}
				}

				$ids = \Flydom\Db::fetchArray('SELECT i FROM orst WHERE '.implode(' AND ', $where));

				if (count($ids)) {
					$count+= count($ids);
					\Flydom\Db::update('orst', ['state'=>35], ['i'=>$ids]);
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
						'since'=>date('c', now() - $days*24*60*60),
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
}