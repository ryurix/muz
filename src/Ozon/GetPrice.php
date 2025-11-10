<?php

namespace Ozon;

class GetPrice {

	static function run($data) {
		\Cabinet\Model::load($data['usr']);
		if (!\Cabinet\Model::valid()) {
			return 'Кабинет не найден: '.$data['usr'];
		}

		set_time_limit(0);

		$rows = \Db::fetchList('SELECT store FROM stock WHERE usr='.\Cabinet\Model::user());
		$now = \Config::now();
		$prices = self::getPrices();

		$inserted = 0;
		$updated = 0;
		foreach ($prices as $k=>$v)
		{
			$row = [
				'code'=>$v['code'],
				'dt'=>$now,
				'price'=>$v['price'],
				'data'=>\Flydom\Arrau::encode(\Flydom\Arrau::exclude(['code', 'price'], $v))
			];

			if (in_array($k, $rows)) {
				\Db::update('stock', $row, [
					'store'=>$k,
					'usr'=>\Cabinet\Model::user()
				]);
				$updated++;
			} else {
				$row['store'] = $k;
				$row['usr'] = \Cabinet\Model::user();
				$row['stock'] = 0;
				\Db::insert('stock', $row);
				$inserted++;
			}
		}

		$deleted = \Db::delete('stock', [
			'dt<'.$now,
			'usr'=>\Cabinet\Model::user()
		]);

		$s = 'Обновлено цен товаров: '.$updated;
		if ($inserted) { $s.= ', добавлено '.$inserted; }
		if ($deleted) { $s.= ', удалено: '.$deleted; }

		return $s;
	}

	static function getPrices()
	{
		$items = [];
		$post = ['filter'=>['visibility'=>'ALL'], 'limit'=>1000, 'cursor'=>''];

		do {
			$json = \Ozon\Api::post('/v5/product/info/prices', $post);

			if (!is_array($json) || !isset($json['items'])) {
				\Flydom\Log::add(352, 1, $json, \Cabinet\Model::user());
				break;
			}
			$chunk = [];
			foreach ($json['items'] as $i) {
				$id = \Flydom\Clean::firstUint($i['offer_id']);
				if ($id) {
					$chunk[$id] = [
						'code'=>$i['product_id'],
						'price'=>$i['price']['price'],
						'percent'=>$i['commissions']['sales_percent_fbs'],
						'deliv'=>$i['commissions']['fbs_deliv_to_customer_amount'],
						'trans'=>$i['commissions']['fbs_direct_flow_trans_max_amount'],
						'first'=>$i['commissions']['fbs_first_mile_max_amount'],
						'acquiring'=>$i['acquiring'],

					];
				} else {
					\Flydom\Log::add(352, 2, 'Неправильный артикул: '.$i['offer_id'], \Cabinet\Model::user());
				}
			}

			$items+= $chunk;
			$post['cursor'] = $json['cursor'];
		} while (count($chunk));

		return $items;
	}

/*
	static function getPids()
	{
		$items = [];
		$post = ['filter'=>['visibility'=>'ALL'], 'limit'=>1000, 'last_id'=>''];

		do {
			$json = \Ozon\Api::post('/v3/product/list', $post);

			if (!isset($json['result'])) {
				\Flydom\Log::add(355, 2, \Flydom\Arrau::encode($json), \Cabinet\Model::user());
				break;
			}
			$chunk = [];
			foreach ($json['result']['items'] as $i) {
				$id = \Flydom\Clean::firstUint($i['offer_id']);
				if ($id) {
					$chunk[$id] = $i['product_id'];
				} else {
					\Flydom\Log::add(355, 4, 'Неправильный артикул: '.$i['offer_id'], \Cabinet\Model::user());
				}
			}

			$items+= $chunk;
			$post['last_id'] = $json['result']['last_id'];
		} while (count($chunk) == $post['limit']);

		return $items;
	}
*/
}