<?

w('clean');

$catalog = cache_load('catalog');
$pathway = cache_load('pathway');
$here = 'catalog' == substr(\Page::url(), 1, strlen('catalog'));

function echo_shop($catalog, $pathway, $level = 0) {
	$level++;
	$sub = isset($catalog['/']) ? $catalog['/'] : array();
	$name = $pathway[$catalog['i']]['name'];
	$url = $pathway[$catalog['i']]['url'];
	if (count($sub)) {
		$s = '<li><a href="/catalog/'.$url.'" tabindex="-1" class="has-submenu">'.$name.'</a>';
		$s.= '<ul class="level_'.$level.'">';
		foreach ($sub as $i) {
			$s.= echo_shop($i, $pathway, $level);
		}
		$s.= '</ul></li>';
	} else {
		$s = '<li><a href="/catalog/'.$url.'">'.$name.'</a></li>';
	}

	return $s;
}

//$s = '<ul class="header_nav">';
$s = '';
foreach ($catalog['/'] as $sub) {
	$s.= echo_shop($sub, $pathway);
}
//$s.= '</ul>';

// cache_set('menu-catalog', $s);
file_put_contents(\Config::ROOT.'files/menu-catalog.html', $s);



// ------- mobile


function echo_mobile($catalog, $pathway, $level = 0) {
	$level++;
	$sub = isset($catalog['/']) ? $catalog['/'] : array();
	$name = $pathway[$catalog['i']]['name'];
	$url = $pathway[$catalog['i']]['url'];
	if (count($sub)) {
		$s = '
<li>
	<a href="/catalog/'.$url.'" class="sm_name">'.$name.'</a>
	<a href="#" class="sm_link next">&#032;</a>
	<ul>
		<li><button class="sm_btn-close"><i class="fa fa-times"></i></button></li>
';
		foreach ($sub as $i) {
			$s.= echo_mobile($i, $pathway, $level);
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
	<a href="/catalog" class="sm_name">Каталог</a>
	<a href="#" class="sm_link next">&#032;</a>
	<ul>
		<li><button class="sm_btn-close"><i class="fa fa-times"></i></button></li>';
foreach ($catalog['/'] as $sub) {
	$s.= echo_mobile($sub, $pathway);
}
$s.= '
	</ul>
';

//$s.= cache_get('menu-mobile-2');

//cache_set('catalog-mobile', $s);
file_put_contents(\Config::ROOT.'files/menu-mobile.html', $s);

?>