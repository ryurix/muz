<?php

namespace Ozon;

class GetStock {

	static function run($data)
	{
		\Cabinet\Model::load($data['usr']);
		if (!\Cabinet\Model::valid()) {
			return 'Кабинет не найден: '.$data['usr'];
		}

		set_time_limit(\Config::TIME_LIMIT);

		$rows = \Db::fetchMap('SELECT store,stock FROM stock WHERE usr='.\Cabinet\Model::user());
		$now = \Config::now();
		$stock = self::getStock();

		$updated = 0;
		foreach ($rows as $k=>$v) {

			$sum = isset($stock[$k]) ? $stock[$k]['present'] + $stock[$k]['reserved'] : 0;

			if ($sum != $v) {
				$updated++;
				\Db::update('stock', [
					'dt_stock'=>$now,
					'stock'=>$sum,
				], [
					'store'=>$k,
					'usr'=>\Cabinet\Model::user()
				]);
			}
		}

		return 'Получено '.count($stock).', обновлено наличие: '.$updated.' из '.count($rows);
	}

	static function getStock()
	{
		$items = [];
		$post = ['filter'=>['visibility'=>'ALL'], 'limit'=>1000, 'cursor'=>''];

		do {
			$json = \Ozon\Api::post('/v4/product/info/stocks', $post);

			if (!is_array($json) || !isset($json['items'])) {
				\Flydom\Log::add(354, 3, $json, \Cabinet\Model::user());
				break;
			}
			$chunk = [];
			foreach ($json['items'] as $i) {
				$id = \Flydom\Clean::firstUint($i['offer_id']);
				if ($id) {
					$present = 0;
					$reserved = 0;
					foreach ($i['stocks'] as $stock) {
						$present+= $stock['present'];
						$reserved+= $stock['reserved'];
					}
					$chunk[$id] = [
						'present'=>$present,
						'reserved'=>$reserved
					];
				} else {
					\Flydom\Log::add(354, 4, 'Неправильный артикул: '.$i['offer_id'], \Cabinet\Model::user());
				}
			}

			$items+= $chunk;
			$post['cursor'] = $json['cursor'];
		} while (count($chunk));

		return $items;
	}
}