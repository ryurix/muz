<script type="text/javascript" src="/rich/ckeditor.js"></script>
<script type="text/javascript">
/*
if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
    CKEDITOR.tools.enableHtml5Elements( document );
*/
// The trick to keep the editor in the sample quite small
// unless user specified own height.
//CKEDITOR.config.height = 150;
//CKEDITOR.config.width = 'auto';
function loadrich() {
	CKEDITOR.replaceAll('rich', {
		removeButtons: 'Save',
		filebrowserBrowseUrl: '/rich/files/ckfinder.html',
		filebrowserUploadUrl: '/rich/files/core/connector/php/connector.php?command=QuickUpload&typ=Files'
	});

	CKEDITOR.inlineAll('richi', {
		removeButtons: 'Save',
		filebrowserBrowseUrl: '/rich/files/ckfinder.html',
		filebrowserUploadUrl: '/rich/files/core/connector/php/connector.php?command=QuickUpload&typ=Files'
	});
}
window.onload = loadrich;
</script>