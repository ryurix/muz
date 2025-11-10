<?php

class Page extends \Flydom\Core\Page {

	protected static $city;
	protected static $word;
	static function site($value = null) {
		static $site;
		if (!is_null($value)) {
			$site = $value;
			static::$word = \Flydom\Cache::get('word')[$site] ?? [];
			static::$city = \Flydom\Memcached::fetchRow('SELECT head,metrics,metrico,robots FROM city WHERE i='.$site);
		}
		return $site;
	}

	static function cityHead() { return static::$city['head']; }
	static function cityMetrics() { return static::$city['metrics']; }
	static function cityMetrico() { return static::$city['metrico']; }
	static function cityRobots() { return static::$city['robots']; }

	static function dict($text) {
		return empty($text) ? '' : str_replace(array_keys(static::$word), array_values(static::$word), $text);
	}

	static function domain($value = null) {
		if (\Config::DEBUG) {
			return \Config::DOMAIN;
		}
		static $domain;
		if (!is_null($value)) {
			$domain = $value;
		}
		return $domain;
	}

} // class Page