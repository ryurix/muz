<?

function cheap_button() {
	echo '<a href="javascript:void(0)" class="product_discount" data-toggle="modal" data-target="#modal_discont">Как получить скидку?</a>';
}

function cheap_dialog() {
	$info = db_result('SELECT info FROM block WHERE code="cheap"');
	echo '
<div class="modal fade" id="modal_discont" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg " role="document">
    <div class="modal-content">
		<div class="modal-body">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <p class="product_discount">Как получить скидку?</p>
'.\Page::dict($info).'
			</div>
		</div>
	</div>
</div>';
}

?>