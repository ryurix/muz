<?

w('autoload');

$typ = \Type\Price::get();
$list = \Type\Price::names();

$config['name'] = 'Ценообразование (' . \Type\Price::name($typ) . ')';

$links = [];
foreach ($list as $k=>$v) {
	$links[] = [
		'action'=>$v,
		'href'=>'/prices?typ='.$k
	];
}

$config['action'] = [
	['action'=>'планировщик', 'href'=>'/prices/plan'],
	['action'=>'цена', 'href'=>'#', '/'=>$links],
	['action'=>'добавить правило', 'href'=>'/prices/0?typ='.$typ],
];

$plan = array(
	''=>array('method'=>'POST'),
	'send'=>array('type'=>'button', 'count'=>1,
		1=>'Пересчитать цены в каталоге согласно правилам',
		'class'=>[1=>'btn-warning']),
);
w('request', $plan);

$config['plan'] = $plan;