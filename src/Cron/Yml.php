<?

namespace Cron;

class Yml {

	const YANDEX = 0;
	const YANDEX_COUNT = 1;
	const YANDEX_COUNT_FULL = 2;
	const GOODS = 10;
	const CDEK = 20;
	const DYNATONE = 30;

	static function calc_barcode($code) {
		$a = str_pad($code, 7, 0, STR_PAD_LEFT);
		$sum1 = ($a[0]+$a[2]+$a[4]+$a[6])*3;
		$sum2 = $a[1]+$a[3]+$a[5];
		$check = (10 - ($sum1 + $sum2)%10)%10;
		return $a.$check;
	}

	static function yandex_catalog($f, $catalog, $pathway, &$list) {
		$i = $catalog['i'];
		$list[] = $i;
		fwrite($f, '<category id="'.$i.'"');
		if ($catalog['up'] > 0) {
			fwrite($f, ' parentId="'.$catalog['up'].'"');
		}
		fwrite($f, '>'.htmlspecialchars($pathway[$i]['name'])."</category>\n");
		if (isset($catalog['/'])) {
			foreach ($catalog['/'] as $i) {
				self::yandex_catalog($f, $i, $pathway, $list);
			}
		}
	}

	public static function run($data)
	{
		$city = $data['city'];
		w('load-speeds');
		$speeds = w('speed-yandex');
		$filename = \Config::ROOT.'files/'.$data['filename'];
		$f = fopen($filename, 'w+');

		$site = $data['site'];

		$delivery = $data['city'] == 34 ? 400 : 300; // Доставка для Москвы 400, для остальных 300

		fwrite($f, '');

		fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>
		<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
		<yml_catalog date="'.date('Y-m-d\TH:i+03:00', \Config::now()-2*60*60).'">
			<shop>
				<name>'.\Config::TITLE.'</name>
				<company>ООО "КАЙРОС"</company>
				<url>https://'.$site.'</url>
				<currencies>
					<currency id="RUR" rate="1" plus="0"/>
				</currencies>
				<categories>
		');

		$pathway = cache_load('pathway');
		$andNotHidden = ' AND up IN ('.implode(',', array_keys($pathway)).')';
		$list = array();
		$catalog = cache_load('catalog');
		foreach ($catalog['/'] as $i) {
			self::yandex_catalog($f, $i, $pathway, $list);
		}

		// <local_delivery_cost>300</local_delivery_cost>

		fwrite($f, '
				</categories>
				<delivery-options>
					<option cost="'.$delivery.'" days="1-3"/>
				</delivery-options>
				<offers>
		');

		w('clean');

		if (is_array($data['vendor']) && count($data['vendor'])) {
			$vendor = ' AND vendor IN ('.implode(',', $data['vendor']).')';
		} else {
			$vendor = '';
		}

		$dt = \Config::now() - 30*24*60*60;
		if ($data['form'] == self::DYNATONE) {
			$select = 'SELECT store.*,ven.count,dyna.pic dpic,dyna.pics dpics,dyna.info dinfo,dyna.size FROM dyna,store INNER JOIN (SELECT store, SUM(count) count FROM sync WHERE dt>='.$dt.$vendor.' GROUP BY store) ven ON ven.store=store.i WHERE store.i=dyna.store AND hide<=0'.$andNotHidden;
		} else {
			$select = 'SELECT store.*,ven.count FROM store INNER JOIN (SELECT store, SUM(count) count FROM sync WHERE dt>='.$dt.$vendor.' GROUP BY store) ven ON ven.store=store.i WHERE hide<=0'.$andNotHidden;
		}

		if (kv($data, 'min', 0)) { $select.= ' AND '.$data['min'].'<=ven.count'; }
		if (kv($data, 'price', 0)) { $select.= ' AND '.$data['price'].'<=price'; }
		$price2 = kv($data, 'price2', 1000000);
		if ($price2) { $select.= ' AND '.$price2.'>=price'; }

		$rows = \Flydom\Memcached::fetchAll($select);

		$reserve = \Tool\Reserve::get();
		$brands = cache_load('brand');
		$count = 0;
		foreach ($rows as $i) {
			if (!isset($pathway[$i['up']]) || !strlen($i['name']) || !strlen($i['pic'])) {
				continue;
			}

			if (kv($data, 'type', 0) > 0) {
				$decoded = \Cron\Prices::decode($i['prices']);
				if (isset($decoded[$data['type'] - 1])) {
					$price = $decoded[$data['type'] - 1];
					if (!strlen($price)) {
						$price = 0;
					}
				} else {
					continue;
				}
			} else {
				$price = $i['price'];
			}

			if (!\Flydom\Clean::is_int($price)) {
				\Flydom\Log::add(\Flydom\Type\Log::ERROR, $i['i'], 'Yml price: '.$price);
				$price = \Flydom\Clean::uint($price);
			}

			$count++;
			$brand = htmlspecialchars($brands[$i['brand']]);
			$i['count'] = max(0, $i['count'] - kv($data, 'minus', 0) - kv($reserve, $i['i'], 0));
			$available = $i['count'] ? 'true' : 'false';
			$offer_id = $data['form'] == self::GOODS ? $i['i'] : 'М'.$i['i'];
			if (strlen($i['model']) && strlen($brand)) {
				$name = $brand.' '.$i['model'].' '.$i['name'];

				if ($data['form'] != self::YANDEX_COUNT) {
					fwrite($f, '<offer id="'.$offer_id.'" type="vendor.model" available="'.$available.'">'."\n");
				} else {
					fwrite($f, '<offer id="'.$offer_id.'" available="'.$available.'">'."\n");
				}
		//		fwrite($f, '<vendorCode>'.htmlspecialchars($i['model']).'</vendorCode>'."\n";
				fwrite($f, '<vendor>'.$brand.'</vendor>'."\n");

				if ($data['form'] == self::YANDEX_COUNT_FULL) {
					fwrite($f, '<model>'.htmlspecialchars($name).'</model>'."\n");
				} elseif ($data['form'] != self::CDEK) {
					fwrite($f, '<model>'.htmlspecialchars($i['model']).'</model>'."\n");
				}

				if ($data['form'] == self::GOODS) {
					fwrite($f, '<name>'.htmlspecialchars($name).'</name>'."\n");
				}

			} else {
				$name = trim($brand.' '.(strlen($i['model']) ? $i['model'].' ' : '').$i['name']);
				fwrite($f, '<offer id="'.$offer_id.'" available="'.$available.'">'."\n");
				if ($data['form'] != self::CDEK) {
					fwrite($f, '<name>'.htmlspecialchars($name).'</name>'."\n");
				}
			}

			if ($data['form'] == self::CDEK) {
				fwrite($f, '<model>'.htmlspecialchars($name).'</model>'."\n");
			}

			fwrite($f, '<url>https://'.$site.'/store/'.$i['url'].'</url>'."\n");
			fwrite($f, '<price>'.$price.'</price>'."\n");
			fwrite($f, '<oldprice>'.round($price*1.18).'</oldprice>'."\n");
			fwrite($f, '<currencyId>RUR</currencyId>'."\n");
			fwrite($f, '<categoryId>'.$i['up'].'</categoryId>'."\n");
			if (strlen($i['pic'])) {
				fwrite($f, '<picture>https://'.$site.$i['pic'].'</picture>'."\n");
			}
			if ($data['form'] != self::CDEK) {
				fwrite($f, '<typePrefix>'.htmlspecialchars($i['name']).'</typePrefix>'."\n");
			}
			if ($data['form'] == self::DYNATONE) {
				fwrite($f, '<description>'.htmlspecialchars(html_entity_decode($i['dinfo'])).'</description>'."\n");
				fwrite($f, '<pic>https://'.$site.$i['dpic'].'</pic>'."\n");
				$pics = array_decode($i['dpics']);
				$hrefs = array();
				foreach ($pics as $pic) {
					$hrefs[] = 'https://'.$site.$pic;
				}
				fwrite($f, '<pics>'.implode(';', $hrefs).'</pics>'."\n");
				$size = array_decode($i['size']);
				if (count($size)) {
					$weight = array_shift($size);
					fwrite($f, '<weight>'.$weight.'</weight>'."\n");
					fwrite($f, '<box>'.implode('/', $size).'</box>'."\n");
				}
			} else {
				fwrite($f, '<description>'.htmlspecialchars($i['short']).'</description>'."\n");
			}
			fwrite($f, '<sales_notes>'.($i['price'] < 10000 ? 'Без предоплаты' : 'Предоплата').'</sales_notes>'."\n");
			fwrite($f, '<pickup>true</pickup>'."\n");
			fwrite($f, '<delivery>true</delivery>'."\n");

			fwrite($f, '<vat>NO_VAT</vat>'."\n");
			if ($data['form'] == self::GOODS) {
				fwrite($f, '<shop-sku>М'.$i['i'].'</shop-sku>'."\n"); // shop-sku устарел для яндекса
			}
			fwrite($f, '<barcode>'.self::calc_barcode($i['i']).'</barcode>'."\n");
			if ($data['form'] == self::GOODS) { // Гудс
				fwrite($f, '<outlets><outlet id="1" instock="'.$i['count'].'" /></outlets>'."\n");
			}
			if ($data['form'] == self::YANDEX_COUNT || $data['form'] == self::YANDEX_COUNT_FULL) { // Яндекс + количество
				fwrite($f, '<count>'.$i['count'].'</count>'."\n");
				fwrite($f, '<manufacturer>'.$brand.'</manufacturer>'."\n");
				fwrite($f, '<country_of_origin>Китай</country_of_origin>'."\n");
			}
			if ($data['form'] == self::GOODS) {
				fwrite($f, '<count>'.$i['count'].'</count>'."\n");
			}

			$speed = get_speed_i($i['vendor'], $city, $i['count']);
			fwrite($f, '<delivery-options><option cost="'.$delivery.'" days="'.$speeds[$speed].'" /></delivery-options>'."\n");
			fwrite($f, '<manufacturer_warranty>true</manufacturer_warranty>'."\n");
			fwrite($f, "</offer>\n");
		}

		fwrite($f, '
				</offers>
			</shop>
		</yml_catalog>');

		fclose($f);

		return '<a href="/files/'.$data['filename'].'">'.$data['filename'].'</a> ('.$count.')';
	}
}