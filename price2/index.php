<?

w('autoload');

$typ = \Price\Type::get();
$list = \Price\Type::names();

\Page::name('Ценообразование (' . \Price\Type::name($typ) . ')');

$links = [];
foreach ($list as $k=>$v) {
	$links[] = [
		'name'=>$v,
		'href'=>'/price2?typ='.$k
	];
}

\Action::before('/price2/plan', 'планировщик');
\Action::before('#', 'цена', 'price');
foreach ($links as $i) {
	\Action::before($i['href'], $i['name'], 'price');
}
\Action::before('/price2/0?typ='.$typ, 'добавить правило');

$start = array(
	''=>array('method'=>'POST'),
	'send'=>array('type'=>'button', 'count'=>1,
		1=>'Пересчитать цены в каталоге согласно правилам',
		'class'=>[1=>'btn-warning']),
);
w('request', $start);