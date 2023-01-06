<?

/*
 *	Copyright flydom.ru
 *	Version 2015-06-12
 */

function form_text($row) {
	$more = isset($row['more']) ? ' '.$row['more'] : '';
	$class = array();

	if (isset($row['class'])) { $class[] = $row['class']; }
	if (isset($row['iv']) && $row['iv']) { $class[] = 'iv'; }

	echo '<input type="text"';
	if (count($class)) {
		echo ' class="'.implode(' ', $class).'"';
	}
	if (isset($row['readonly']) && $row['readonly']) {
		echo ' disabled="disabled"';
	}
	if (isset($row['code'])) {
		echo ' name="'.$row['code'].'"';
	}
	if (isset($row['placeholder'])) {
		echo ' placeholder="'.$row['placeholder'].'"';
	}
	if (isset($row['width'])) {
		echo ' style="width:'.$row['width'].'px"';
	}
	$value = str_replace('"', '&quot;', $row['value']);
	echo '" value="'.$value.'"'.$more.'>';
}

function form_input($row, $disabled = 0) {
	$row['class'] = 'input form-control'.(isset($row['class']) ? ' '.$row['class'] : '');
	form_text($row);
}

function form_input_x($row, $disabled = 0) {
	echo '<span class="input-append">';
	form_input($row, $disabled);
	echo '<button class="btn" type="button" onclick="'
."$(this).closest('span').find('input').val('');"
.'">&times;</button></span>';
}

function form_combo($row) {
	$class = array('form-control');
	if (isset($row['iv']) && $row['iv']) { $class[] = 'error'; }
	if (isset($row['class'])) { $class[] = $row['class']; }

	$more = isset($row['more']) ? ' '.$row['more'] : '';
if (!isset($row['width'])) { $row['width'] = 300; }
	if (isset($row['width'])) { $more.= ' style="width:'.$row['width'].'px"'; }

	$class = count($class) > 0 ? ' class="'.implode(' ', $class).'"' : '';
	echo '<select name="'.$row['code'].'"'.$class.$more.'>';
	$values = $row['values'];
	if (!isset($values[$row['value']])) {
		echo '<option value="'.$row['value'].'" SELECTED>';
		$row['value'] = '';
	}
	foreach ($values as $k=>$v) {
		echo '<option value="'.$k.'"';
		if (strcmp($row['value'], $k) == 0) {
			echo ' SELECTED';
		}
		$name = $v;
		if (is_array($name)) {
			$name = $v['name'];
		}
		echo '>'.$name;
	}
	echo '</select>';
}

function form_time($row, $more = '') {
	echo '<input type="text" class="input-mini"'
	.(isset($row['width'])? ' style="width:'.$row['width'].'px"' : '')
	.' name="time'.$row['code'].'" size=5 placeholder="HH:MM" value="';
	w('ft');
	echo $row['value'] ? ft($row['value'], 2) : '';
	echo "\"$more> ";
}

function form_date($row, $more = '') {
	w('ft');
	w('calendar');
	
	$class = 'input-date';
	if (isset($row['class'])) {
		$class.= ' '.$row['class'];
	} else {
		$class.= ' form-control';
	}

	$readonly = isset($row['readonly']) && $row['readonly'];

	if (!$readonly) {
		echo '<span class="input-append">';
	}
	echo '<input type="text" id="dt'.$row['code'].'" name="'.$row['code']
.(isset($row['width'])? '" style="width:'.$row['width'].'px' : '')
.'" placeholder="'.(isset($row['placeholder']) ? $row['placeholder'] : 'ДД.ММ.ГГГГ')
.'" size=10 class="'.$class.'" value="'.($row['value'] ? ft($row['value']) : '').'"'.$more.'>';
	if (!$readonly) {
		echo '<button class="btn btn-default btn-xs" type="button" onclick="Calendar(\'dt'.$row['code']
.'\'); return false;"><i class="fa fa-calendar"></i></button></span>';
	}
}

function form_datetime($row, $more = '') {
	form_time($row, $more);
	form_date($row, $more);
}

function form_file($row, $disabled = 0) {
	if (!$disabled) {
		echo '<span class="btn fileinput-button btn-'.(isset($row['iv']) && $row['iv'] ? 'danger' : 'info').'">'
.'<i class="icon-plus icon-white"></i> '
.'<span>Выбрать файл...</span>'
.'<input type="file" name="'.$row['code'].'">'
.'</span>'
.' <input type="text" name="'.$row['code'].'-url" class="input-large form-control" style="width:300px" placeholder="http://...">';
	}
}

function form_hidden($row) {
	echo '<input type=hidden name="'.$row['code'].'" value="'.$row['value'].'">';
}

function form_textarea($row, $disabled = 0) {
	echo '<textarea name="'.$row['code'].'"';
	if ($disabled) {
		echo ' disabled="disabled"';
	}
	if (isset($row['class'])) {
		echo ' class="'.$row['class'].'"';
	} else {
		echo ' class="form-control"';
	}
	if (isset($row['style'])) {
		echo ' style="'.$row['style'].'"';
	}
	if (isset($row['id'])) {
		echo ' id="'.$row['id'].'"';
	}
	if (isset($row['rows'])) {
		echo ' rows='.$row['rows'];
	}
	echo '>'.$row['value'].'</textarea>';
}

function form_wiki($row, $disabled = 0) {
	w('wiki');
	$row['value'] = wiki_unclean($row['value']);
	form_textarea($row, $disabled);
}

function form_checkbox($row, $disabled = 0) {
	$readonly = $disabled ? ' readonly' : '';
	$checked = array_key_exists('checked', $row) ? $row['checked'] : 1;
	echo '<label class="checkbox';
	if (isset($row['class'])) {
		echo ' '.$row['class'];
	}
	echo '"><input type=checkbox name="'.$row['code'].'"';
	if ($row['value'] == $checked) {
		echo ' CHECKED';
	}
	if (isset($row['id'])) {
		echo ' id="'.$row['id'].'"';
	}	
	echo $readonly.'>';
	if (isset($row['label'])) {
		echo $row['label'];
	}
	echo '</label>';
	echo '<input type=hidden name="'.$row['code'].'--" />';
}

?>