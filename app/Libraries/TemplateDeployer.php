<?php

namespace App\Libraries;

use App\Models\HostDeployModel;
use CodeIgniter\CLI\CLI;
use phpseclib3\Net\SSH2;

/**
 * @codeCoverageIgnore
 */
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

    public function deploy($server, $domain, $username, $password, $config, $home, $timeout, $writer)
    {
        $timing = microtime(true);
        $log = '';
        $writeLog = function ($str) use ($writer, $log) {
            $log .= ($str);
            $writer($str);
        };
        $ssh = new SSH2($server . '.domcloud.id');
        if (!$ssh->login($username, $password)) {
            $writeLog('CRITICAL: SSH login failed. Most procedure will not execute.');
            $ssh = null;
        } else {
            $ssh->setTimeout($timeout);
            $ssh->enablePTY();
            // drop initial welcome msg
            $ssh->read('/\[.+?\@.+? .+?\]\$/', SSH2::READ_REGEX);
        }
        $queueTask = function (string $task, $password = null) use ($ssh, $writeLog) {
            $ssh->write($task . "\n");
            $read = $ssh->read('/\[.+?\@.+? .+?\]\$/', SSH2::READ_REGEX);
            $tmplog = $read;
            $tmplog = str_replace("\0", "", $tmplog);
            $tmplog = preg_replace('/\[.+?\]\$$/', '', $tmplog);
            $tmplog = str_replace("\r\n", "\n", $tmplog);
            $tmplog = preg_replace('/\r./', '', $tmplog);
            $tmplog = trim($tmplog);
            $tmplog = '$> ' . $tmplog . "\n";
            if ($password) {
                $tmplog = str_replace($password, '[password]', $tmplog);
            }
            $writeLog($tmplog);
        };

        $writeLog('#----- DEPLOYMENT STARTED -----#' . "\n");
        $writeLog('Time of execution in UTC: ' . date('Y-m-d H:i:s') . "\n\n");
        if (!empty($config['root'])) {
            $writeLog('#----- CONFIGURING WEB ROOT -----#' . "\n");
            $writeLog(str_replace("\n\n", "\n", (new VirtualMinShell())->modifyWebHome(trim($config['root'], ' /'), $domain, $server)));
        }
        if (!empty($config['features'])) {
            $writeLog('#----- APPLYING FEATURES -----#' . "\n");
            foreach ($config['features'] as $feature) {
                $args = null;
                if (is_string($feature)) {
                    $args = explode(' ', $feature);
                } else if (is_array($feature)) {
                    foreach ($feature as $key => $value) {
                        $args = array_merge([$key], explode(' ', $value));
                        break;
                    }
                }
                if (!$args) continue;
                switch ($args[0]) {
                    case 'dns':
                        if (count($args) == 1 || $args[1] == 'on') {
                            $writeLog(str_replace("\n\n", "\n", (new VirtualMinShell())->enableFeature($domain, $server, 'dns')));
                        } else if (count($args) == 2 && $args[1] == 'off') {
                            $writeLog(str_replace("\n\n", "\n", (new VirtualMinShell())->disableFeature($domain, $server, 'dns')));
                        }
                        break;
                    case 'mysql':
                        if (count($args) == 1 || $args[1] == 'on') {
                            $writeLog(str_replace("\n\n", "\n", (new VirtualMinShell())->enableFeature($domain, $server, 'mysql')));
                        } else if (count($args) == 2 && $args[1] == 'off') {
                            $writeLog(str_replace("\n\n", "\n", (new VirtualMinShell())->disableFeature($domain, $server, 'mysql')));
                        }
                        if (count($args) == 1 || $args[1] === 'create') {
                            $dbname = ($username . '_' . ($args[2] ?? $config['subdomain'] ?? 'db'));
                            $writeLog(str_replace("\n\n", "\n", (new VirtualMinShell())->createDatabase($dbname, 'mysql', $domain, $server)));
                        }
                        break;
                    case 'postgres':
                        if (count($args) == 1 || $args[1] == 'on') {
                            $writeLog(str_replace("\n\n", "\n", (new VirtualMinShell())->enableFeature($domain, $server, 'postgres')));
                        } else if (count($args) == 2 && $args[1] == 'off') {
                            $writeLog(str_replace("\n\n", "\n", (new VirtualMinShell())->disableFeature($domain, $server, 'postgres')));
                        }
                        if (count($args) == 1 || $args[1] === 'create') {
                            $dbname = ($username . '_' . ($args[2] ?? $config['subdomain'] ?? 'db'));
                            $writeLog(str_replace("\n\n", "\n", (new VirtualMinShell())->createDatabase($dbname, 'postgres', $domain, $server)));
                        }
                        break;
                    case 'ssl':
                        // SSL is enabled by default
                        if (isset($config['root']) && $ssh) {
                            $queueTask('mkdir -m 0750 -p ~/' . sanitize_shell_arg_dir($config['root'] . '/.well-known'));
                        }
                        $writeLog(str_replace("\n\n", "\n", (new VirtualMinShell())->requestLetsEncrypt($domain, $server)));
                        break;
                    case 'firewall':
                        if (count($args) == 1 || $args[1] == 'on') {
                            $writeLog("Adding user to firewall list\n");
                            $writeLog((new VirtualMinShell())->addIpTablesLimit($username, $server));
                        } else if (count($args) == 2 && $args[1] == 'off') {
                            if (str_ends_with(trim($config['root'] ?? '', '/'), '/public') || ($config['nginx']['fastcgi'] ?? '') == 'off') {
                                $writeLog("Removing user to firewall list\n");
                                $writeLog((new VirtualMinShell())->delIpTablesLimit($username, $server));
                            } else {
                                $writeLog("Can't remove user from firewall list due to unsatisfied condition.\n");
                            }
                        }
                }
            }
        }
        if (!empty($config['source']) && $ssh) {
            $writeLog('#----- OVERWRITING HOST FILES WITH SOURCE -----#' . "\n");
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
                if (substr_compare($tpath, '.git', -strlen('.git')) === 0) {
                    // use git clone
                    $cloning = true;
                    if ($directory) {
                        $directory = " -b " . $directory;
                    }
                    if (isset($config['args'])) {
                        $directory .= " " . $config['args']; // maybe couple finetuning
                    } else {
                        $directory .= " --depth 1"; // faster clone
                    }
                } else if (substr_compare($tpath, '.zip', -strlen('.zip')) === 0) {
                    // already a zip file
                } else if ($tdomain === 'github.com' && preg_match('/^\/([-_\w]+)\/([-_\w]+)/', $tpath, $matches)) {
                    $thash = (strpos($thash, '#') === 0 ? substr($thash, 1) : $thash) ?: 'master';
                    $path = "https://github.com/$matches[1]/$matches[2]/archive/$thash.zip";
                    $thash = (strpos($thash, 'v') === 0 ? substr($thash, 1) : $thash);
                    $directory = "$matches[2]-$thash";
                } else if ($tdomain === 'gitlab.com' && preg_match('/^\/([-_\w]+)\/([-_\w]+)/', $tpath, $matches)) {
                    $thash = (strpos($thash, '#') === 0 ? substr($thash, 1) : $thash) ?: 'master';
                    $path = "https://gitlab.com/$matches[1]/$matches[2]/-/archive/$thash/$tpath-$thash.zip";
                    $thash = (strpos($thash, 'v') === 0 ? substr($thash, 1) : $thash);
                    $directory = "$matches[2]-$thash";
                }
                // check headers
                if (!isset($cloning) && array_search('Content-Type: application/zip', get_headers($path)) === false) {
                    $writeLog("WARNING: The resource doesn't have Content-Type: application/zip header. Likely not a zip file.\n");
                }
                // build command
                $writeLog((isset($cloning) ? 'Cloning ' : 'Fetching ') . $path . "\n");
                $queueTask('cd ' . $home);
                $queueTask('rm -rf * .* 2>/dev/null');
                if (isset($cloning)) {
                    $queueTask("git clone " . escapeshellarg($path) . " ." . $directory, $tpass);
                } else {
                    $queueTask("wget -q -O _.zip " . escapeshellarg($path), $tpass);
                    $queueTask("unzip -q -o _.zip ; rm _.zip ; chmod -R 0750 * .*");
                    if ($directory) {
                        $directory = sanitize_shell_arg_dir($directory);
                        $queueTask("mv $directory/{.,}* . 2>/dev/null ; rmdir $directory");
                    }
                }
                $writeLog("\nDone\n");
            } else {
                $writeLog('Error: unknown URL scheme. must be either HTTP or HTTPS' . "\n");
            }
        }
        if (!empty($config['commands']) && $ssh) {
            $dbname = $dbname ?? $username . '_db';
            $writeLog('#----- EXECUTING COMMANDS -----#' . "\n");
            $queueTask("DATABASE='$dbname' ; DOMAIN='$domain' ; USERNAME='$username' ; PASSWORD='$password' ; cd $home", $password);
            foreach ($config['commands'] as $cmd) {
                $queueTask($cmd);
                if ($ssh->getExitStatus() ?: 0) {
                    $writeLog('Exit status: ' . $ssh->getExitStatus() . "\n");
                }
            }
            $writeLog("Done\n");
        }
        if (!empty($config['nginx'])) {
            $writeLog('#----- APPLYING NGINX CONFIG -----#' . "\n");
            $writeLog('$> ' . ($nginx = json_encode($config['nginx'])) . "\n");
            $res = (new VirtualMinShell)->setNginxConfig($domain, $server, $nginx);
            if ($res) {
                $writeLog("$res\nExit status: config discarded.\n");
            } else {
                $res = (new VirtualMinShell)->getNginxConfig($domain, $server);
                $writeLog("$res\nExit status: config applied.\n");
            }
        }
        if (($config['root'] ?? '') || ($config['nginx'] ?? '')) {
            if (!str_ends_with(trim($config['root'] ?? '', '/'), '/public') && ($config['nginx']['fastcgi'] ?? '') != 'off') {
                if ((new VirtualMinShell())->checkIpTablesLimit($username, $server) === 0) {
                    $writeLog("Firewall condition breaks! Making sure user is on firewall back.\n");
                    $writeLog((new VirtualMinShell())->addIpTablesLimit($username, $server));
                }
            }
        }
        $writeLog('#----- DEPLOYMENT ENDED -----#' . "\n");
        $writeLog("execution time: " . number_format(microtime(true) - $timing, 3) . " s");
        return str_replace("\0", "", $log);
    }
}
