<?

namespace Cron;

class Yml extends Task {

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

	public static function run($data) {

		global $config;

		if (strlen($data['form']) <= 2) {
			switch($data['form']) {
				case 0: $data['form'] = 'yandex'; break;
				case 1: $data['form'] = 'yandex+count'; break;
				case 10: $data['form'] = 'goods'; break;
				case 20: $data['form'] = 'cdek'; break;
			}
		}

		$city = $data['city'];
		w('load-speeds');
		$speeds = w('speed-yandex');

		$filename = $config['root'].'files/'.$data['filename'];
		$f = fopen($filename, 'w+');

		$site = $data['site'];

		$delivery = $data['city'] == 34 ? 400 : 300; // Доставка для Москвы 400, для остальных 300

		fwrite($f, '');

		fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>
		<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
		<yml_catalog date="'.date('Y-m-d H:i', now()-2*60*60).'">
			<shop>
				<name>'.$config['title'].'</name>
				<company>ООО "КАЙРОС"</company>
				<url>https://'.$site.'</url>
				<currencies>
					<currency id="RUR" rate="1" plus="0"/>
				</currencies>
				<categories>
		');

		$pathway = cache_load('pathway'); // $config['pathway'];
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

		if (kv($data, 'complex', 0)) {
			if ($data['form'] == 'dynatone') {
				$select = 'SELECT store.*,dyna.pic dpic,dyna.pics dpics,dyna.info dinfo,dyna.size FROM dyna,store WHERE store.i=dyna.store AND store.complex=1 AND hide<=0';
			} else {
				$select = 'SELECT store.* FROM store WHERE store.complex=1 AND hide<=0';
			}
		} else {
			$dt = now() - 30*24*60*60;
			if ($data['form'] == 'dynatone') {
				$select = 'SELECT store.*,ven.count,dyna.pic dpic,dyna.pics dpics,dyna.info dinfo,dyna.size FROM dyna,store INNER JOIN (SELECT store, SUM(count) count FROM sync WHERE dt>='.$dt.$vendor.' GROUP BY store) ven ON ven.store=store.i WHERE store.complex<1 AND store.i=dyna.store AND hide<=0';
			} else {
				$select = 'SELECT store.*,ven.count FROM store INNER JOIN (SELECT store, SUM(count) count FROM sync WHERE dt>='.$dt.$vendor.' GROUP BY store) ven ON ven.store=store.i WHERE store.complex<1 AND hide<=0';
			}
		}
		if (kv($data, 'min', 0)) { $select.= ' AND '.$data['min'].'<=ven.count'; }
		if (kv($data, 'price', 0)) { $select.= ' AND '.$data['price'].'<=price'; }
		$price2 = kv($data, 'price2', 1000000);
		if ($price2) { $select.= ' AND '.$price2.'>=price'; }

		$q = db_query($select);

		$reserve = \Tool\Reserve::get();
		$brands = cache_load('brand');
		$count = 0;
		while ($i = db_fetch($q)) {
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

			$count++;
			$brand = htmlspecialchars($brands[$i['brand']]);
			$i['count'] = max(0, $i['count'] - kv($data, 'minus', 0) - kv($reserve, $i['i'], 0));
			$available = $i['count'] ? 'true' : 'false';
			if (strlen($i['model']) && strlen($brand)) {
				$name = $brand.' '.$i['model'].' '.$i['name'];
				fwrite($f, '<offer id="'.$i['i'].'" type="vendor.model" available="'.$available.'">'."\n");
		//		fwrite($f, '<vendorCode>'.htmlspecialchars($i['model']).'</vendorCode>'."\n";
				fwrite($f, '<vendor>'.$brand.'</vendor>'."\n");

				if (strpos($data['form'], '+fullmodel') !== false) {
					fwrite($f, '<model>'.htmlspecialchars($name).'</model>'."\n");
				} elseif ($data['form'] != 'cdek') {
					fwrite($f, '<model>'.htmlspecialchars($i['model']).'</model>'."\n");
				}

				if ($data['form'] == 'goods') {
					fwrite($f, '<name>'.htmlspecialchars($name).'</name>'."\n");
				}


			} else {
				$name = trim($brand.' '.(strlen($i['model']) ? $i['model'].' ' : '').$i['name']);
				fwrite($f, '<offer id="'.$i['i'].'" available="'.$available.'">'."\n");
				if ($data['form'] != 'cdek') {
					fwrite($f, '<name>'.htmlspecialchars($name).'</name>'."\n");
				}
			}

			if ($data['form'] == 'cdek') {
				fwrite($f, '<model>'.htmlspecialchars($name).'</model>'."\n");
			}

			fwrite($f, '<url>https://'.$site.'/store/'.$i['url'].'</url>'."\n");
			fwrite($f, '<price>'.$price.'</price>'."\n");
			fwrite($f, '<currencyId>RUR</currencyId>'."\n");
			fwrite($f, '<categoryId>'.$i['up'].'</categoryId>'."\n");
			if (strlen($i['pic'])) {
				fwrite($f, '<picture>https://'.$site.$i['pic'].'</picture>'."\n");
			}
			if ($data['form'] != 'cdek') {
				fwrite($f, '<typePrefix>'.htmlspecialchars($i['name']).'</typePrefix>'."\n");
			}
			if ($data['form'] == 'dynatone') {
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
			fwrite($f, '<shop-sku>М'.$i['i'].'</shop-sku>'."\n");
			fwrite($f, '<barcode>'.self::calc_barcode($i['i']).'</barcode>'."\n");
			if ($data['form'] == 'goods') { // Гудс
				fwrite($f, '<outlets><outlet id="1" instock="'.$i['count'].'" /></outlets>'."\n");
			}
			if (strpos($data['form'], '+count') !== false) { // Яндекс + количество
				fwrite($f, '<count>'.$i['count'].'</count>'."\n");
				fwrite($f, '<manufacturer>'.$brand.'</manufacturer>'."\n");
				fwrite($f, '<country_of_origin>Китай</country_of_origin>'."\n");
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