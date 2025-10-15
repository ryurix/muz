<?

/*

Base32: CXRFLB3RPAVAU6NW27EQCVSEN6SHHFYHRZPW5Y3B4GSWWZJ5IJTQ

Base64: FeJVh3F4Kgp5ttfJAVZEb6RzlweOX27jYeGla2U9Qmc=

HEX: 15E2558771782A0A79B6D7C90156446FA47397078E5F6EE361E1A56B653D4267

*/

function appex($method, $url, $data, $debug = 0) {
	global $config;

	$data = json_encode($data);

	$ch = curl_init('https://ecommerce.appex.ru/v1/api/'.$url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	if (isset($config['appex'])) {
		curl_setopt($ch, CURLOPT_COOKIE, '.p2mAuth='.$config['appex']);
	}

	if ($debug) {
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		print_pre($data);
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: '.strlen($data)));
	$result = curl_exec($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	if ($debug) {
		print_pre(curl_getinfo($ch, CURLINFO_HEADER_OUT));
		print_pre($code);
		print_pre($result);
	}

	curl_close($ch);

	if ($code >= 200 && $code < 300) {
		return json_decode($result);
	} else {
		return $code;
	}
}

class OneTimePasswordGenerator {

	static $PASS_CODE_LENGTH = 6;
	static $PIN_MODULO;

	public function __construct() {
		self::$PIN_MODULO = pow(10, self::$PASS_CODE_LENGTH);
	}

	public function getCode($base64Secret, $time = null) {
		if (!$time) {
			$time = floor(\Config::now() / 30);
		}
		$secret = base64_decode($base64Secret);
		$time = pack("N", $time);

		$time = str_pad($time,8, chr(0), STR_PAD_LEFT);
		$hash = hash_hmac('sha1',$time,$secret,true);
		$offset = ord(substr($hash,-1));
		$offset = $offset & 0xF;
		$truncatedHash = self::hashToInt($hash, $offset) & 0x7FFFFFFF;
		$pinValue = str_pad($truncatedHash % self::$PIN_MODULO,6,"0",STR_PAD_LEFT);

		return $pinValue;
	}

	protected function hashToInt($bytes, $start) {
		$input = substr($bytes, $start, strlen($bytes) - $start);
		$val2 = unpack("N",substr($input,0,4));
		return $val2[1];
	}
}

// Авторизация

$appex = cache_load('appex');
if (!isset($appex['exp']) || $appex['exp'] < (time() - 5*60)) {

	$pass_gen = new OneTimePasswordGenerator;

	$oneTimePassword = $pass_gen->getCode('FeJVh3F4Kgp5ttfJAVZEb6RzlweOX27jYeGla2U9Qmc=');

	$post = array(
		'login'=>'muzmart-api',
		'password'=>'pastila72970494',
		'oneTimePassword'=>$oneTimePassword,
	);

	$a = appex('POST', 'accounts/authenticate', $post);

	if (is_object($a)) {
		$appex = array(
			'dt'=>\Config::now(),
			'name'=>$a->name,
			'exp'=>strtotime($a->expiration),
			'key'=>$a->value,
		);

		cache_save('appex', $appex);
	}

	appex('PUT', 'events/handlers', array('url'=>'https://muzmart.com/basket/appex?{params}'));
}

$config['appex'] = $appex['key'];

// Изменение URL оповещения

//$result = appex('PUT', 'events/handlers', array('url'=>'https://new.muzmart.com/basket/appex?type={eventType}&amp;params={params}'));
//$result = appex('GET', 'events/handlers', array(), 1);

//appex('GET', 'paymentOptions', array(), 1);


/*
print_pre($appex);
w('ft');
print_pre(ft($appex['dt'], 1));
print_pre(ft($appex['exp'], 1));
*/

?>