<?

use \Flydom\Form\Form;

$vendor = \Flydom\Cache::get('vendor');

Form::plan([
	'file'=>new \Flydom\Input\File([
		'name'=>'Файл экселя',
		'min'=>4
	]),
	'vendor'=>new \Flydom\Input\Select([
		'name'=>'Поставщик',
		'values'=>$vendor
	]),
	'art'=>new \Flydom\Input\Integer('Колонка артикула поставщика', 1),
	'code'=>new \Flydom\Input\Integer('Колонка штрихкода', 1),
	'send'=>new \Flydom\Input\Button('', ['import'=>'Импорт'])
]);
Form::method('POST');
Form::parse();

if (Form::isValid() && Form::get('send') == 'import') {

	$file = Form::get('file');
	$filename = Form::field('file')->filename();
	$ext = \Flydom\File::extension($filename);

	if ($ext == 'xls') {
		if ($xls = \Shuchkin\SimpleXLS::parse($file)) {
			$data = $xls->rows();
		} else {
			\Flydom\Alert::danger('Ошибка чтения файла: '.\Shuchkin\SimpleXLS::parseError());
		}
	} elseif ($ext == 'xlsx') {
		if ($xlsx = \Shuchkin\SimpleXLSX::parse($file)) {
			$data = $xlsx->rows();
		} else {
			\Flydom\Alert::danger('Ошибка чтения файла: '.\Shuchkin\SimpleXLSX::parseError());
		}
	} else {
		\Flydom\Alert::danger('Неизвестный тип файла, загрузите Excel.');
		$data = [];
	}

	$updated = 0;

	$artCol = Form::get('art') - 1;
	$codeCol = Form::get('code') - 1;
	$vendor = Form::get('vendor');
	foreach ($data as $row) {
		$code = \Tool\Barcode::clean($row[$codeCol] ?? '');
		if (!\Tool\Barcode::check($code)) { continue; }

		$store = \Db::fetchRow(\Db::select('store.i,store.code', 'sync LEFT JOIN store ON sync.store=store.i', ['sync.vendor'=>$vendor, 'sync.code'=>$row[$artCol]]));
		if (is_array($store)) {
			$codes = \Flydom\Arrau::decodec($store['code']);

			if (array_search($code, $codes) === false) {
				$codes[] = $code;
				\Db::update('store', ['code'=>\Flydom\Arrau::encodec($codes)], ['i'=>$store['i']]);
				$updated++;
			}
		}
	}
} elseif (Form::get('send') == 1) {
	Form::validate();
}