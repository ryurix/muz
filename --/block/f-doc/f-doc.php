<?

// Copyright © 2015 Elena Bakun Contacts: <floppox@gmail.com>
// License: http://opensource.org/licenses/MIT

error_reporting(0);

include_once __DIR__.'/compiler.class.php';
include_once __DIR__.'/document.class.php';
include_once __DIR__.'/docx_processor.class.php';
include_once __DIR__.'/request.class.php';
include_once __DIR__.'/xlsx_processor.class.php';


$request = new Request($args);

$document = new Document($request->template, $request->filename);

$document->process();

$matrix = new Compiler($document->search_filds(), $request->matrix);

$document->replace_simple($matrix->simple);

if(!empty($matrix->groups_map)){
	$document->build_groups($matrix->group, $matrix->groups_map);
	$matrix->expand_iteratives();
}

$document->fill_iterative($matrix->iterative); 

if($request->extention) {
	$action = 'extention';
	$extention = $request->extention;
} elseif($request->action) {
	$action = $request->action;
	$extention = false;
} else {
	$action = 'show_link';
	$extention = false;
}

$document->close($action, $extention);

?>
