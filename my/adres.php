<?

w('chosen.js');

$city = cache_load('city');
$config['name'] = $city[$_SESSION['city']];

?>