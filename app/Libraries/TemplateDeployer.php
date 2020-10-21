<?php

namespace App\Libraries;

use App\Models\HostDeployModel;
use phpseclib\Net\SSH2;

class TemplateDeployer
{
    public function schedule($host_id, $domain, $template)
    {
        if (!($template = trim($template))) {
            return;
        }
        $did = (new HostDeployModel())->insert([
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
    public function deploy($server, $domain, $username, $password, $config, $timeout)
    {
        $timing = microtime(true);
        $log = '';

        $ssh = new SSH2($server . '.domcloud.id');
        if (!$ssh->login($username, $password)) {
            $log .= 'CRITICAL: SSH login failed. Most procedure will not execute.';
            $ssh = null;
        } else {
            $ssh->setTimeout($timeout);
        }

        $log .= '#----- DEPLOYMENT STARTED -----#' . "\n";
        $log .= 'Time of execution in UTC: ' . date('Y-m-d H:i:s') . "\n\n";
        if (!empty($config['root'])) {
            $log .= '#----- CONFIGURING WEB ROOT -----#' . "\n";
            $log .= str_replace("\n\n", "\n", (new VirtualMinShell())->modifyWebHome(trim($config['root'], ' /'), $domain, $server));
        }
        if (!empty($config['source']) && $ssh) {
            $log .= '#----- OVERWRITING HOST FILES WITH SOURCE -----#' . "\n";
            $path = $config['source'];
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
                $cmd = "cd ~/public_html ; rm -rf * .* 2>/dev/null ; ";
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
                $log .= (isset($cloning) ? 'Cloning ' : 'Fetching ') . $path . "\n";
                $log .= $tpass ? str_replace($tpass, '[password]', "$> $cmd\n") : "$> $cmd\n";
                $path = $tpass ? str_replace($tpass, '[password]', $path) : $path;

                // execute
                $log .= $ssh->exec($cmd);
                $log .= "\nExit status: " . json_encode($ssh->getExitStatus() ?: 0) . "\n";
            } else {
                $log .= 'Error: unknown URL scheme. must be either HTTP or HTTPS' . "\n";
            }
        }
        if (!empty($config['nginx'])) {
            $log .= '#----- APPLYING NGINX CONFIG -----#' . "\n";
            $log .= '$> '.($nginx = json_encode($config['nginx']))."\n";
            $res = (new VirtualMinShell)->setNginxConfig($domain, $server, $nginx);
            if ($res) {
                $log .= "$res\nExit status: config discarded.\n";
            } else {
                $res = (new VirtualMinShell)->getNginxConfig($domain, $server);
                $log .= "$res\nExit status: config applied.\n";
            }
        }
        if (!empty($config['features'])) {
            $log .= '#----- APPLYING OPTIONAL FEATURES -----#' . "\n";
            foreach ($config['features'] as $feature) {
                $args = explode(' ', $feature);
                if (!$args) continue;
                switch ($args[0]) {
                    case 'mysql':
                        $dbname = ($username . '_' . ($args[1] ?? 'db'));
                        $log .= str_replace("\n\n", "\n", (new VirtualMinShell())->enableFeature($domain, $server, ['mysql']));
                        $log .= str_replace("\n\n", "\n", (new VirtualMinShell())->createDatabase($dbname, 'mysql', $domain, $server));
                        break;
                    case 'postgres':
                        $dbname = ($username . '_' . ($args[1] ?? 'db'));
                        $log .= str_replace("\n\n", "\n", (new VirtualMinShell())->enableFeature($domain, $server, ['postgres']));
                        $log .= str_replace("\n\n", "\n", (new VirtualMinShell())->createDatabase($dbname, 'postgres', $domain, $server));
                        break;
                    case 'ssl':
                        // SSL is enabled by default
                        if (isset($config['root']) && $ssh) {
                            $cmd = 'mkdir -m 0750 -p ~/' . sanitize_shell_arg_dir($config['root'] . '/.well-known');
                            $log .= "$> $cmd\n";
                            $log .= $ssh->exec($cmd);
                        }
                        $log .= str_replace("\n\n", "\n", (new VirtualMinShell())->requestLetsEncrypt($domain, $server));
                        break;
                }
            }
        }
        if (!empty($config['commands']) && $ssh) {
            $dbname = $dbname ?? $username . '_db';
            $log .= '#----- EXECUTING COMMANDS -----#' . "\n";
            $cmd = "cd ~/public_html ; ";
            $cmd .= "DATABASE='$dbname' ; ";
            $cmd .= "DOMAIN='$domain' ; ";
            $cmd .= "USERNAME='$username' ; ";
            $cmd .= "PASSWORD='$password' ; ";
            $cmd .= implode(' ; ', $config['commands']);
            $log .= str_replace($password, '[password]', "$> $cmd\n\n");
            $log .= str_replace($password, '[password]', $ssh->exec($cmd));
            $log .= "\nExit status: " . json_encode($ssh->getExitStatus() ?: 0) . "\n";
        }
        $log .= '#----- DEPLOYMENT ENDED -----#' . "\n";
        $log .= "execution time: " . number_format(microtime(true) - $timing, 3) . " s";
        return str_replace("\0", "", $log);
    }
}
