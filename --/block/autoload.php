<?

$autoload = ['Cron\\Ozon'=>'src/Cron/Ozon.php','Cron\\Prices'=>'src/Cron/Prices.php','Cron\\Sitemap'=>'src/Cron/Sitemap.php','Cron\\Task'=>'src/Cron/Task.php','Cron\\Wildberries'=>'src/Cron/Wildberries.php','Cron\\Yml'=>'src/Cron/Yml.php','Marketplace\\Sber'=>'src/Marketplace/Sber.php','Type\\Cron'=>'src/Type/Cron.php','Type\\Log'=>'src/Type/Log.php','Type\\Price'=>'src/Type/Price.php','Flydom\\Cache'=>'lib/flydom/Cache.php','Flydom\\Clean'=>'lib/flydom/Clean.php','Flydom\\Db'=>'lib/flydom/Db.php','Flydom\\Debug'=>'lib/flydom/Debug.php','Flydom\\Form'=>'lib/flydom/Form.php','Flydom\\Input\\Button'=>'lib/flydom/Input/Button.php','Flydom\\Input\\Checkbox'=>'lib/flydom/Input/Checkbox.php','Flydom\\Input\\Date'=>'lib/flydom/Input/Date.php','Flydom\\Input\\Datetime'=>'lib/flydom/Input/Datetime.php','Flydom\\Input\\File'=>'lib/flydom/Input/File.php','Flydom\\Input\\Hidden'=>'lib/flydom/Input/Hidden.php','Flydom\\Input\\Html'=>'lib/flydom/Input/Html.php','Flydom\\Input\\Input'=>'lib/flydom/Input/Input.php','Flydom\\Input\\Integer'=>'lib/flydom/Input/Integer.php','Flydom\\Input\\Line'=>'lib/flydom/Input/Line.php','Flydom\\Input\\Multibox'=>'lib/flydom/Input/Multibox.php','Flydom\\Input\\Number'=>'lib/flydom/Input/Number.php','Flydom\\Input\\Password'=>'lib/flydom/Input/Password.php','Flydom\\Input\\Select'=>'lib/flydom/Input/Select.php','Flydom\\Input\\Submit'=>'lib/flydom/Input/Submit.php','Flydom\\Input\\Text'=>'lib/flydom/Input/Text.php','Flydom\\Log'=>'lib/flydom/Log.php','Flydom\\LogType'=>'lib/flydom/LogType.php','Flydom\\Page'=>'lib/flydom/Page.php','Flydom\\Singleton'=>'lib/flydom/Singleton.php','Flydom\\Type'=>'lib/flydom/Type.php','Flydom\\User'=>'lib/flydom/User.php','SimpleXLSX'=>'lib/shuchkin/simplexlsx/src/SimpleXLSX.php'];

spl_autoload_register(function ($name) use ($autoload) {
	if (isset($autoload[$name])) {
		include_once $autoload[$name];
	}
});

unset($autoload);