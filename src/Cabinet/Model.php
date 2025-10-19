<?php

namespace Cabinet;

class Model
{
	protected static $row;

	protected static function _load($user)
	{
		if (!$user) { return null; }

		$row = \Db::fetchRow('SELECT * FROM cabinet WHERE usr='.$user);

		if (is_null($row)) { return null; }


		return \Flydom\Arrau::exclude('data', $row) + \Flydom\Arrau::decode($row['data']);
	}

	static function load($user) {
		self::$row = self::_load($user);
		return self::$row;
	}

	static function defaults() {
		return self::$row ?? [];
	}

	static function list($type = null) {
		return \Db::fetchMap(\Db::select(['usr', 'name'], 'cabinet', empty($type) ? '' : ['typ'=>$type], 'ORDER BY name'));
	}

	static function valid() { return !empty(self::$row); }
	static function user() { return self::$row['usr']; }
	static function type() { return self::$row['typ']; }

	static function key() { return self::$row['key']; }
	static function token() { return self::$row['token']; }
	static function campaign() { return self::$row['campaign']; }
	static function vat() { return self::$row['vat']; }
	static function client() { return self::$row['client']; }
	static function api() { return self::$row['api']; }
	static function warehouse() { return self::$row['warehouse']; }
	static function store() { return self::$row['store']; }
	static function auth() { return self::$row['auth']; }
}