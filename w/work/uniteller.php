<?

$config['uniteller'] = array(
	'shop' => '00015973', //'00005294',
	'sign' => 'h8X5xPGC0OXhrRQP2FK3z4XIPa5klBwogbzwcg7nDbRL3jsNwFO8FGjYUUlOTPhpQ3NJMrcOuUpJ6F7d',
	'lifetime' => 300,
	'return_ok'=> 'https://'.$config['domain'].'/basket/ok',
	'return_no'=> 'https://'.$config['domain'].'/basket/no',
);

function u_sign($data) {
	global $config;
	return strtoupper(
		md5(md5($config['uniteller']['shop']) // Shop_IDP
		.'&'.md5($data['i']) // Order_IDP
		.'&'.md5($data['total']) // Subtotal_P
		.'&'.md5(0) // MeanType Платежная система кредитной карты, 0 - любая
		.'&'.md5(0) // EMoneyType Тип электронной валюты, 0 - любая
		.'&'.md5($config['uniteller']['lifetime']) // Lifetime Время жизни формы оплаты в секундах
		.'&'.md5($data['user']) //  Customer_IDP
		.'&'.md5('') // Card_IDP идентификатор зарегистрированной карты
		.'&'.md5('') // IData "Длинная запись" 
		.'&'.md5('') // PT_Code Тип платежа, произвольная строка
		.'&'.md5($config['uniteller']['sign']) // password
		)
	);
}

function u_check($order, $status) {
	global $config;
	return strtoupper(md5($order.$status.$config['uniteller']['sign']));
}

/*
function u_status($order) {
	return null;
}

function u_cron() {
	$select = 'SELECT * FROM bill WHERE state=0';
	$q = db_query($select);
	while ($i = db_fetch($q)) {

	}
}
*/

?>