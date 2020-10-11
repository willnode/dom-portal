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
use Symfony\Component\Yaml\Yaml;

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
            $template = Yaml::parse($deploy->template);
            $deploy->result = (new TemplateDeployer())->deploy(
                $host->server->alias,
                $deploy->domain,
                $host->username,
                $host->password,
                $template,
                ($host->plan_id + 1) * 300
            );
            if ($host->status === 'starting') {
                $host->status = 'active';
                (new HostModel())->save($host);
            }
            if (!empty($template['source'])) {
                // Mask password in the URL as
                // we don't have any business with it
                $tpass = parse_url($template['source'], PHP_URL_PASS);
                $deploy->template = str_replace($tpass, '****', $deploy->template);
            }
            (new HostDeploysModel())->save($deploy);
        }
    }
}
