<?

w('chosen.js');

$city = cache_load('city');
\Page::name($city[$_SESSION['city']]);

?>