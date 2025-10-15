<?

$plan = w('plan-article');
$plan['pic']['type'] = 'file0';
w('request', $plan);

if ($plan['']['valid'] && $plan['send']['value']==1) {
	w('clean');
	if (strlen($plan['url']['value']) == 0) {
		$plan['url']['value'] = str2url($plan['name']['value']);
	} else {
		$plan['url']['value'] = str2url($plan['url']['value']);
	}

	$q = db_query('SELECT 1 FROM article WHERE url="'.$plan['url']['value'].'"');
	if ($dummy = db_fetch($q) || strlen($plan['url']['value']) < 3) {
		\Flydom\Alert::warning('Данная ссылка уже используется для другой статьи!');
		$plan['']['valid'] = 0;
	}
}

if ($plan['']['valid'] && $plan['send']['value']==1) {
	db_insert('article', array(
		'url' =>$plan['url']['value'],
		'usr'=>$_SESSION['i'],
		'dt'=>\Config::now(),
		'last'=>\Config::now(),
		'hide'=>$plan['hide']['value'],
		'up'=>$plan['up']['value'],
		'up2'=>$plan['up2']['value'],
		'name'=>$plan['name']['value'],
		'pic'=>$plan['pic']['filename'],
		'body'=>$plan['body']['value'],
		'w'=>$plan['w']['value'],
		'tag0'=>$plan['tag0']['value'],
		'tag1'=>$plan['tag1']['value'],
		'tag2'=>$plan['tag2']['value'],
		'tag3'=>$plan['tag3']['value'],
	));
	\Flydom\Alert::warning('Статья создана!');

	$id = db_insert_id();
	$plan['pic']['path'] = '/files/article/'.$id.'/';
	w('input-pic');
	parse_pic($plan['pic']);

	\Page::redirect('.', 302);
}

$config['plan'] = $plan;

?>