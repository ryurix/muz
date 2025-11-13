<?

$typ = \Price\Type::get();
\Page::breadcrumb([
	'/price2?typ='.$typ=>'Ценообразование 2'
]);

$id = \Page::arg();
if ($id) {
	$price = \Price\Form::load($id, $typ);
	\Page::name('Правило #'.$price['i']);
} else {
	$price = [
		'code'=>0,
		'grp'=>[],
		'brand'=>[],
		'vendor'=>[],
		'up'=>0,
		'count'=>2,
		'price'=>0,
		'sale'=>0,
		'info'=>'',
		'fin'=>0
	];
	\Page::name('Новое правило');
}

\Price\Form::start($price);

if (\Price\Form::valid()) {
	if (\Price\Form::send() == 'save') {
		\Price\Form::save();
	}

	if (\Price\Form::send() == 'delete') {
		\Price\Form::delete();
	}
}