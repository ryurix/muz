<?php

namespace Ozon;

class Price {

	static function run($args) {
		\Cabinet\Model::load($args['usr']);
		if (!\Cabinet\Model::valid()) {
			return 'Кабинет не найден: '.$args['usr'];
		}

		$rows = \Db::fetchAll('SELECT c.data,c.price,c.store,s.price2 FROM stock c LEFT JOIN store s ON c.store=s.i WHERE c.usr='.$args['usr'], 'store', false);

		$test = \Flydom\Clean::firstUint($args['test']);
		if ($test) {
			if (isset($rows[$test])) {
				$rows = [ $test => $rows[$test] ];
			} else {
				return 'Товар не найден: '.$test;
			}
		}

		$upd = [];
		foreach ($rows as $k=>$v) {
			if ($v['price2'] == 0) {
				if ($test) {
					return 'Нет оптовой цены!';
				}
				continue;
			}

			$data = \Flydom\Arrau::decode($v['data']);

			$fix = $data['deliv'] + $data['trans'] + $data['first'];
			$koef = $data['percent'] + 1.9; // $data['acquiring'];
			$margin = \Cabinet\Model::margin() * (100 + \Cabinet\Model::vat()) / 100;

			$price = round(
				($v['price2'] + $fix + \Cabinet\Model::profit()) * 100 /
				(100 - $koef - $margin)
			);

			if ($price != $v['price']) {
				$upd[$v['store']] = $price;
			}

			if ($test) {
				\Flydom\Alert::info('Оптовая цена: '.$v['price2']);
				\Flydom\Alert::info('Фиксированные расходы: '.$fix);
				\Flydom\Alert::info('Переменные расходы: '.$koef.'% '.round($price*$koef/100));
				\Flydom\Alert::info('Доход: '.$margin.'% '.round($price*$margin/100).' + '.\Cabinet\Model::profit());
				\Flydom\Alert::info('Цена: '.$price);

				if (isset($upd[$test])) {
					\Flydom\Alert::info('Старая цена: '.$v['price'].', обновление');
				}
			}
		}

		$errors = [];
		if (count($upd))
		{
			$a = [];
			foreach ($upd as $k=>$v) {
				\Db::update('stock', ['price'=>$v], [
					'store'=>$k,
					'usr'=>$args['usr']
				]);

				$a[] = [
					'offer_id'=>'М'.$k,
					'price'=>$v,
					'old_price'=>0,
					'min_price'=>max(0, ceil($v * 0.98))
				];
			}

			$chunks = array_chunk($a, 1000);

			foreach ($chunks as $a)
			{
				$post = ['prices'=>$a];
				$result = \Ozon\Api::post('/v1/product/import/prices', $post);

				if (isset($result['result'])) {
					foreach ($result['result'] as $i) {
						if (count($i['errors'])) {
							$errors[] = $i['offer_id'].': '.$i['errors'][0]['message'];
						}
					}
				} else {
					$errors[] = $result['message'];
				}

				sleep(1);
			}
		}

		\Flydom\Log::add(353, 0, implode(', ', $errors), $args['usr']);

		return 'Обновлено: '.count($upd).' цен'.(empty($errors) ? '' : ', ошибка: '.implode(', ', $errors));
	}
}