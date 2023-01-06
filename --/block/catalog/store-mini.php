<?

w('gtm');

function store_mini($i) {
	$name = $i['brand'];
	if (strlen($i['model'])) {
		$name.= ' '.$i['model'];
	}
	$name.= ' '.$i['name'];

	$alt = 'alt="'.htmlspecialchars($name).'" title="'.htmlspecialchars($name).'"';

	$href = '/store/'.$i['url'];
	$img = strlen($i['icon']) > 0 ? $i['icon'] : '/design/img/no-photo-s.png';
	if ($i['price'] > 0) {
		$buy = 'купить';
		$price = '<p class="catalog_price">'.number_format($i['price'], 0, '.', ' ').'</p>';
	} else {
		$buy = 'заказать';
		$price = '<p class="catalog_price"></p>';
	}

	$s = '
<div class="catalog_item" data-id="'.$i['i'].'">
';

	if ($i['sign1'] || $i['sign2'] || $i['sign4']) {
		$sign2 = cache_load('sign2');

		$s.= '
	<ul class="catalog_status">';
		$s.= $i['sign1'] ? '<li><img src="'.$sign2[$i['sign1']].'"></li>' : '';
		$s.= $i['sign2'] ? '<li><img src="'.$sign2[$i['sign2']].'"></li>' : '';
		$s.= $i['sign4'] ? '<li><img src="'.$sign2[$i['sign4']].'"></li>' : '';
		$s.= '
	</ul>';
	}

	$speed = $i['count'] ? '<p class="catalog_available">Есть</p>' : '<p class="catalog_available not">Уточняйте</p>';

	$category = gtm_catalog_fullname($i['up']);
/*
	$pathway = cache_load('pathway-hide');
	$up = kv($pathway, $i['up'], array('name'=>$i['up'].'?'));
	$category = '';
	foreach (kv($up, 'pre', array()) as $j) {
		$category.= kv(kv($pathway, $j, array()), 'name').'/';
	}
	$category.= htmlspecialchars(kv(kv($pathway, $i['up'], array()), 'name'));
*/
	$s.= '
	<a href="'.$href.'" class="item_imgcont">
		<img class="item_img" src="'.(strlen($img) ? $img : '/design/img/no-photo-s.png').'" '.$alt.'>
	</a>
	<p class="catalog_articul">Артикул: М'.$i['i'].'</p>
	<a href="'.$href.'" class="catalog_name-product">'.$name.'</a>
	'.$speed.'
	<div class="catalog_itemcont">
		'.$price.'
		<button class="catalog_addToCard buy" data-i='.$i['i'].' data-category="'.$category.'" data-brand="'.$i['brand'].'" data-name="'.htmlspecialchars($name).'" data-price="'.$i['price'].'">
			<img src="/design/img/addtobasket.svg" alt="купить">
		</button>
	</div>
</div>';

	return $s;

}

?>