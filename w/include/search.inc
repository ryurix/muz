<?

global $menu;

if (isset($_GET['search'])) {
	if (\Page::depth() < 2 || \Page::menu(1)['code'] != 'catalog') {
		\Page::redirect('/catalog?search='.$_GET['search']);
//		\Flydom\Alert::warning(\Page::url());
	}
}