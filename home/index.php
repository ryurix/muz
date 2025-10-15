<?

//	\Flydom\Alert::warning('test');
//	\Flydom\Alert::warning('Съешь же ещё этих мягких французских булочек, <a href="#">да выпей чаю</a>! Съешь же ещё этих мягких французских булочек, да выпей чаю!');

\Page::set('wide', 1);

//$config['meta'] = cache_get('description');
$config['meta'] = cache_get('muzmart-meta');

$config['gtag-event'] = array(
	'event_name'=>'page_view',
	'ecomm_pagetype'=>'home',
);