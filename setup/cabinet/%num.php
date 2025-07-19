<?

$user = $config['args'][0];

\Cabinet\Model::load($user);

if (\Cabinet\Model::valid()) {
	 \Cabinet\Form::type(\Cabinet\Model::type());
}

if (isset($_REQUEST['type'])) {
	\Cabinet\Form::type(\Flydom\Clean::int($_REQUEST['type']));
}

\Cabinet\Form::start(\Cabinet\Model::defaults());

if (\Cabinet\Form::send() === 'delete') {
	\Db::delete('cabinet', ['usr'=>\Cabinet\Form::user()]);
	redirect('.');
}

if (\Cabinet\Form::isValid())
{
	$exists = \Db::result('SELECT COUNT(*) FROM cabinet WHERE usr='.\Cabinet\Form::user());

	if (\Cabinet\Form::send() == 'save')
	{
		$data = \Flydom\Arrau::exclude(\Cabinet\Form::values(), ['type', 'usr', 'name', 'w', 'send']);

		$row = [
			'usr'=>\Cabinet\Form::user(),
			'typ'=>\Cabinet\Form::type(),
			'name'=>\Cabinet\Form::name(),
			'data'=>\Flydom\Arrau::encode($data)
		];

		if ($exists) {
			\Db::update('cabinet', $row, ['usr'=>\Cabinet\Form::user()]);
			\Flydom\Alert::success('Кабинет обновлен');
		} else {
			\Db::insert('cabinet', $row);
			\Flydom\Alert::success('Кабинет создан!');
		}
		redirect('/setup/cabinet/'.\Cabinet\Form::user());
	}

	if (\Cabinet\Form::send() == 'delete' && $exists)
	{
		\Db::delete('cabinet', ['usr'=>\Cabinet\Form::user()]);
		\Flydom\Alert::success('Кабинет удалён');
		redirect('/setup/cabinet');
	}
}