<?

w('clean');
set_time_limit(0);

//$domain = $config['domain'];
$domain = 'muzmart.com';

function google_merchant_callback($buffer) {
	global $config;
	static $f = 0;
	if (!$f) {
		$f = fopen($config['root'].'files/google-merchant.xml', 'w+');
	}
	fwrite($f, $buffer);
}

ob_start('google_merchant_callback');

echo '<?xml version="1.0"?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
<title>MuzMart -- эксперты в области профессионального звука и света!</title>
<link>https://'.$domain.'</link>
<description>Комплексные поставки музыкальных инструментов, а так же профессионального светового звукового и видео оборудования в любую точку России</description>';

$pathway = cache_load('pathway');

function get_product_type($i, $pathway) {
	$node = $pathway[$i];
	$up = $node['up'];

	if ($up) {
		return get_product_type($up, $pathway).' &gt; '.$node['name'];
	} else {
		return $node['name'];
	}
}

$google = array();
$q = db_query('SELECT i,google FROM catalog');
while ($i = db_fetch($q)) {
	$google[$i['i']] = $i['google'];
}
db_close($q);

$brands = cache_load('brand');
$q = db_query('SELECT i,url,up,name,brand,model,short,icon,pic,price FROM store WHERE hide<=0 AND price>=500 AND count>0');
$count = 0;
while ($i = db_fetch($q)) {
	if (!isset($pathway[$i['up']]) || !strlen($i['name'])) {
		continue;
	}
	$count++;

	if (!isset($brands[$i['brand']])) { continue; }
	$brand = htmlspecialchars($brands[$i['brand']]);

	if (strlen($i['model']) && strlen($brand)) {
		$title = $brand.' '.htmlspecialchars($i['model'].' '.$i['name']);
	} else {
		$title = $brand.' '.htmlspecialchars($i['name']);
	}

	$product_type = get_product_type($i['up'], $pathway);

	$name = $i['name'];
	if (strlen($i['model']) > 0) {
		$name = $brands[$i['brand']].' '.$i['model'].' '.$name;
	}

	echo '

<item>
<title>'.mb_substr($title, 0, 149, 'UTF-8').'</title>
<link>https://'.$domain.'/store/'.$i['url'].'</link>
<description>'.htmlspecialchars($i['short']).'</description>
<g:google_product_category>'.$google[$i['up']].'</g:google_product_category>
<g:product_type>'.$product_type.'</g:product_type>
<g:id>'.$i['i'].'</g:id>
<g:image_link>https://'.$domain.$i['icon'].'</g:image_link>
<g:condition>new</g:condition>
<g:availability>preorder</g:availability>
<g:brand>'.$brand.'</g:brand>
<g:price>'.$i['price'].' RUB</g:price>
<g:shipping>
	<g:country>RU</g:country>
	<g:service>Standard</g:service>
	<g:price>300 RUB</g:price>
</g:shipping>
</item>';

// цена доставки -- 300 рублей

}

echo '
</channel>
</rss>';

ob_end_flush();

$status = cache_load('status');
$status['google-merchant'] = array(
	'dt'=>now(),
	'count'=>$count,
);
cache_save('status', $status); 

?>