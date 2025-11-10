<?

$user = \Page::arg();

\Cabinet\Model::load($user);

\Cabinet\Form::start(\Cabinet\Model::defaults());

\Page::name(\Cabinet\Model::name());
\Flydom\Action::before('/setup/cabinet/'.$user.'/stock', 'Товары');

if (\Cabinet\Form::send() === 'delete') {
	\Db::delete('cabinet', ['usr'=>\Cabinet\Form::user()]);
	\Page::redirect('.');
}

if (\Cabinet\Form::isValid())
{
	$exists = \Db::result('SELECT COUNT(*) FROM cabinet WHERE usr='.\Cabinet\Form::user());

	if (\Cabinet\Form::send() === 'send')
	{
		$row = [
			'usr'=>\Cabinet\Form::user(),
			'typ'=>\Cabinet\Form::type(),
			'name'=>\Cabinet\Form::name(),
			'margin'=>\Cabinet\Form::margin(),
			'profit'=>\Cabinet\Form::profit(),
			'vat'=>\Cabinet\Form::vat(),
		];

		$row['data'] = \Flydom\Arrau::encode(\Flydom\Arrau::exclude(array_keys($row), \Cabinet\Form::values()));

		if ($exists) {
			\Db::update('cabinet', $row, ['usr'=>\Cabinet\Form::user()]);
			\Flydom\Alert::success('Кабинет обновлен');
		} else {
			\Db::insert('cabinet', $row);
			\Flydom\Alert::success('Кабинет создан!');
			\Page::redirect('/setup/cabinet/'.\Cabinet\Form::user());
		}
	}

	if (\Cabinet\Form::send() == 'delete' && $exists)
	{
		\Db::delete('cabinet', ['usr'=>\Cabinet\Form::user()]);
		\Flydom\Alert::success('Кабинет удалён');
		\Page::redirect('/setup/cabinet');
	}
}