<?

$client_user = "mfk3";
$client_id = "d649245c3f894cddb37ee1ca40667953";
$client_secret = "f2dcc50fa4264cff8c82be5c3a707cd5";

// Если мы еще не получили разрешения от пользователя, отправляем его на страницу для его получения
// В урл мы также можем вставить переменную state, которую можем использовать для собственных нужд, я не стал
if (!isset($_GET["code"])) {
	if (is_user('admin')) {
		Header("Location: https://oauth.yandex.ru/authorize?response_type=code&client_id=".$client_id);
		die();
	} else {
		redirect('/');
	}
}

// Если пользователь нажимает "Разрешить" на странице подтверждения, он приходит обратно к нам
// $_Get["code"] будет содержать код для получения токена. Код действителен в течении часа.
// Теперь у нас есть разрешение и его код, можем отправлять запрос на токен.

$result=postKeys("https://oauth.yandex.ru/token",
	array(
		'grant_type'=> 'authorization_code', // тип авторизации
		'code'=> $_GET["code"], // наш полученный код
		'client_id'=>$client_id,
		'client_secret'=>$client_secret
		),
	array('Content-type: application/x-www-form-urlencoded')
	);

// отправляем запрос курлом

function postKeys($url,$peremen,$headers) {
	$post_arr=array();
	foreach ($peremen as $key=>$value) {
		$post_arr[]=$key."=".$value;
		}
	$data=implode('&',$post_arr);
	
	$handle=curl_init();
	curl_setopt($handle, CURLOPT_URL, $url);
	curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($handle, CURLOPT_POST, true);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
	$response=curl_exec($handle);
	$code=curl_getinfo($handle, CURLINFO_HTTP_CODE);
	return array("code"=>$code,"response"=>$response);
	}

// после получения ответа, проверяем на код 200, и если все хорошо, то у нас есть токен

if ($result["code"]==200) {
	$result["response"]=json_decode($result["response"],true);
	$token=$result["response"]["access_token"];
	$expire=$result['response']['expires_in'];
	w('ft');
	alert('Token: '.$token.', expires: '.$expire.' ('.ft(now() + $expire, 1).')');
	cache_save('yandex-market', array(
		'token'=>$token,
		'expires'=>now() + $expire,
		'user'=>$client_user,
		'id'=>$client_id,
	));
}else{
	alert("Ошибка! Код: ".$result["code"]);
}

redirect('/');

// Токен можно кинуть в базу, связав с пользователем, например, а за пару дней до конца токена напомнить, чтобы обновил

?>