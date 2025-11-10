<?

namespace View;

class OrderLog {

static function echo($order) {
	$rows = \Db::fetchAll(\Db::select('log.dt,log.typ,log.info,log.usr,user.name', 'log LEFT JOIN user ON log.usr=user.i', ['log.typ>=100', 'log.code'=>$order], 'ORDER BY log.dt'));

	foreach ($rows as $i) {
		$type = $i['typ'];
		$info = \Type\Log::name($type);
		echo '<p>'.\Flydom\Time::dateTime($i['dt']).' '.$i['name'].': '.$info.' '.$i['info'].'</p>';
	}
}

} // OrderLog