<?

if (\User::is('sklad')) {
	\Action::after('/basket/naklad', 'Накладная');
}

$action = 'view';

if (isset($_REQUEST['calc'])) {
	$action = 'calc';
}

if (isset($_REQUEST['next'])) {
	$action = 'next';
}

$sale = kv($_SESSION, 'sale', '');

if ($action != 'view') {
	// Загрузка корзины из REQUEST

	$basket = array();
	if (isset($_REQUEST['i']) && is_array($_REQUEST['i']) && is_array($_REQUEST['c'])) {
		foreach ($_REQUEST['i'] as $k=>$i) {
			$c = kv($_REQUEST['c'], $k, 0);
			if ($c) {
				$basket[$i] = array('c'=>$c, 'i'=>'');
			}
		}
	}
	$_SESSION['basket'] = $basket;

	if (isset($_REQUEST['sale'])) {
		$sale = addcslashes($_REQUEST['sale'], '"');
	}
}

$config['sale'] = $sale;
$_SESSION['sale'] = $sale;

if ($action == 'next') {
	\Page::redirect('/basket/next');
}

$config['gtag-event'] = array(
	'event_name'=>'page_view',
	'ecomm_pagetype'=>'cart',
);

?>