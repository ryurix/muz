<?

function import_catalog($url) {
	$code = trim($url);
	$code = substr($code, 0, 7) == 'http://' ? substr($code, 7, 32) : substr($code, 0, 32);
	$code = substr($code, 0, 8) == 'https://' ? substr($code, 8, 32) : substr($code, 0, 32);
	$pos = strpos($code, '/');
	if ($pos === FALSE) {
		\Flydom\Alert::warning('Название сайта не опознано: '.$code);
		return FALSE;
	} else {
		$code = substr($code, 0, $pos);
		if (substr($code, 0, 4) == 'www.') {
			$code = substr($code, 4);
		}
	}

	w('simple_html_dom');
	set_time_limit(\Config::TIME_LIMIT);

	return w($code, $url);
}

$brands = cache_load('brand');
$brands[0] = '[Автоматически]';
$plan = array(
	''=>array('method'=>'POST'),
	'url'=>array('name'=>'Адрес на сайте', 'type'=>'line'),
	'up'=>array('name'=>'Раздел', 'type'=>'combo2', 'width'=>500, 'values'=>w('catalog-all')),
	'brand'=>array('name'=>'Производитель', 'type'=>'combo2', 'values'=>$brands),
	'name'=>array('name'=>'Название', 'type'=>'line'),
	'vendor'=>array('name'=>'Поставщик', 'type'=>'combo2', 'width'=>500, 'values'=>cache_load('vendor')),
	'same'=>array('name'=>'Аналоги', 'type'=>'checkbox', 'default'=>1),
	'send'=>array('type'=>'button', 'count'=>2, 1=>'Проверка', 2=>'Импорт', 'class'=>array(1=>'btn-success', 2=>'btn-warning')),
);

//$plan['url']['value'] = 'http://www.ltm-music.ru/catalog/akusticheskie_komplekty/?set_filter=y&arrFilter_234_4009429771=Y&arrFilter_305_MAX=339200&arrFilter_305_MIN=9900';
//$plan['url']['value'] = 'http://www.svetomuz.ru/catalog/muzykalnye_instrumenty/gitary/akusticheskie_gitary_vestern/';
/*
$plan['url']['value'] = 'https://www.yarovit-m.ru/zvukovoe-oborudovanie/akusticheskie-sistemy/monitory-studiynye/?price_min=&price_max=&brend%5B%5D=27931&brend%5B%5D=27922';
$plan['up']['value'] = 1678;
$plan['brand']['value'] = 0;
$plan['vendor']['value'] = 0;
//*/

/*
$plan['url']['value'] = 'https://invask.ru/cat/404/115';
$plan['up']['value'] = 1678;
$plan['brand']['value'] = 0;
$plan['vendor']['value'] = 0;
//*/

/*
$_REQUEST['url'] = 'http://kramer.ru/products/collaboration/';
$_REQUEST['up'] = 1678;
$_REQUEST['brand'] = 0;
$_REQUEST['vendor'] = 1;
$_REQUEST['send1'] = 1;
//*/

w('request', $plan);

if ($plan['']['valid']) {
	$config['import'] = import_catalog($plan['url']['value']);
}

$config['plan'] = $plan;
w('lightbox.js');

?>