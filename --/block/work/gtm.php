<?

function gtm_catalog_fullname($catalog_id, $delimiter = '/') {
	static $cats = array();

	if (isset($cats[$catalog_id])) {
		return $cats[$catalog_id];
	}

	$pathway = cache_load('pathway-hide');

	$ups = kv($pathway, $catalog_id, array('name'=>$catalog_id.'?'));
	$category = '';
	foreach (kv($ups, 'pre', array()) as $j) {
		$category.= kv(kv($pathway, $j, array()), 'name').$delimiter;
	}
	$category.= htmlspecialchars(kv(kv($pathway, $catalog_id, array()), 'name'));

	$cats[$catalog_id] = $category;

	return $category;
}