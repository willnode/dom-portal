<?php

namespace App\Libraries;

use Config\Services;

class SendGridEmail
{
    public $sendGridUrl = 'https://api.sendgrid.com/v3/mail/send';
    public $sendGridSecret;
    public $sendGridTemplates;
    public $sendGridAsms;
    public $sendGridFromName;
    public $sendGridFromEmail;
    public static string $sentEmail = '';
    public static string $sentBody = '{}';


    public function __construct()
    {
        $this->sendGridSecret = Services::request()->config->sendGridSecret;
        $this->sendGridTemplates = Services::request()->config->sendGridTemplates;
        $this->sendGridAsms = array_map('intval', Services::request()->config->sendGridAsms);
        $this->sendGridFromName = Services::request()->config->sendGridFromName;
        $this->sendGridFromEmail = Services::request()->config->sendGridFromEmail;
    }

    public function send($id, $asm, $params)
    {
        if (!($template = $this->sendGridTemplates[$id][lang('Interface.code')] ?? '')) {
            if (ENVIRONMENT === 'production')
                return;
        }
        $ch = curl_init($this->sendGridUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->sendGridSecret
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body = json_encode(
            [
                "from" => [
                    "email" => $this->sendGridFromEmail,
                    "name" => $this->sendGridFromName,
                ],
                "template_id" => $template,
                "personalizations" => $params,
                "asm" => [
                    "group_id" => $this->sendGridAsms[$asm],
                    "groups_to_display" => array_values($this->sendGridAsms),
                ]
            ]
        )); // Set the posted fields

        if (ENVIRONMENT === 'production')
            // execute!
            curl_exec($ch);

        SendGridEmail::$sentEmail = $id;
        SendGridEmail::$sentBody = $body;

        log_message('notice',  $body);
    }
}
