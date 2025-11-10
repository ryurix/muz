<?php

namespace Ozon;

class Api
{
	static function post($url, $args)
	{
		\Flydom\Memcached::lock('ozon-api', 1);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api-seller.ozon.ru'.$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($args) ? \Flydom\Json::encode($args, 0, true) : $args);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Client-Id: '.\Cabinet\Model::client(),
			'Api-Key: '.\Cabinet\Model::key(),
			'Content-Type: application/json',
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$result = curl_exec($ch);
		curl_close($ch);

		\Flydom\Memcached::unlock('ozon-api');

		return json_decode($result, true);
	}
}