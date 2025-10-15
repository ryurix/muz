<?

if (isset($_SESSION['basket']) && count($_SESSION['basket'])) {

//	refile('index.php', 'basket');

	w('basket');
	$basket = basket_calc($_SESSION['basket'], kv($config, 'sale', ''));

	$rows = array();
	$rows[] = array('Артикул', 'Наименование', 'Цена', 'Количество');
	$total = 0;
	$totalc = 0;
	foreach ($basket as $i) {
		$total+= $i['count']*$i['price1'];
		$totalc+= $i['count'];
		$rows[] = array(
			'М'.$i['store'],
			$i['name'],
			number_format($i['price1'], 0, '.', ' '),
			$i['count'],
		);
	}
	$rows[] = array('', 'Итого', number_format($total, 0, '.', ' '), $totalc);

//$fp = @fopen($yourfile, 'rb');

if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
{
	header('Content-Type: "application/octet-stream"');
	header('Content-Disposition: attachment; filename="basket.csv"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header("Content-Transfer-Encoding: binary");
	header('Pragma: public');
//	header("Content-Length: ".filesize($yourfile));
}
else
{
	header('Content-Type: "application/octet-stream"');
	header('Content-Disposition: attachment; filename="basket.csv"');
	header("Content-Transfer-Encoding: binary");
	header('Expires: 0');
	header('Pragma: no-cache');
//	header("Content-Length: ".filesize($yourfile));
}

//$f = fopen(\Config::ROOT.'/files/basket.csv', 'w+');

$f = fopen('php://output', 'w');

foreach ($rows as $j) {
	foreach (array_keys($j) as $i) {
		$s = html_entity_decode($j[$i]);
		$s = iconv('UTF-8', 'CP1251//IGNORE', $s);
		$j[$i] = $s;
	}
	fputcsv($f, $j, ';');
}

//fclose($f);

exit;
//fpassthru($fp);
//fclose($fp);

} else {
	\Flydom\Alert::warning('Корзина пуста!');
	\Page::redirect('/basket');
}

?>