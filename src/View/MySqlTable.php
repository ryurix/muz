<?

namespace View;

class MySqlTable extends \Flydom\Table\Sql {

	protected const FROM = 'information_schema.processlist';
	protected const COLUMNS = [
		'time'=>['name'=>'Time', 'field'=>'time'],
		'state'=>['name'=>'State', 'field'=>'state'],
		'db'=>['name'=>'DB', 'field'=>'db'],
		'info'=>['name'=>'Query', 'field'=>'info'],
	];

	protected static function where() {
		return ' WHERE command="Query"';
	}

}