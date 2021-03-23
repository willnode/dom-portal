<?php

namespace App\Libraries;

use App\Entities\Login;
use Config\Services;

/**
 * @codeCoverageIgnore
 */
class IpaymuGate
{
	public function createPayment($id, $amount, $name, $challenge, Login $login)
	{
		$url = Services::request()->config->ipaymuURL;
		$key = Services::request()->config->ipaymuKey;
		$secret = Services::request()->config->ipaymuSecret;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		curl_setopt($ch, CURLOPT_POSTFIELDS, [
			'key' => $key,
			'action' => 'payment',
			'price[]' => $amount,
			'quantity[]' => 1,
			'product[]' => $name,
			'format' => 'json',
			'ureturn' => base_url("user/host/?status=return"),
			'uncancel' => base_url('user/host/?status=cancel'),
			'unotify' =>  base_url("api/notify?id=$id&challenge=$challenge&secret=$secret"),
			'buyer_name' => $login->name,
			'buyer_email' => $login->email,
			'buyer_phone' => $login->phone,
			'auto_redirect' => 10,
		]);

		// execute!
		$response = curl_exec($ch);
		curl_close($ch);
		if ($response) {
			return json_decode($response);
		} else {
			return null;
		}
	}
}
