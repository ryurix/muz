<?

/*
 *	Copyright flydom.ru
 *	Version 1.0.2008-06-10
 */

function pager_nums($here, $max) {
	$nums = array(1);
//	$nums[] = floor($here * 0.25);
	$nums[] = floor($here * 0.5);
//	$nums[] = floor($here * 0.75);
	$nums[] = $here - 2;
	$nums[] = $here - 1;
	$nums[] = $here;
	if ($here < $max) {
		$nums[] = $here + 1;
		$nums[] = $here + 2;
//		$nums[] = $here + ceil(($max - $here + 1) * 0.25);
		$nums[] = $here + ceil(($max - $here + 1) * 0.5);
//		$nums[] = $here + ceil(($max - $here + 1) * 0.75);
		$nums[] = $max;
	}

	$links = array();
	$pre = 0;
	$count = count($nums);
	for($i = 0; $i < $count; $i++) {
		if ($nums[$i] > $pre && $nums[$i] <= $max) {
			if ($nums[$i] > ($pre + 1)) {
				$links[] = '';
			}
			$links[] = $nums[$i];
			$pre = $nums[$i];
		}
	}
	return $links;
}

function pager_query($query, $limit = 10, $name = 0, $count_query = NULL) {
	global $config;

	if (!isset($count_query)) {
	    $count_query = preg_replace(array('/SELECT.*?FROM /As', '/ORDER BY .*/'), array('SELECT COUNT(*) FROM ', ''), $query);
	}

	$page = isset($_GET['page']) ? $_GET['page'] : '';
	settype($page, "integer");
	$max = max(1, ceil(db_result($count_query) / $limit));
	$page = $page < 1 ? 1 : ($page > $max ? $max : $page);

	$pager = array('max' => $max, 'page' => $page);
	$config['pager'][$name] = $pager;

	$query.= ' LIMIT '.(($page-1)*$limit).','.$limit;
	return db_query($query);
}

function pager_block($link = '?', $name = 0, $sharp = '') {
	global $config;

	$pager = $config['pager'][$name];

	$max = $pager['max'];
	if ($max < 2) {return '';}

	$page = $pager['page'];
	$nums = pager_nums($page, $max);

	$block = '<nav><ul class="pagination">';

	if ($page > 1) {
		$block.= '<li class="page-item"><a class="page-link" href="'.$link.'page='.($page - 1).$sharp.'">&laquo</a></li>';
	}
	foreach($nums as $i) {
		if ($i == '') {
			//	$block.= '<li class="page-item disabled"><a class="page-link">&#133;</a></li>';
			$block.= '<li class="page-item">&#133;</li>';
		} else {
			if ($i == $page) {
				$block.= '<li class="page-item active"><a class="page-link">'.$pager['page'].'</a></li>';
			} else {
				$block.= '<li class="page-item"><a class="page-link" href="'.$link.'page='.$i.$sharp.'">'.$i.'</a></li>';
			}
		}
	}
	if ($page < $max) {
		$block.= '<li class="page-item"><a class="page-link" href="'.$link.'page='.($page + 1).$sharp.'">&raquo;</a></li>';
	}

	return $block.'</ul></nav>';
}

?>