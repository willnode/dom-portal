<?php

namespace App\Libraries;

use App\Entities\Login;
use Config\Services;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersAuthorizeRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException;

/**
 * @codeCoverageIgnore
 */
class PayPalGate
{
    /**
     * @return mixed
     */
    public function createPayment($id, $amount, $name, $challenge, Login $login)
    {
        $key = Services::request()->config->paypalClient;
        $secret = Services::request()->config->paypalSecret;
        $environment = ENVIRONMENT === 'production' ? new ProductionEnvironment($key, $secret) : new SandboxEnvironment($key, $secret);
        $client = new PayPalHttpClient($environment);
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => $id,
                "description" => $name,
                "custom_id" => $challenge,
                "amount" => [
                    "value" => number_format($amount, 2, '.', ''),
                    "currency_code" => "USD"
                ]
            ]],
            "application_context" => [
                "cancel_url" => base_url('user/host/?status=cancel'),
                "return_url" => base_url("user/host/?status=return")
            ]
        ];

        try {
            // Call API with your client and get a response for your call
            return $client->execute($request)->result;
        } catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }

    public function getPayment($id)
    {
        $key = Services::request()->config->paypalClient;
        $secret = Services::request()->config->paypalSecret;
        $environment = ENVIRONMENT === 'production' ? new ProductionEnvironment($key, $secret) : new SandboxEnvironment($key, $secret);
        $client = new PayPalHttpClient($environment);
        $request = new OrdersGetRequest($id);
        try {
            // Call API with your client and get a response for your call
            return $client->execute($request)->result;
        } catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }

    public function authorizePayment($id)
    {
        $key = Services::request()->config->paypalClient;
        $secret = Services::request()->config->paypalSecret;
        $environment = ENVIRONMENT === 'production' ? new ProductionEnvironment($key, $secret) : new SandboxEnvironment($key, $secret);
        $client = new PayPalHttpClient($environment);
        $request = new OrdersAuthorizeRequest($id);
        try {
            // Call API with your client and get a response for your call
            return $client->execute($request)->result;
        } catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    }
}
