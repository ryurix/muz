<?

w('autoload');

$typ = \Type\Price::get();
$list = \Type\Price::names();

\Page::name('Ценообразование (' . \Type\Price::name($typ) . ')');

$links = [];
foreach ($list as $k=>$v) {
	$links[] = [
		'name'=>$v,
		'href'=>'/prices?typ='.$k
	];
}


\Action::before('/prices/0?typ='.$typ, '+ правило');
\Action::before('#', 'цена', 'price');
foreach ($links as $i) {
	\Action::before($i['href'], $i['name'], 'price');
}
\Action::before('/prices/plan', 'планировщик');


$plan = array(
	''=>array('method'=>'POST'),
	'send'=>array('type'=>'button', 'count'=>1,
		1=>'Пересчитать цены в каталоге согласно правилам',
		'class'=>[1=>'btn-warning']),
);
w('request', $plan);

$config['plan'] = $plan;