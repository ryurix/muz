<?

w('clean');

$catalog = cache_load('catalog');
$pathway = cache_load('pathway');

function echo_home($catalog, $pathway, $level = 0) {
	$level++;
	$sub = isset($catalog['/']) ? $catalog['/'] : array();
	$name = $pathway[$catalog['i']]['name'];
	$url = $pathway[$catalog['i']]['url'];
	if (count($sub) && $level == 1) {
		$s = '

<li>
	<a href="/catalog/'.$url.'" class="has-submenu">'.$name.'</a>
	<ul class="level_1">
';
		foreach ($sub as $i) {
			$s.= echo_home($i, $pathway, $level);
		}
		$s.= '
	</ul>
</li>';
	} else {
		$s = '
<li><a href="/catalog/'.$url.'">'.$name.'</a></li>';
	}

	return $s;
}

$s = '
<ul class="main_catalogmenu">
';
foreach ($catalog['/'] as $sub) {
	$s.= echo_home($sub, $pathway);
}
$s.= '
</ul>';

cache_set('catalog-home', $s);

?>