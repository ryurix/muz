<? namespace Flydom\Input;

class Line extends Input
{
    static $subtypes = [
		'color',
		'date',
		'datetime-local',
        'email',
		'month',
		'number',
		'url',
		'week',
        'phone',
        'tel',
    ];

	function parse($values = null) {
		$v = &$this->data;

		$v['valid'] = 1;
		if (isset($v['value'])) {
			$v['value'] = trim($v['value']);
			if (isset($v['exp'])) {
				$v['valid'] = preg_match('/^'.$v['exp'].'$/ui', $v['value']) ? 1 : 0;
			}
			if (isset($v['min']) && mb_strlen($v['value'])<$v['min']) {
				$v['valid'] = 0;
			}
		} else {
			if (isset($v['default'])) {
				$v['value'] = $v['default'];
			} else {
				$v['value'] = '';
				$v['valid'] = !isset($v['exp']) && !isset($v['min']);
			}
		}
	}

	function build($class = '') {
		$v = &$this->data;

		$subtype = 'text';
		if (!empty($v['subtype']) && false !== array_search($v['subtype'], self::$subtypes)) {
            $subtype = $v['subtype'];
        }
		$back = '<input type="'.$subtype.'" name="'.$v['code'].'"';

		$class = !is_array($class) && strlen($class) ? array($class) : array();
		$class[] = 'form-control';
		if (isset($v['class'])) { $class[] = $v['class']; }
		if (isset($v['iv']) && $v['iv']) { $class[] = 'is-invalid'; }
		if (count($class)) {
			$back.=' class="'.implode(' ', $class).'"';
		}

		$style = array();
		if (isset($v['width'])) { $style[] = 'width:'.$v['width'].(ctype_digit($v['width'].'') ? 'px' : '');	}
		if (count($style)) {
			$back.=' style="'.implode(';', $style).';"';
		}

		if (isset($v['placeholder'])) { $back.=' placeholder="'.$v['placeholder'].'"'; }
		if (isset($v['readonly']) && $v['readonly']) { $back.=' disabled="disabled"'; }
		if (isset($v['id'])) { $back.= ' id="'.$v['id'].'"'; }
		if (isset($v['required']) && $v['required']) { $back.= ' required aria-required="true"'; }
//		if (isset($v['more'])) { $back.= ' '.$v['more']; }

		$value = str_replace('"', '&quot;', $v['value']);
		$more = isset($v['more']) ? ' '.$v['more'] : '';

		$back.=' value="'.$value.'"'.$more.'>';

        $back .= isset($v['error-block']) ? '<div id="error-'.$v['code'].'" style="color: #990000; font-size: 0.9em;'.((empty($v['error-block'])) ? ' display: none;' : '') .'">'.((!empty($v['error-block'])) ? $v['error-block'] : '').'</div>' : '';

		return $back;
	}

}

?>
