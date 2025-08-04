<?

namespace View;

class OrderLog {

static function echo($order) {
	$rows = \Db::fetchAll(\Db::select('log.dt,log.type,log.info,user,user.name', 'log LEFT JOIN user ON log.user=user.i', ['log.type>=100', 'log.code'=>$order], 'ORDER BY log.dt'));

	foreach ($rows as $i) {
		$type = $i['type'];
		$info = \Type\Log::name($type);
		echo '<p>'.\Flydom\Time::dateTime($i['dt']).' '.$i['name'].': '.$info.'</p>';
	}
}

} // OrderLog