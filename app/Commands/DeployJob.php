<?php

namespace App\Commands;

use App\Entities\HostDeploy;
use App\Libraries\TemplateDeployer;
use App\Models\HostDeployModel;
use App\Models\HostModel;
use CodeIgniter\CLI\BaseCommand;
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
                set_time_limit($timeout = (($host->plan_id + 1) * 300));
                $template = Yaml::parse($deploy->template);
                $home = '~/public_html';
                if (isset($template['subdomain']) && is_string($template['subdomain']) && preg_match('/[a-zA-Z0-9-]+/', $template['subdomain'])) {
                    $deploy->domain = $template['subdomain'] . '.' . $deploy->domain;
                    $home = "~/domains/$deploy->domain/public_html";
                }
                $deploy->result = "Running in background with execution limit of {$timeout} seconds....";
                if ($deploy->hasChanged()) {
                    (new HostDeployModel())->save($deploy);
                }
                $result = '';
                $result = (new TemplateDeployer())->deploy(
                    $host->server->alias,
                    $deploy->domain,
                    $host->username,
                    $host->password,
                    $template,
                    $home,
                    $timeout
                );
                register_shutdown_function(function () use ($result, $deploy) {
                    if (!$result) {
                        $deploy->result = 'Sorry, this task didn\'t finish successfully due to emergency exit is triggered.';
                        (new HostDeployModel())->save($deploy);
                    }
                });
                $deploy->result = $result;
            } catch (\Throwable $th) {
                $deploy->result .= 'Error: ' . $th;
            } finally {
                if ($host->status === 'starting') {
                    $host->status = 'active';
                    (new HostModel())->save($host);
                }
                if (!empty($template['source'])) {
                    // Mask password in the URL (if any) as
                    // we don't have any business with it
                    $tpass = parse_url($template['source'], PHP_URL_PASS);
                    if ($tpass) {
                        $deploy->template = str_replace($tpass, '****', $deploy->template);
                    }
                }
                if ($deploy->hasChanged()) {
                    (new HostDeployModel())->save($deploy);
                }
            }
        }
    }
}
