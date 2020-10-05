<?php

namespace App\Libraries;

use Config\Services;

class TransferWiseGate
{
    private $url;
    private $secret;
    private $pid;

    public function __construct()
    {
        $conf = Services::request()->config;
        $this->url = $conf->transferWiseURL;
        $this->secret = $conf->transferWiseSecret;
        $this->pid = $conf->transferWisePID;
    }

    public function sign($json, $signature)
    {
        // https://api-docs.transferwise.com/#webhook-events-event-http-requests
        $pub_key = "-----BEGIN PUBLIC KEY-----\n";
        $pub_key .= 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvO8vXV+JksBzZAY6GhSO
XdoTCfhXaaiZ+qAbtaDBiu2AGkGVpmEygFmWP4Li9m5+Ni85BhVvZOodM9epgW3F
bA5Q1SexvAF1PPjX4JpMstak/QhAgl1qMSqEevL8cmUeTgcMuVWCJmlge9h7B1CS
D4rtlimGZozG39rUBDg6Qt2K+P4wBfLblL0k4C4YUdLnpGYEDIth+i8XsRpFlogx
CAFyH9+knYsDbR43UJ9shtc42Ybd40Afihj8KnYKXzchyQ42aC8aZ/h5hyZ28yVy
Oj3Vos0VdBIs/gAyJ/4yyQFCXYte64I7ssrlbGRaco4nKF3HmaNhxwyKyJafz19e
HwIDAQAB';
        $pub_key .= "\n-----END PUBLIC KEY-----";
        return openssl_verify($json, base64_decode($signature, true), $pub_key, OPENSSL_ALGO_SHA1);
    }

    public function getTransferInfo($id)
    {
        $ch = curl_init($this->url . 'transfers/' . $id);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secret
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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
