<?

// docs https://yandex.ru/dev/market/partner-marketplace-cd/doc/dg/reference/put-campaigns-id-orders-id-status.html
// logs https://partner.market.yandex.ru/supplier/21927869/api/log

header("Access-Control-Allow-Origin: *");

$request = $_REQUEST;

$request['user'] = $_SESSION['i'];

$config['design'] = 'none';

$block['body'] = json_encode($request);
