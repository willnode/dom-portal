<?php

namespace App\Commands;

use App\Entities\HostDeploys;
use App\Libraries\TemplateDeployer;
use App\Libraries\VirtualMinShell;
use App\Models\HostDeploysModel;
use App\Models\HostModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class FetchTemplate extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'deploy';
    protected $description = 'Process Deployment';

    public function run(array $params)
    {
        set_time_limit(30 * 60);
        /** @var HostDeploys */
        $deploy = (new HostDeploysModel())->find($params[0]);
        if ($deploy) {
            $host = $deploy->host;
            $deploy->result = (new TemplateDeployer())->deploy(
                $host->server->alias,
                $deploy->domain,
                $host->username,
                $host->password,
                $deploy->template,
                ($host->plan_id + 1) * 300
            );
            if ($host->status === 'starting') {
                $host->status = 'active';
                (new HostModel())->save($host);
            }
            (new HostDeploysModel())->save($deploy);
        }
    }
}
