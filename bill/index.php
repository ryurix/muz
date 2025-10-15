<?

if (!\User::is('')) {
	\Page::redirect('/');
}

?>