<?

// TODO: удалить старый модуль после проверки нового
//w('google-merchant-old');

set_time_limit(0);


$site = cache_load('site');

$msk = $site[34];
foreach ($site as $k=>$v)
{
	if ($v === $msk)
	{
		$dummy = array(
			'site'=>$v,
			'suffix'=>'',
		);
		$count = w('google-merchant', $dummy);		
	}
	else
	{
		$s = substr($v, 0, -1-strlen($msk));
		$dummy = array(
			'site'=>$v,
			'suffix'=>'_'.$s,
		);
		w('google-merchant', $dummy);
	}
}


/*
$dummy = array(
	'site'=>'muzmart.com',
	'suffix'=>'',
);
$count = w('google-merchant', $dummy);

$dummy = array(
	'site'=>'tmn.muzmart.com',
	'suffix'=>'_tmn',
);
w('google-merchant', $dummy);
*/

$status = cache_load('status');
$status['google-merchant'] = array(
	'dt'=>now(),
	'count'=>$count,
);
cache_save('status', $status); 

?>