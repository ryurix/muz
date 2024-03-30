<?php

class Db extends \Flydom\DbMySql
{
	protected static $db;
	protected static $res;

	protected static function init() {
		if (is_null(self::$db)) {
			self::connect(\Config::DATABASE);
		}
	}

} // class Db
