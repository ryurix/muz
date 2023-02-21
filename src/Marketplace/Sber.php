<?

namespace Marketplace;

class Sber
{
	const base_url = 'https://partner.sbermegamarket.ru/api/market/v1/orderService';

	const CONFIRM = 1;
	const PACKING = 5;

	static function query($url, $data, &$code = null) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::base_url.$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$result = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return json_decode($result, true);
	}

	static function cron_confirm($token, $shipmentId, $orderCode, $items) {
		db_insert('send_sber', [
			'dt'=>now(),
			'state'=>0,
			'typ'=>self::CONFIRM,
			'data'=> array_encode([
				'token'=>$token,
				'shipmentId'=>$shipmentId,
				'orderCode'=>$orderCode,
				'offers'=>$items,
			]),
		]);
	}

	static function cron_packing($token, $shipmentId, $orderCode, $items) {
		db_insert('send_sber', [
			'dt'=>now(),
			'state'=>0,
			'typ'=>self::PACKING,
			'data'=> array_encode([
				'token'=>$token,
				'shipmentId'=>$shipmentId,
				'orderCode'=>$orderCode,
				'offers'=>$items,
			]),
		]);
	}

	static function confirm($token, $shipmentId, $orderCode, $items) {
		return self::query('/order/confirm', [
			'data'=>[
				'token'=>$token,
				'shipments'=>[
					[
						'shipmentId'=>$shipmentId,
						'orderCode'=>$orderCode,
						'items'=>$items,
					],
				],
			],
			'meta'=>new Class {},
		], $code);
	}

	static function packing($token, $shipmentId, $orderCode, $items) {
		return self::query('/order/packing', [
			'data'=>[
				'token'=>$token,
				'shipments'=>[
					[
						'shipmentId'=>$shipmentId,
						'orderCode'=>$orderCode,
						'items'=>$items,
					],
				],
			],
			'meta'=>new Class {},
		], $code);
	}

	static function items($offers) {
		$items = [];
		foreach ($offers as $k=>$v) {
			$items[] = [
				'itemIndex' => $v,
				'offerId' => $k,
			];
		}
		return $items;
	}

	static function cron() {

		$row = db_fetch_row('SELECT * FROM send_sber WHERE state=0 ORDER BY dt LIMIT 1');

		if (!is_array($row)) {
			return '';
		}

		$data = array_decode($row['data']);

		if ($row['typ'] == self::CONFIRM) {
			$result = self::confirm($data['token'], $data['shipmentId'], $data['orderCode'], $data['offers']);
			db_update('send_sber', ['state'=>1], ['i'=>$row['i']]);
			return 'Заказ собран ('.\Flydom\Cache::array_encode($result).')';
		}
	}
} // Sber