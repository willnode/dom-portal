<?php

namespace App\Models;

use Config\Services;

class PaymentGate
{
	public function createPayment($id, $amount, $name, $challenge)
	{
		$key = Services::request()->config->paymentKey;
		$prod = Services::request()->config->paymentEnv === 'production';
		$sub = $prod ? 'my' : 'sandbox';
		$ch = curl_init("https://$sub.ipaymu.com/payment/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, [
			'key' => $key,
			'action' => 'payment',
			'price[]' => $amount,
			'quantity[]' => 1,
			'product[]' => $name,
			'format' => 'json',
			'ureturn' => base_url("notify?id=$id&challenge=$challenge"),
			'uncancel' => base_url('user/hosting/'),
			'unotify' =>  base_url("notify?id=$id&challenge=$challenge"),
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
