<?php

namespace App\Libraries;

use Config\Services;

class PaymentGate
{
	public function createPayment($id, $amount, $name, $challenge)
	{
		$url = Services::request()->config->paymentURL;
		$key = Services::request()->config->paymentKey;
		$secret = Services::request()->config->paymentSecret;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, [
			'key' => $key,
			'action' => 'payment',
			'price[]' => $amount,
			'quantity[]' => 1,
			'product[]' => $name,
			'format' => 'json',
			'ureturn' => base_url("user/hosting/?status=return"),
			'uncancel' => base_url('user/hosting/?status=cancel'),
			'unotify' =>  base_url("notify?id=$id&challenge=$challenge&secret=$secret"),
			'buyer_name' => Services::session()->name,
			'buyer_email' => Services::session()->email,
			'buyer_phone' => Services::session()->phone,
			'auto_redirect' => 10,
		]);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

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