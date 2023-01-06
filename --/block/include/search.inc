<?

global $menu;

if (isset($_GET['search'])) {
	if (count($menu) < 2 || $menu[1]['code'] != 'catalog') {
		redirect('/catalog?search='.$_GET['search']);
//		alert($config['q']);
	}
}