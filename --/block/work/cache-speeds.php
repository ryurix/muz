<?

$speeds = array();
$q = db_query('SELECT * FROM speeds');
while ($i = db_fetch($q)) {
	if (isset($speeds[$i['vendor']])) {
		$speeds[$i['vendor']][$i['cire']] = $i['speed'];
	} else {
		$speeds[$i['vendor']] = array($i['cire']=>$i['speed']);
	}
}
cache_save('speeds', $speeds);

//	* * *

db_query('DELETE FROM speed2');

$vendor = cache_load('vendor');

w('load-speeds');
$city = cache_load('city');

foreach ($vendor as $k=>$v) {
	foreach ($city as $i=>$j) {
		db_insert('speed2', array(
			'vendor'=>$k,
			'cire'=>$i,
			'speed'=>get_speed_i($k, $i),
		));
	}
}

?>