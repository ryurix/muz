<?

namespace View;

class OrderLog {

static function echo($order) {
	$types = [25, 27];
	$rows = \Db::fetchAll(\Db::select('log.dt,log.type,log.info,user,user.name', 'log LEFT JOIN user ON log.user=user.i', ['log.type IN ('.implode(',', $types).')', 'log.code'=>$order], 'ORDER BY log.dt'));

	$states = w('order-state');
	foreach ($rows as $i) {
		$type = $i['type'];
		$info = \Type\Log::name($type);
		if ($type == 27) {
			$info.= ' на '.$states[$i['info']] ?? '';
		}
		echo '<p>'.\Flydom\Time::dateTime($i['dt']).' '.$i['name'].': '.$info.'</p>';
	}
}

} // OrderLog