<?

namespace Cron;

class Sitemap {
	static function run($args) {
		$index = cache_get('sitemap-index', 0);
		$index++;
		cache_set('sitemap-index', $index);

		$sites = cache_load('site');

		$key = $index % count($sites);
		$keys = array_keys($sites);
		$site = $sites[$keys[$key]];
		$suffix = '_'.trim(substr($site, 0, 4), '.');

		if ($keys[$key] == 34) {
			self::create();
		} else {
			self::create(['site'=>$site, 'suffix'=>$suffix]);
		}

		return $site;
	}

	static function create($args = null) {
		global $config;

		if (is_array($args)) {
			$site = 'https://'.$args['site'].'/';
			$suffix = $args['suffix'];
		} else {
			$sites = cache_load('site');
			$site = 'https://'.$sites[34].'/';
			$suffix = '';
		}

		w('clean');

		$f = fopen(\Config::ROOT.'files/sitemap'.$suffix.'.xml', 'w+');

		fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>'."\n"
		.'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");

		self::echo_url($f, rtrim($site, '/'), \Config::now());
		self::echo_url($f, $site.'catalog', 0, 0, 1);

		$catalog = cache_load('catalog');
		$pathway = cache_load('pathway');

		self::echo_catalog($f, $site, $catalog['/'], $pathway, 1);

		/*
		$q = db_query('SELECT * FROM catalog WHERE hide=0');
		while ($i = db_fetch($q)) {
			echo_url($f, $site.'catalog/'.$i['i'].'-'.str2url($i['name']), $i['dt'], $priority);
		}
		db_close($q);
		*/

		$brand = cache_load('brand');

		$ch = cache_load('children');
		$q = db_query('SELECT * FROM store WHERE hide=0 AND up IN ('.implode(',', $ch[0]).')');
		while ($i = db_fetch($q)) {
			self::echo_url($f, $site.'store/'.$i['url'], $i['dt']);
		}
		db_close($q);


		$q = db_query('SELECT * FROM menu WHERE hide=0');
		while ($i = db_fetch($q)) {
			self::echo_url($f, $site.substr($i['code'], 1), $i['dt']);
		}
		db_close($q);

		$q = db_query('SELECT * FROM article WHERE hide=0');
		while ($i = db_fetch($q)) {
			self::echo_url($f, $site.'article/'.$i['url'], $i['dt']);
		}
		db_close($q);

		// Подкатегории упразднили, вместо них фильтры
		/*
		$q = db_query('SELECT catalog.i,catalog.dt,subcat.code,catalog.name2 FROM subcat LEFT JOIN catalog ON subcat.up=catalog.i');
		while ($i = db_fetch($q)) {
			$dt = $i['dt'];
			$mindt = \Config::now() - 24*60*60 * 31;
			if ($dt < $mindt) { $dt = 0; }

			$url = $site.'catalog/'.$i['i'].'-'.str2url($i['name2']).'/'.$i['code'];
			echo_url($f, $url, $dt);
		}
		db_close($q);
		*/

		db_query('UPDATE catalog SET dt='.\Config::now().' WHERE dt IS NULL');
		db_query('UPDATE menu SET dt='.\Config::now().' WHERE dt IS NULL');

		fwrite($f, '</urlset>');
		fclose($f);



		/* sitemap2.xml */

		$ch = cache_load('children');
		$total = db_result('SELECT COUNT(*) FROM store WHERE hide=0 AND up IN ('.implode(',', $ch[0]).')');
		$limit = 27777;
		$pages = ceil($total/$limit);

		for ($page = 2; $page <= ($pages + 1); $page++) {

			$f = fopen(\Config::ROOT.'files/sitemap'.$page.$suffix.'.xml', 'w+');
			//print_pre(\Config::ROOT);

			fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>
			<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
					xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n");


			$q = db_query('SELECT * FROM store WHERE hide=0 AND up IN ('.implode(',', $ch[0]).') LIMIT '.(($page-2)*$limit).','.$limit);
			while ($i = db_fetch($q)) {
				$name = $i['name'];
				if (isset($brand[$i['brand']]) && strlen($brand[$i['brand']])) {
					$name.= ' '.$brand[$i['brand']];
				}
				if (strlen($i['model'])) {
					$name.= ' '.$i['model'];
				}
				$name = htmlspecialchars($name);

				$images = array();
				if (strlen($i['pic'])) {
					$images[] = $site.substr($i['pic'], 1);
				}

				if (strlen($i['pics']) > 7) {
					$pics = php_decode($i['pics']);
					foreach ($pics as $p) {
						$images[] = $site.substr($p['href'], 1);
					}
				}

				if (count($images)) {
					self::echo_images($f, $site.'store/'.$i['url'], $images, $name);
				}
			}
			db_close($q);


			fwrite($f, '</urlset>');
			fclose($f);

		}
	}

	static protected function echo_images($f, $loc, $images, $title) {
		fwrite($f, '<url>'."\n".'<loc>'.$loc.'</loc>'."\n");
		foreach ($images as $i) {
			fwrite($f, '<image:image><image:loc>'.$i.'</image:loc><image:title>'.$title.'</image:title></image:image>'."\n");
			break; // only first
		}
		fwrite($f, '</url>'."\n");
	}

	static protected function echo_url($f, $loc, $dt = 0, $freq = 'monthly', $level = 0) {
		$mindt = \Config::now() - 24*60*60 * 31;
		if ($dt < $mindt) { $dt = 0; }

		fwrite($f, '<url>'."\n".'<loc>'.$loc.'</loc>'."\n"
	.($dt ? '<lastmod>'.date("Y-m-d", $dt).'</lastmod>'."\n" : '')
	.($dt ? '<changefreq>'.$freq.'</changefreq>'."\n" : '')
	.($level > 0 ? '<priority>'.$level.'</priority>'."\n" : '')
	.'</url>'."\n");
		// date("Y-m-d\TH:i:s", $dt)
	}

	static protected function echo_catalog($f, $site, $cat, $path, $level) {
		foreach ($cat as $k=>$v) {
			self::echo_url($f, $site.'catalog/'.$path[$k]['url'], 0, 0, $level);
			if (isset($v['/'])) {
				self::echo_catalog($f, $site, $v['/'], $path, $level - 0.2);
			}
		}
	}
}