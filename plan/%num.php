<?

$code = \Page::arg();

$row = \Cron\Form::load($code);
if (empty($row)) {
	if ($code) {
		\Flydom\Alert::danger('Задача не найдена: ' . $code);
		\Page::redirect('/plan');
	}

	$row = [
		'i'=>0,
		'typ'=>0,
		'name'=>'Новая задача',
		'dt'=>\Config::now() + 24*60*60,
		'every'=>86400,
		'data'=>'',
		'week'=>[],
		'follow'=>[],
	];
}

$follow = \Db::fetchMap('SELECT i,name FROM cron WHERE typ>=100 AND i<>'.$code.' ORDER BY name');
\Cron\Form::types(\Cron\Type::names(100));
\Cron\Form::follow($follow);

\Cron\Form::start([
], $row);

\Page::name(\Cron\Form::name());

\Cron\Form::process('/plan');
