<?

$head = '
<link rel="stylesheet" href="/design/libs/summernote/summernote-bs4.css">
<script type="text/javascript" src="/design/libs/summernote/summernote-bs4.min.js"></script>
<script type="text/javascript" src="/design/libs/summernote/lang/summernote-ru-RU.min.js"></script>

<link rel="stylesheet" href="example.css">
<script type="text/javascript">
	$(document).ready(function() {
		$("textarea.rich").summernote({
			height: 300,
			tabsize: 2,
			lang: "ru-RU"
		});
	});
</script>';

if (isset($block['head'])) {
	$block['head'].= $head;
} else {
	$block['head'] = $head;
}