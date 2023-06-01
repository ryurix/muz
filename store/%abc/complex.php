<?

if (isset($_REQUEST['send'])) {
	$store = $_REQUEST['store'];
	$amount = $_REQUEST['amount'];
	$minus = $_REQUEST['minus'];
	$sale = $_REQUEST['sale'];

	\Flydom\Db::query('DELETE FROM complex WHERE up='.$row['i']);

	foreach ($store as $k=>$i) {
		$i = \Flydom\Clean::int($i);
		if ($i && \Flydom\Db::result('SELECT COUNT(*) FROM store WHERE i='.$i)) {
			\Flydom\Db::insert('complex', [
				'up'=>$row['i'],
				'store'=>\Flydom\Clean::int(kv($store, $k, 0)),
				'amount'=>\Flydom\Clean::int(kv($amount, $k, 0)),
				'minus'=>\Flydom\Clean::int(kv($minus, $k, 0)),
				'sale'=>\Flydom\Clean::int(kv($sale, $k, 0)),
			]);
		}
	}
}