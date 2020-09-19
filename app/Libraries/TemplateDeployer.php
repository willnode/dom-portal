<?php

namespace App\Libraries;

use Config\Database;
use Config\Services;
use phpseclib\Net\SSH2;

class TemplateDeployer
{
    public function deploy($server, $domain, $username, $password, $template)
    {
        $tdomain = parse_url($template, PHP_URL_HOST);
        $configs = (Database::connect())->table('templates__index')->where('domain', $tdomain)->get()->getResult();
        $config = [
            'root' => null,
            'size' => 0,
            'features' => [],
            'commands' => [],
        ];
        foreach ($configs as $c) {
            if (preg_match("/$c->match/", $template)) {
                $config = json_decode((Database::connect())->table('templates')->where('id', $c->target)->get()->getRow()->metadata);
                break;
            }
        }
        if (count($config->commands) > 1) {
            $cmd = 'cd public_html; rm index.html; rmdir .well-known; ';
            $cmd .= 'wget -q -O __extract.zip ' . escapeshellarg($template) . ' ; ';
            $cmd .= 'unzip -q -o __extract.zip; rm __extract.zip; ';
            $cmd .= preg_replace_callback('/\$\{(\w+)\}/', function ($matches) use ($domain, $username, $password) {
                switch ($matches[1]) {
                    case 'DATABASE':
                        return $username . '_db';
                    case 'DOMAIN':
                        return $domain . '_db';
                    case 'USERNAME':
                        return $username;
                    case 'PASSWORD':
                        return $password;
                    default:
                        return $matches[0];
                }
            }, implode('; ', $config->commands));
            $ssh = new SSH2($server);
            if (!$ssh->login($username, $password)) {
                echo json_encode([$username, $password, $server, $cmd]);
                exit;
            }
            log_message('notice', $cmd);
            log_message('notice', $ssh->exec($cmd));
        }
    }
}
