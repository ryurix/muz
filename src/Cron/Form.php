<?

namespace Cron;

class Form
{
	static function name($value = null) { return FormData::getSet('name', $value, ''); }
	static function send() { return FormData::get('send'); }
	static function type($value = null) { return FormData::getSet('typ', $value, 0); }

	protected static $types;
	static function types($value = null) {
		if (!is_null($value)) {
			static::$types = is_array($value) ? $value : [$value=>\Cron\Type::name($value)];
		}
		return static::$types;
	}

	protected static $follow;
	static function follow($values = null) { return is_null($values) ? static::$follow : static::$follow = $values; }

	const EVERY = [
		0=>'Не запускать автоматически',
		1=>'Ежедневно по расписанию',
		60=>'1 минута',
		120=>'2 минуты',
		180=>'3 минуты',
		300=>'5 минут',
		600=>'10 минут',
		900=>'15 минут',
		1200=>'20 минут',
		1500=>'25 минут',
		1800=>'30 минут',
		2100=>'35 минут',
		3600=>'1 час',
		28800=>'8 часов',
		64800=>'18 часов',
		86400=>'1 день',
		259200=>'3 дня'
	];

	static function load($id) {
		$row = \Db::fetchRow(\Db::select('*', 'cron', ['i'=>$id]));
		if (!is_array($row)) { return []; }
		FormData::set('i', $id);
		$row['week'] = \Flydom\Arrau::decode($row['week']);
		$row['follow'] = \Flydom\Arrau::decode($row['follow']);
		return \Flydom\Arrau::decode($row['data']) + $row;
	}

	static function save()
	{
		$new = [
			'typ'=>FormData::get('typ'),
			'name'=>FormData::get('name'),
			'usr'=>FormData::get('usr') ?? 0,
			'info'=>'',
			'every'=>FormData::get('every'),
			'time'=>FormData::get('time'),
			'week'=>\Flydom\Arrau::encode(FormData::get('week')),
			'follow'=>\Flydom\Arrau::encode(FormData::get('follow') ?? ''),
		];

		$new['dt'] = \Flydom\Cron\Task::next($new);
		FormData::field('send')->name(static::nextDt($new['dt']));

		$data = [];
		foreach (FormData::values() as $k=>$v) {
			if (empty($k) || isset($new[$k]) || $k === 'send') { continue; }
			$data[$k] = $v;
		}
		$new['data'] = \Flydom\Arrau::encode($data);

		if (FormData::get('i') ?? 0) {
			\Db::update('cron', $new, ['i'=>FormData::get('i')]);
			$new['i'] = FormData::get('i');
		} else {
			$new['i'] = \Db::insert('cron', $new);
		}

		return $new;
	}

	static function start($plan, $default)
	{
		FormData::plan(static::plan($plan), $default);
		if (isset($default['dt'])) {

			FormData::field('send')->name(static::nextDt($default['dt']));
			unset($default['dt']);
		}
		FormData::parse();
	}

	protected static function nextDt($dt) {
		if (!$dt) { return ''; }
		return $dt < \Config::now() ? 'скоро' : \Flydom\Time::dateTimes($dt);
	}

	static function valid() { return FormData::isValid(); }
	static function validate() { return FormData::validate(); }

	protected static function plan($custom)
	{
		$week = [1=>'пн', 2=>'вт', 3=>'ср', 4=>'чт', 5=>'пт', 6=>'сб', 7=>'вс'];

		$plan1 = [
			'typ'=>new \Flydom\Input\Select('Тип', static::$types),
			'name'=>new \Flydom\Input\Line('Название'),
		];

		$plan2 = [
			'every'=>new \Flydom\Input\Select('Период', self::EVERY),
			'time'=>new \Flydom\Input\Time('Время запуска'),
			'week'=>new \Flydom\Input\Multiselect(['name'=>'Дни недели', 'class'=>'chosen', 'values'=>$week, 'placeholder'=>'ежедневно']),
			'follow'=>empty(static::$follow) ? new \Flydom\Input\None() : new \Flydom\Input\Multiselect(['name'=>'Следующая', 'values'=>static::$follow, 'class'=>'chosen', 'placeholder'=>' ']),
			'dt'=>new \Flydom\Input\Checkbox(['name'=>'Запуск', 'label'=>'Запустить сейчас']),
			'send'=> new \Flydom\Input\Button('', [
				'send'=>['name'=>'Сохранить', 'class'=>'btn-default'],
				'delete'=>['name'=>'Удалить', 'confirm'=>'Удалить выгрузку?']
			]),
		];

		return array_merge($plan1, $custom) + $plan2;
	}

	static function build() {
		return FormData::build();
	}

	static function process($root) {
		if (!empty(\Cron\Form::send())) {
			if (\Cron\Form::valid()) {
				if (\Cron\Form::send() === 'send') {
					$task = \Cron\Form::save();
					if (FormData::get('dt')) {
						FormData::set('dt', 0);
						$info = \Flydom\Cron\Task::execute($task);
						\Flydom\Alert::warning($info);
						\Db::update('cron', [
							'last'=>\Config::now(),
							'info'=>mb_substr(trim($info), 0, 65535),
						], ['i'=>$task['i']]);
					} else {
						\Flydom\Alert::success('Задача сохранена');
					}
					if (!FormData::get('i') ?? 0) {
						\Page::redirect($root.'/'.$task['i']);
					}
				}
				if (\Cron\Form::send() === 'delete') {
					\Db::delete('cron', ['i'=>FormData::get('i')]);
					\Flydom\Alert::warning('Задача удалена!');
					\Page::redirect($root);
				}
			} else {
				\Cron\Form::validate();
			}
		}
	}
}

class FormData extends \Form\Form {
	static protected $valid = null;
	static protected $fields = [];
	static protected $default = [];

	static protected $open = true;
	static protected $close = true;
	static protected $action = null;
	static protected $name = null;
	static protected $class = null;
	static protected $method = 'REQUEST';
}