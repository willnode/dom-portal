<?php

namespace App\Libraries;

use App\Models\HostDeploysModel;
use phpseclib\Net\SSH2;
use Symfony\Component\Yaml\Yaml;

class TemplateDeployer
{
    public function schedule($host_id, $domain, $template)
    {
        $did = (new HostDeploysModel())->insert([
            'host_id' => $host_id,
            'template' => $template,
            'domain' => $domain,
        ]);
        chdir(ROOTPATH);
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            pclose(popen("start /B start \"\" php spark deploy $did", "r"));
        } else {
            exec("php spark deploy $did  > /dev/null &");
        }
    }
    public function deploy($server, $domain, $username, $password, $template, $timeout)
    {
        $timing = microtime(true);
        $config = [
            'source' => '',
            'directory' => '${REPO}',
            'root' => 'public_html',
            'features' => [],
            'commands' => [],
        ];
        $config = array_replace_recursive($config, Yaml::parse($template));
        if (!($path = $config['source'] ?? '')) {
            return "Missing source path";
        }

        $tdomain = strtolower(parse_url($path, PHP_URL_HOST));
        $tpath = strtolower(parse_url($path, PHP_URL_PATH));
        $tscheme = strtolower(parse_url($path, PHP_URL_SCHEME));
        $thash = strtolower(parse_url($path, PHP_URL_FRAGMENT));

        if ($tscheme === 'github') {
            // Get tag
            $thash = (strpos($thash, '#') === 0 ? substr($thash, 1) : $thash) ?: 'master';
            // Replace with proper ZIP URL
            $config['source'] = "https://github.com/$tdomain$tpath/archive/$thash.zip";
            $thash = (strpos($thash, 'v') === 0 ? substr($thash, 1) : $thash);
            $config['directory'] = str_replace('${REPO}', "$tpath-$thash", $config['directory']);
        } else if ($tscheme === 'https' || $tscheme === 'http') {
            // ${REPO} flag is useless here
            $config['directory'] = str_replace('${REPO}', '', $config['directory']);
        } else {
            return "Unknown Target Scheme"; // Don't try to bother
        }
        if ($config['root'] ?? null) {
            (new VirtualMinShell())->modifyWebHome(trim($config['root'], ' /'), $domain, $server);
        }
        if (count($config['features']) > 0) {
            $features = array_intersect($config['features'], ['mysql', 'postgres', 'ssl']);
            (new VirtualMinShell())->enableFeature($domain, $server, $features);
            foreach ($config['features'] as $feature) {
                switch ($feature) {
                    case 'mysql':
                        (new VirtualMinShell())->createDatabase($username . '_db', 'mysql', $domain, $server);
                        break;
                    case 'postgres':
                        (new VirtualMinShell())->createDatabase($username . '_db', 'postgres', $domain, $server);
                        break;
                    case 'ssl':
                        $flag_enable_ssl = 1;
                        break;
                }
            }
        }
        // I know, this is a bit naive to check headers first before actually do SSH. But do we have options?
        if (array_search('Content-Type: application/zip', get_headers($config['source'])) !== false) {
            $path = escapeshellarg($config['source']);
            $cmd = 'rm -rf public_html/* ; cd public_html ; ';
            $cmd .= "wget -q -O __extract.zip $path ; ";
            $cmd .= 'unzip -q -o __extract.zip ; rm __extract.zip ; ';
            if ($config['directory']) {
                $dir = sanitize_shell_arg_dir($config['directory']);
                $cmd .= "mv $dir/{.,}* . 2>/dev/null ; rmdir $dir ; ";
            }
            $cmd .= 'chmod -R 0755 * ; ';
            if (count($config['commands']) > 0) {
                $cmd .= 'echo ==== execution started ==== ; ';
                $cmd .= preg_replace_callback(
                    '/\$\{(\w+)\}/',
                    function ($matches) use ($domain, $username, $password) {
                        switch ($matches[1]) {
                            case 'DATABASE':
                                return $username . '_db';
                            case 'DOMAIN':
                                return $domain;
                            case 'USERNAME':
                                return $username;
                            case 'PASSWORD':
                                return $password;
                            default:
                                return $matches[0];
                        }
                    },
                    implode(' ; ', $config['commands'])
                );
                if (isset($flag_enable_ssl)) {
                    $cmd .= ' ; mkdir -m 0755 $HOME/' . sanitize_shell_arg_dir($config['root']) . '/.well-known';
                }
                $cmd .= ' ; echo ==== execution finished ==== ';
            }
            $ssh = new SSH2($server . '.domcloud.id');
            if (!$ssh->login($username, $password)) {
                return "SSH Login failed";
            }
            $ssh->enableQuietMode();
            $ssh->setTimeout($timeout);
            $log = $ssh->exec($cmd);
            $log .= "\n\n=== log errors === \n";
            $log .= $ssh->getStdError();
            $log .= "\n=== end of log === \n";
            $log .= "\n\n exit code: " . json_encode($ssh->getExitStatus());
            $log .= "\n execution time: " . number_format(microtime(true) - $timing, 3) . " s";
            $log .= "\n executed commands: " . str_replace($password, '****MASKED****', $cmd);
            if (isset($flag_enable_ssl)) {
                (new VirtualMinShell())->requestLetsEncrypt($domain, $server);
            }
            return str_replace("\0", "", $log);
        } else {
            return "The resource doesn't have Content-Type: application/zip header. Likely not a zip file.";
        }
    }
}
