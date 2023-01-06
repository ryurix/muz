<?

w('clean');
$url = str2url($config['args'][0]);

$q = db_query('SELECT * FROM article WHERE url="'.$url.'"');
$root = '/article';

if ($row = db_fetch($q)) {
	$config['name'] = $row['name'];

	$action = count($config['args']) > 1 ? $config['args'][1] : 'view';

	if ($action == 'view') {
/*
		$canonical = '<link rel="canonical" href="https://'.$config['domain'].$root.'/'.$row['url'].'">';
		if (isset($block['head'])) {
			$block['head'].= "\n".$canonical;
		} else {
			$block['head'] = $canonical;
		}
*/

		if (count($_GET)) {
			$config['canonical'] = '/article/'.$row['url'];
		}
		unset($row['up']);
		$config['og:image'] = '/files/article/'.$row['i'].'/mini.jpg';
		$config['row'] = $row;
		refile('view.html');
	} elseif ($action == 'edit') {
		$plan = w('plan-article');
		$plan['']['default'] = $row;
		$plan['pic']['path'] = '/files/article/'.$row['i'].'/';
		w('request', $plan);

		$config['action'] = array(array('href'=>'/article/'.$row['url'], 'action'=>'смотр'));

		if ($plan['']['valid'] && $plan['send']['value']==1) {
			w('clean');
			if (strlen($plan['url']['value']) == 0) {
				$plan['url']['value'] = str2url($plan['name']['value']);
			} else {
				$plan['url']['value'] = str2url($plan['url']['value']);
			}

			$q = db_query('SELECT 1 FROM article WHERE url="'.$plan['url']['value'].'" AND i<>'.$row['i']);
			if ($dummy = db_fetch($q) || strlen($plan['url']['value']) < 3) {
				alert('Данная ссылка уже используется для другой статьи!');
				$plan['']['valid'] = 0;
			}
		}
		
		if ($plan['']['valid'] && $plan['send']['value'] == 1) {
			db_update('article', array(
				'url'=>$plan['url']['value'],
				'last'=>now(),
				'hide'=>$plan['hide']['value'],
				'up'=>$plan['up']['value'],
				'up2'=>$plan['up2']['value'],
				'name'=>$plan['name']['value'],
				'pic'=>$plan['pic']['value'],
				'body'=>$plan['body']['value'],
				'w'=>$plan['w']['value'],
				'tag0'=>$plan['tag0']['value'],
				'tag1'=>$plan['tag1']['value'],
				'tag2'=>$plan['tag2']['value'],
				'tag3'=>$plan['tag3']['value'],
			), array('i'=>$row['i']));

			if (strlen(kv($plan['pic'], 'filename', ''))) {
				db_update('article', array(
					'pic'=>$plan['pic']['filename'],
				), array('i'=>$row['i']));
			}
			alert('Статья изменена');
//			redirect($root);
		} elseif ($plan['']['valid'] && $plan['send']['value'] == 2) {
			db_delete('article', array(
				'i'=>$row['i']
			));
			alert('Статья удалена');
			redirect($root);
		}
		$config['plan'] = $plan;
	}	
} else {
	redirect($root);
}

?>