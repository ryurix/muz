<?

use \Flydom\Form\Form;

$names = [];

foreach (\Type\Doc::FILES as $k=>$v) {
	$names[$v] = \Type\Doc::NAMES[$k];
	switch ($v) {
		case 'nak_s': case 'nak_su':
		case 'nak_k': case 'nak_ku':
		case 's-f_s': case 's-f_su':
		case 'upd_t': case 'upd_tu':
			$names[$v.'-2'] = \Type\Doc::NAMES[$k].' (2 страницы)';
			$names[$v.'-3'] = \Type\Doc::NAMES[$k].' (3 страницы)';
		break;
	}
}

Form::plan([
	'code'=>['name'=>'Документ', 'type'=>'select', 'values'=>$names],
	'file'=>new \Flydom\Input\File(),
	'send'=>new \Flydom\Input\Button('', ['download'=>'Скачать', 'upload'=>'Закачать']),
]);

Form::parse();
Form::method('POST');

if (Form::get('send') == 'download') {
	$code = Form::get('code');
	if (isset($names[$code])) {
		$filename = __DIR__.'/template/'.$code.'.xlsx';
		if (is_file($filename)) {
			\Flydom\File::upload($filename);
		}
	}
}

if (Form::get('send') == 'upload') {
	$code = Form::get('code');
	$file = Form::get('file');
	if (isset($names[$code]) && is_file($file)) {
		$filename = __DIR__.'/template/'.$code.'.xlsx';
		\Flydom\File::move($file, $filename);
		\Flydom\Alert::success('Шаблон закачан! '.$names[$code]);
	}
}
