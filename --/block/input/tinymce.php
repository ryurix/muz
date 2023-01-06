<script type="text/javascript" src="/rich/tinymce.min.js"></script>
<script type="text/javascript">
tinymce.init({
    selector: "textarea.rich",
    theme: "modern",
    skin: "pepper-grinder",
    plugins: [
        "advlist autolink lists link image charmap preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor moxiemanager"
    ],
    toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media | forecolor backcolor emoticons",
    image_advtab: true,
    convert_urls: false,
    templates: [
        {title: 'Test template 1', content: 'Test 1'},
        {title: 'Test template 2', content: 'Test 2'}
    ],
    language: 'ru'
});
</script>