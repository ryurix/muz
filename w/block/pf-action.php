<?

if ($args) {
	\Action::before('/portfolio/'.$args, 'Посмотреть');
	\Action::before('/portfolio/'.$args.'/edit', 'Изменить запись');
	\Action::before('/portfolio/'.$args.'/erase', 'Удалить запись');
}
