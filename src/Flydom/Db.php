<?php

class Db extends \Flydom\Db\MySql
{
	protected static $db = [];

	protected static function init() {
		return self::connect(\Config::DATABASE);
	}

} // class Db
