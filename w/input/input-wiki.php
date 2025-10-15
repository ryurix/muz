<?

w('clean');
w('input-text');

function parse_wiki(&$v) {
	$v['valid'] = 1;
	if (isset($v['value'])) {
		$v['value'] = wiki_clean($v['value']);
	} else {
		$v['value'] = isset($v['default']) ? $v['default'] : '';
	}

	$v['columns'] = $v['columns'] ?? 2;
}

function input_wiki($v) {
	$v['value'] = wiki_unclean($v['value']);
	return input_text($v);
}

/*
 *	Copyright flydom.ru
 *	Version 1.2.2011-05-01
 */

function wiki_clean($text) {
	$text = str_replace(chr(0), '', $text);
	$text = str_replace(array("\r\n", "\r"), "\n", $text);
	$text = str_replace(array("-", "-", "-"), "-", $text);
	$text = str_replace("\t", " ", $text);

	$text = trim($text);

//	Parsing

	$ps = explode("\n", $text);
	$ps[] = "\n";
	array_unshift($ps, "\n");

	$length = count($ps);
	for ($i = 0; $i < $length; $i++) {
		$s = trim($ps[$i]);
		$p = array(
			'stars' => '',
		);

		if (substr($s, 0, 3) == "===") {
			$p['==='] = ltrim(substr($s, 3));
			$p['text'] = '';
		} else {
			if (preg_match('%^[\*#№]+%', $s, $ms)) {
				$p['stars'] = $ms[0];
				$s = ltrim(substr($s, strlen($ms[0])));
			} else {
				if (preg_match('%^[;\|]%', $s, $ms)) {
					$p['stars'] = '|';
					$s = substr($s, strlen($ms[0]));
					$s = ltrim(str_replace(";", "|", $s));
				}
			}
			$p['text'] = $s;
		}
		$ps[$i] = $p;
	}

//	Assembling

	$h = array(3 => 1, 4 => 1, 5 => 1, 6 => 1);

	$text = '';
	$sirius = '';
	$stack = array();
	while (count($ps)) {
		$p = array_shift($ps);
		$stars = $p['stars'];
		if ($p['text'] == '') {
			while (count($stack) && $stack[0] != 'div') {
				$text.= "</".array_shift($stack).">\n";
			}
// Divs
			if (isset($p['==='])) {
				if ($p['==='] == '') {
					if (count($stack) && $stack[0] == 'div') {
						$text.= "</".array_shift($stack).">\n";
					}
				} else {
					$text.= '<div class="'.$p['==='].'">'."\n";
					array_unshift($stack, 'div');
				}
			}
		} else {
			if (count($stack) == 0 || $stack[0] == 'div') {
				if ($stars != '' && $stars != '|' && count($ps) && $ps[0]['text'] == '') {
// Header
					$l = min(strlen($stars) + 2, 6);
					$type = substr($stars, 0, 1);
					$text.= "<h$l>";
					if ($type == '*') {
						$text.= wiki_line($p['text']);
					} else {
						for ($i=3; $i<=6; $i++) {
							if ($i <= $l) { $text.= $h[$i].'.'; }
							else { $h[$i] = 1; }
						}
						$h[$l] = $h[$l] + 1;
						$text.= ' '.wiki_line($p['text']);
					}
					$text.= "</h$l>\n";
					continue;
				} else {
					$text.= "<p>";
					array_unshift($stack, 'p');
				}
			}

			if ($stars == '') {
// Nothing
				if ($stars != $sirius) {
					while (count($stack) && $stack[0] != 'p') {
							$text.= "</".array_shift($stack).">\n";
					}
				} else {
					$text.= '<br>';
				}

				if (strlen($p['text']) > 0) {
					$text.= wiki_line($p['text'])."\n";
				}
			} else {
            	if ($p['stars'] == '|') {
// Table
					if ($stars != $sirius) {
						while (count($stack) && $stack[0] != 'p') {
							$text.= "</".array_shift($stack).">\n";
						}
						$text.= "\n<table>\n";
						array_unshift($stack, 'table');
						$text.= '<tr class="first">';
					} else {
						$text.= '<tr>';
					}

					$cells = explode('|', $p['text']);

					for ($i = 0; $i < count($cells); $i++) {
						$cells[$i] = wiki_line($cells[$i]);
					}

					$text.= '<th>'.array_shift($cells).'</th>';
					$text.= '<td>'.implode('</td><td>', $cells).'</td>';
					$text.= "</tr>\n";
					
				} else {
// List
					if ($stars != $sirius) {

						$dif = strlen($stars) - strlen($sirius);
						if ($dif >= 0) {
							if ($dif == 0) {
								$text.= "</".array_shift($stack).">\n";
								$text.= "</".array_shift($stack).">\n";
							} else {
								if (strlen($stars) > 1) {
									$text.= "\n";
								}
							}

							$type = substr($stars, -1, 1);
							$type = $type == '*' ? 'ul' : 'ol';
							$text.= "<$type>\n";
							array_unshift($stack, $type);
						} else {
							while ($dif < 0) {
								$dif = $dif + 1;
								$text.= "</".array_shift($stack).">\n";
								$text.= "</".array_shift($stack).">\n";
							}
						}

					} else {
						$text.= "</".array_shift($stack).">\n";
					}

					$text.= '<li>'.wiki_line($p['text']);
					array_unshift($stack, 'li');
				}
			}
		}
		$sirius = $stars;
	}
	$text = str_replace(
		array("\n</p>", "<p>\n", "<p><br>", "\n<br>"),
		array('</p>', '<p>', "<p>", "<br>\n"),
		$text);
	return trim($text);
}

function wiki_line($text) {
	global $config;

	$config['span-class'] = array(
		'красный' => 'red',
		'red' => 'red',
		'quote' => 'quote',
		'цитата' => 'quote',
		'one' => 'one',
		'two' => 'two',
	);

// Simple

	$text = str_replace(
	    array(/*'(+)', */'--'),
		array(/*'<br />', */'&mdash;'),
		$text);

// Bold

	$text = preg_replace('%\\\\\\\\(.*)\\\\\\\\%U', '<b>$1</b>', $text);

// Italic

	$text = preg_replace('%//(.*)//%U', '<i>$1</i>', $text);

// Underline

	$text = preg_replace('%__(.*)__%U', '<b>$1</b>', $text);

// Spans

	$search = array();
	$replace = array();

	preg_match_all(
		"%\(\+([^\+]+)\+(.+)\+\)%",
		$text, $chunks, PREG_SET_ORDER);

	while (count($chunks)) {
		$chunk = array_shift($chunks);

		$params = explode(' ', $chunk[1]);
		$class = array();
		while (count($params)) {
			$param = array_shift($params);
			if (isset($config['span-class'][$param])) {
				$class[] = $config['span-class'][$param];
			}
		}

		if (count($class)) {
			$search[] = $chunk[0];
	        $replace[] = '<span class="'.implode(' ', $class).'">'.$chunk[2].'</span>';
		}
	}

	$text = str_replace($search, $replace, $text);
	$text = clean_html($text);

	return $text;
}

function wiki_unclean($text) {
	$text = str_replace(
		array("</p>\n<p>", "</p>\n<div", "</div>\n<p>", "<br>"),
		array("</p>\n\n<p>", "</p>\n\n<div", "</div>\n\n<p>", ""),
		$text);
	$text = str_replace(
		array('<p>', '</p>', "<table>\n", "</table>\n", '<table>', '</table>', '<tr>',
			'<tr class="first">', '</tr>', '</th>', '</td>', "\n</li>", '</li>'),
		"", $text);

// Tables

	$text = str_replace(
		array('<th>', '<td>'),
		"|", $text);

// Headers

	$search = array();
	$replace = array();

	preg_match_all("%<h([3-6])>(([0-9]+\.)*) ?([^<]*)</h[3-6]>%", $text, $chunks, PREG_SET_ORDER);

	while (count($chunks)) {
		$chunk = array_shift($chunks);
		$search[] = $chunk[0];
		$replace[] = str_repeat($chunk[2] == '' ? '*' : '#', $chunk[1] - 2).' '.$chunk[4]."\n";
	}

	$text = str_replace($search, $replace, $text);

// Divs

	$text = preg_replace('%<div class="([^"]+)">%', '=== $1', $text);
	$text = str_replace("</div>", '===', $text);

// List

	$lines = explode("\n", $text);
	$count = count($lines);

	$stars = '';
	for ($i=0; $i<$count; $i++) {
		$s = &$lines[$i];

		if (substr($s, 0, 1) == '<') {
			$s4 = substr($s, 0, 4);
			$s5 = substr($s, 0, 5);

			if ($s5 == '</ol>' || $s5 == '</ul>') {
				$stars = substr($stars, 0, -1);
			}
			if ($s4 == '<ol>') {$stars.= '#';}
			if ($s4 == '<ul>') {$stars.= '*';}
			if ($s4 == '<li>') {$s = $stars.' '.$s;}
		}

		$s = wiki_unline($s);
	}

	$text = implode("\n", $lines);
	$text = str_replace(array("</ol>\n", '</ol>', "</ul>\n", '</ul>', "<ol>\n", "<ul>\n", '<li>'),
		'', $text);

	return $text;
}

function wiki_unline($text) {

// Simple

	$text = str_replace(
		array(/*'<br />', */'&mdash;'),
	    array(/*'(+)', */'--'),
		$text);

// Bold

	$text = preg_replace('%<b>([^<]+)</b>%U', '\\\\\\\\$1\\\\\\\\', $text);

// Italic

	$text = preg_replace('%<i>([^<]+)</i>%U', '//$1//', $text);

// Underline

	$text = preg_replace('%<u>([^<]+)</u>%U', '__$1__', $text);

// Spans

	$search = array();
	$replace = array();

	preg_match_all(
		'%<span class="([^"]+)">([^<]+)</span>%',
		$text, $chunks, PREG_SET_ORDER);

	while (count($chunks)) {
		$chunk = array_shift($chunks);

		$class = explode(' ', $chunk[1]);
		$search[] = $chunk[0];
        $replace[] = '(+'.implode(' ', $class).'+'.$chunk[2].'+)';
	}

	$text = str_replace($search, $replace, $text);

	return $text;
}

?>