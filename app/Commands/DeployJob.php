<?php

namespace App\Commands;

use App\Entities\HostDeploy;
use App\Libraries\TemplateDeployer;
use App\Models\HostDeployModel;
use App\Models\HostModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Console;
use Symfony\Component\Yaml\Yaml;

/**
 * @codeCoverageIgnore
 */
class DeployJob extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'deploy';
    protected $description = 'Process Deployment';

    public function run(array $params)
    {
        /** @var HostDeploy */
        $deploy = (new HostDeployModel())->find($params[0]);
        if ($deploy) {
            try {
                $host = $deploy->host;
                set_time_limit($timeout = (($host->plan_id + 1) * 15));
                $template = Yaml::parse($deploy->template);
                $home = '~/public_html';
                if (isset($template['subdomain']) && is_string($template['subdomain']) && preg_match('/[a-zA-Z0-9-]+/', $template['subdomain'])) {
                    $deploy->domain = $template['subdomain'] . '.' . $deploy->domain;
                    $home = "~/domains/$deploy->domain/public_html";
                }
                $deploy->result = "Running in background with execution limit of {$timeout} seconds....\n";
                if ($deploy->hasChanged()) {
                    (new HostDeployModel())->save($deploy);
                }
                // doesn't work I think
                // }, $deploy->id);
                (new TemplateDeployer())->deploy(
                    $host->server->alias,
                    $deploy->domain,
                    $host->username,
                    $host->password,
                    $template,
                    $home,
                    $timeout,
                    function (string $x) use ($deploy) {
                        $deploy->result .= $x;
                        (new HostDeployModel())->save($deploy);
                    }
                );
                $_SERVER['finished'] = true;
            } catch (\Throwable $th) {
                $_SERVER['finished'] = true;
                $deploy->result .= 'Error: ' . $th;
            } finally {
                if ($host->status === 'starting') {
                    $host->status = 'active';
                    (new HostModel())->save($host);
                }
                $deploy->result = preg_replace('/^.+\n/', '', $deploy->result);
                (new HostDeployModel())->save($deploy);
                sleep(3); // let everything flushes
            }
        }
    }
}
