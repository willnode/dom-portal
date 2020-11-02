<?php

namespace App\Libraries;

use Config\Services;

class GitHubOAuth
{
    public $client;
    public $secret;
    public function __construct() {
        $this->client = Services::request()->config->githubClient;
        $this->secret = Services::request()->config->githubSecret;
    }
    public function verifyCode($code)
	{
		$ch = curl_init('https://github.com/login/oauth/access_token');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
		curl_setopt($ch, CURLOPT_POSTFIELDS, [
			'client_id' => $this->client,
			'client_secret' => $this->secret,
			'code' => $code,
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
        ]);
		// execute!
		$response = curl_exec($ch);
		curl_close($ch);
		if ($response) {
			return json_decode($response)->access_token ?? null;
		} else {
			return null;
		}
	}
    public function getUserInfo($token)
	{
		$ch = curl_init('https://api.github.com/user');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
			'Authorization: token '.$token,
			'User-Agent: DOMCloud.id'
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
    public function getConsentURL()
    {
        return 'https://github.com/login/oauth/authorize?scope=user:email&client_id='.urlencode($this->client);
    }
}