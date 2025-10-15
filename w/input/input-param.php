<?

w('clean');

function parse_param(&$r) {
	$r['valid'] = 1;

	$code = $r['code'];

	if (isset($_REQUEST[$code.'i']) && is_array($_REQUEST[$code.'i'])
	&&	isset($_REQUEST[$code.'v']) && is_array($_REQUEST[$code.'v'])
	&&	isset($_REQUEST[$code.'c']) && is_array($_REQUEST[$code.'c'])
	&& !(isset($r['readonly']) && $r['readonly'])) {
		$a = array();
		foreach ($_REQUEST[$code.'i'] as $k=>$v) {
			$a[] = array(
				'i'=>$v,
				'value'=>$_REQUEST[$code.'v'][$k],
				'code'=>$_REQUEST[$code.'c'][$k],
			);
		}
		$r['value'] = $a;
	} else {
		$r['value'] = isset($r['default']) ? $r['default'] : array();
	}
}

function input_param($r) {
	$s = '<div';

	$code = $r['code'];

	$class = array('input-dict');
	if (isset($r['class'])) { $class[] = $r['class']; }
	if (!$r['valid']) { $class[] = 'is-invalid'; }
	$s.= ' class="'.implode(' ', $class).'"';

	$id = isset($r['id']) ? $r['id'] : $code.'i';
	$s.= ' id="'.$id.'"';
	if (isset($r['more'])) { $s.= ' '.$r['more']; }
	$s.= '>';
	$value = $r['value'];
	if (isset($r['readonly']) && $r['readonly']) {
		$plus = '';
	} else {
		$value[] = array('i'=>'','value'=>'','code'=>'');
		$plus = '<button type="button" onclick=\'var t=document.getElementById("'.$id.'");var n=t.childNodes[t.childNodes.length-1].cloneNode(true);for(i=1;i<7;i++){n.childNodes[i].value="";};t.insertBefore(n,this.parentNode);\'><i class="fa fa-plus-circle"></i></button>';
		$plus.= '<button type="button" onclick=\'var t=this.parentNode;if(t==t.parentNode.lastChild){t.parentNode.insertBefore(t,t.previousSibling);}else{t.parentNode.insertBefore(t.nextSibling,t);}\'><i class="fa fa-refresh"></i></button>';
	}
	foreach ($value as $k=>$v) {
		$s.= '<div>
<input name="'.$code.'i[]" value="'.$v['i'].'" type=hidden><input value="'.$v['i'].'" size=3 disabled=disabled>
<input name="'.$code.'v[]" value="'.htmlspecialchars($v['value']).'">
<input name="'.$code.'c[]" value="'.htmlspecialchars($v['code']).'">
'.$plus.'
</div>';
	}

	return $s;
}

function load_param($filter) {
	if ($filter) {
		$v = array();
		$q = db_query('SELECT i,value,code FROM param WHERE filter='.$filter.' ORDER BY w');
		while ($i = db_fetch($q)) {
			$v[] = $i;
		}
		db_close($q);
		return $v;
	} else {
		$r['default'] = array();
	}
}

function save_param($filter, $value) {
	if ($filter) {
		w('clean');
		db_query('DELETE FROM param WHERE filter='.$filter);
		$w = 0;
		foreach ($value as $k=>$r) {
			$w++;

			$v = $r['value'];
			if (!strlen($v)) { continue; }
			$code = strlen($r['code']) ? $r['code'] : str2url($v);
			if ($r['i']) {
				db_insert('param', array(
					'i'=>$r['i'],
					'filter'=>$filter,
					'value'=>$v,
					'code'=>$code,
					'w'=>$w,
				));
			} else {
				db_insert('param', array(
					'filter'=>$filter,
					'value'=>$v,
					'code'=>$code,
					'w'=>$w,
				));
			}
		}
	}
}

?>