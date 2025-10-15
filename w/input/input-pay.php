<?

w('plan-pay');
w('input-line');
w('input-combo');
w('clean');

function parse_pay(&$v) {
	global $config;

	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = clean_int($v['value']);
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : '';
	}

	$def = substr($v['value'], 0, 1) == '[' || substr($v['value'], 0, 5) == 'array'
		? php_decode($v['value'])
		: array('ptype'=>(strlen($v['value']) ? 0 : 3), 0=>$v['value']);

	$config['plan-pay']['']['default'] = $def;

	w('request', $config['plan-pay']);

	$a = array();

	foreach ($config['plan-pay'] as $k=>$i) {
		if (strlen($k) && strlen($i['value'])) {
			$a[$k] = $i['value'];
		}
	}

	$v['value'] = php_encode($a);
	$v['valid'] = 1;
}

function input_pay($v) {
	global $config;

	function name_input($cols, $row) {
		$s = '<div class="col-sm-'.$cols.'">'.$row['name'].'<br>';
		$s.= input_line($row);
		$s.= '</div>';
		return $s;
	}

//	function val($f, $k) { return 'value="'.addcslashes($f[$k], '"').'"'; }

	$plan = $config['plan-pay'];
	$types = $plan['ptype']['values'];

//	$val = php_decode($v['value']);

	$s = '
<div class="alert alert-info">

<div class="form-group">
	<label>Способ оплаты</label>
	'.input_combo($plan['ptype']).'
</div>';
	$s.= "\n".'<div class="row pay-other"';
	if (!strlen($plan['other']['value'])) {
		$s.= ' style="display:none"';
	}
	$s.= '>
	<div class="form-group">
		<div class="col-sm-12">
			<label style="display:block">Реквизиты</label>
			'.input_wiki($plan['other']).'
		</div>
	</div>
</div>';
	$s.= "\n".'<div class="form-group">
'.input_checkbox($dummy = array(
	'id'=>'pay-ul',
	'label'=>'Юридическое лицо',
	'code'=>'ul',
	'value'=>$v['ul'],
)).'
</div>
<div class="row pay-ul">
	<div class="form-group">
		'.name_input(6, $plan['uname']).'
		'.name_input(6, $plan['head']).'
	</div>
</div>
<div class="row pay-ul">
	<div class="form-group">
		'.name_input(6, $plan['uadr']).'
		'.name_input(6, $plan['fadr']).'
	</div>
</div>
<div class="row pay-ul">
	<div class="form-group">
		'.name_input(6, $plan['inn']).'
		'.name_input(6, $plan['kpp']).'
	</div>
</div>
<div class="row pay-ul">
	<div class="form-group">
		'.name_input(6, $plan['okpo']).'
	</div>
</div>
<div class="form-group pay-ul">
		<label>Банковские реквизиты</label>
</div>
<div class="row pay-ul">
	<div class="form-group">
		'.name_input(6, $plan['bank']).'
		'.name_input(6, $plan['bik']).'
	</div>
</div>
<div class="row pay-ul">
	<div class="form-group">
		'.name_input(6, $plan['bkor']).'
		'.name_input(6, $plan['bras']).'
	</div>
</div>

</div>
<script>
function pay_combo() {
	var types = ["'.implode('","', array_keys($types)).'"];
	var type = document.getElementById("pay-combo").value;
	var ul = document.getElementById("pay-ul").checked;

	if (ul) {
		$(".pay-ul").show();
	} else {
		$(".pay-ul").hide();
	}

	$.each(types, function (k, v) {
		if (type == v) {
			$(".pay-" + v).show();
		} else {
			$(".pay-" + v).hide();
		}
	});
}

$(function() {
	$("#pay-ul").change(pay_combo);
	$("#pay-combo").change(pay_combo).change();
});
</script>';

	return $s;
}

?>