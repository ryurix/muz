<?

$script = '<script src="/design/libs/chosen/chosen.js"></script>'."\n";
$script.= '<link href="/design/libs/chosen/chosen.css" rel="stylesheet" type="text/css">';
if (isset($block['head'])) {
	$block['head'].= "\n".$script;
} else {
	$block['head'] = $script;
}

?>