<?php

namespace App\Libraries;

use App\Models\HostDeploysModel;
use phpseclib\Net\SSH2;
use Symfony\Component\Yaml\Yaml;

class TemplateDeployer
{
    public function schedule($host_id, $domain, $template)
    {
        if (!($template = trim($template))) {
            return;
        }
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
        // $config = [
        //     'debug' => 'false',
        //     'source' => '',
        //     'directory' => '',
        //     'root' => 'public_html',
        //     'nginx' => [],
        //     'features' => [],
        //     'commands' => [],
        // ];
        $config = Yaml::parse($template);
        $debug = $config['debug'] ?? false;
        $log = '';

        $ssh = new SSH2($server . '.domcloud.id');
        if (!$ssh->login($username, $password)) {
            $log .= 'CRITICAL: SSH login failed. Most procedure will not execute.';
            $ssh = null;
        } else {
            $ssh->setTimeout($timeout);
        }

        $log .= '#----- DEPLOYMENT STARTED -----#' . "\n";
        $log .= 'execution time in UTC: ' . time() . "\n\n";
        if (!empty($config['root'])) {
            $log .= '#----- Configuring web root -----#' . "\n";
            $res = (new VirtualMinShell())->modifyWebHome(trim($config['root'], ' /'), $domain, $server);
            if ($debug)
                $log .= $res;
            $log .= "\ndone\n";
        }
        if (!empty($path = $config['source']) && $ssh) {
            $log .= '#----- Fetching directory content from source -----#' . "\n";
            $directory = $config['directory'] ?? '';
            $tdomain = strtolower(parse_url($path, PHP_URL_HOST));
            $tscheme = strtolower(parse_url($path, PHP_URL_SCHEME));
            $tpath = parse_url($path, PHP_URL_PATH);
            $thash = parse_url($path, PHP_URL_FRAGMENT);
            $tpass = parse_url($path, PHP_URL_PASS);
            // Check if it was HTTP
            if ($tscheme === 'https' || $tscheme === 'http') {
                // expand clone/github/gitlab URLs
                if (substr_compare($tscheme, '.git', -strlen('.git')) === 0) {
                    // use git clone
                    $cloning = true;
                } else if ($tdomain === 'github.com' && preg_match('/^\/(\w+)(\/\w+)/', $tpath, $matches)) {
                    $thash = (strpos($thash, '#') === 0 ? substr($thash, 1) : $thash) ?: 'master';
                    $path = "https://github.com/$matches[1]$matches[2]/archive/$thash.zip";
                    $thash = (strpos($thash, 'v') === 0 ? substr($thash, 1) : $thash);
                    $directory = "$tpath-$thash";
                } else if ($tdomain === 'gitlab.com' && preg_match('/^\/(\w+)(\/\w+)/', $tpath, $matches)) {
                    $thash = (strpos($thash, '#') === 0 ? substr($thash, 1) : $thash) ?: 'master';
                    $path = "https://gitlab.com/$matches[1]$matches[2]/-/archive/$thash/$tpath-$thash.zip";
                    $thash = (strpos($thash, 'v') === 0 ? substr($thash, 1) : $thash);
                    $directory = "$tpath-$thash";
                }
                // check headers
                if (!isset($cloning) && array_search('Content-Type: application/zip', get_headers($path)) === false) {
                    $log .= "WARNING: The resource doesn't have Content-Type: application/zip header. Likely not a zip file.\n";
                }
                // build command
                $cmd = "cd ~/public_html ; rm -rf * .*  ; ";
                if (isset($cloning)) {
                    if ($directory) {
                        $directory = ' -b ' . $directory;
                    }
                    $cmd .= "git clone " . escapeshellarg($path) . $directory . " --depth 1 ; ";
                } else {
                    $cmd .= "wget -q -O _.zip " . escapeshellarg($path) . " ; ";
                    $cmd .= "unzip -q -o _.zip ; rm _.zip ; chmod -R 0750 * .* ; ";
                    if ($directory) {
                        $directory = sanitize_shell_arg_dir($directory);
                        $cmd .= "mv $directory/{.,}* . 2>/dev/null ; rmdir $directory ; ";
                    }
                }
                if ($debug) {
                    $log .= "$> $cmd\n\n";
                } else {
                    if ($tpass) {
                        $path = str_replace($tpass, '[password]', $path);
                    }
                    $log .= (isset($cloning) ? 'Cloning ' : 'Fetching ') . $path . "\n";
                }
                // execute
                $log .= $ssh->exec($cmd);
                $log .= "\ndone with exit code " . json_encode($ssh->getExitStatus() ?: 0) . "\n";
            } else {
                $log .= 'Error: unknown URL scheme. must be either HTTP or HTTPS' . "\n";
            }
        }
        if (!empty($config['features'])) {
            $log .= '#----- Applying features -----#' . "\n";
            foreach ($config['features'] as $feature) {
                $args = explode(' ', $feature);
                if (!$args) continue;
                switch ($args[0]) {
                    case 'mysql':
                        $dbname = ($username . '_' . ($args[1] ?? 'db'));
                        $log .= (new VirtualMinShell())->enableFeature($domain, $server, ['mysql']);
                        $log .= (new VirtualMinShell())->createDatabase($dbname, 'mysql', $domain, $server);
                        break;
                    case 'postgres':
                        $dbname = ($username . '_' . ($args[1] ?? 'db'));
                        $log .= (new VirtualMinShell())->enableFeature($domain, $server, ['postgres']);
                        $log .= (new VirtualMinShell())->createDatabase($dbname, 'postgres', $domain, $server);
                        break;
                    case 'ssl':
                        // SSL is enabled by default
                        if ($config['root'] && $ssh) {
                            $cmd = 'mkdir -m 0750 -p ~/' . sanitize_shell_arg_dir($config['root'] . '/.well-known');
                            if ($debug) {
                                $cmd = "$> $cmd\n";
                            }
                            $log .= $ssh->exec($cmd);
                        }
                        $log .= (new VirtualMinShell())->requestLetsEncrypt($domain, $server);
                        break;
                }
            }
        }
        if (!empty($config['nginx'])) {
            $log .= '#----- Applying NginX config -----#' . "\n";
            $res = (new VirtualMinShell)->setNginxConfig($domain, $server, json_encode($config['nginx']));
            if ($res) {
                $log .= "$res\n";
            } else {
                $log .= "NginX config applied\n";
            }
        }
        if (!empty($config['commands']) && $ssh) {
            $dbname = $dbname ?? $username.'_db';
            $log .= '#----- Executing commands -----#' . "\n";
            $cmd = "cd ~/public_html ; ";
            $cmd .= "DATABASE='$dbname' ; ";
            $cmd .= "DOMAIN='$domain' ; ";
            $cmd .= "USERNAME='$username' ; ";
            $cmd .= "PASSWORD='$password' ; ";
            $cmd .= implode(' ; ', $config['commands']);
            if ($debug) {
                $log .= "$> $cmd\n\n";
            }
            $log .= str_replace($password, '[password]', $ssh->exec($cmd));
            $log .= "\ndone with exit code " . json_encode($ssh->getExitStatus() ?: 0) . "\n";
        }
        $log .= '#----- DEPLOYMENT ENDED -----#' . "\n";
        $log .= "execution time: " . number_format(microtime(true) - $timing, 3) . " s";
        return str_replace("\0", "", $log);
    }
}
