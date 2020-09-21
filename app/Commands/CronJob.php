<?php

namespace App\Commands;

use App\Entities\Host;
use App\Entities\HostStat;
use App\Entities\Server;
use App\Entities\ServerStat;
use App\Libraries\VirtualMinShell;
use App\Models\HostModel;
use App\Models\HostStatModel;
use App\Models\ServerModel;
use App\Models\ServerStatModel;
use CodeIgniter\CLI\BaseCommand;

require_once "spyc.php";

class CronJob extends BaseCommand
{
    protected $group       = 'demo';
    protected $name        = 'cronjob';
    protected $description = 'Do Scheduled Server Collection.';

    public function run(array $params)
    {
        /*
            Things to check:
            Updating quota info for users
            Slave usage + health data collection
			Collecting bandwidth usage for users
            Disabling users who exceeded their disk quota
			Disabling users who meets the expiration date
			Deleting users who not reactivating within two weeks
        */
        /** @var Server */
        foreach ((new ServerModel())->find() as $server) {
            $domains = (new VirtualMinShell())->listDomainsInfo($server->alias);
            $bandwidths = (new VirtualMinShell())->listBandwidthInfo($server->alias);
            /** @var Host[] */
            $hosts = (new HostModel())->atServer($server->id)->find();
            foreach ($hosts as $host) {
                if ($domain = ($domains[$host->domain] ?? '')) {
                    $stat = $host->stat;
                    $plan = $host->plan;
                    $newStat = [
                        'host_id' => $host->id,
                        'domain' => $host->domain,
                        'identifier' => $domain['ID'],
                        'password' => $domain['Password'],
                        'quota_server' => intval($domain['Server byte quota used']),
                        'quota_user' => intval($domain['User byte quota used']),
                        'quota_db' => intval($domain['Databases byte size'] ?? 0),
                        'quota_net' => intval($domain['Bandwidth byte usage'] ?? 0),
                        'features' => $domain['Features'],
                        'bandwidths' => json_encode($bandwidths[$host->domain] ?? null),
                        'disabled' => $domain['Disabled'] ?? null,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    if (!$stat) {
                        $stat = new HostStat($newStat);
                    } else {
                        if ($stat->quota_net > $newStat['quota_net']) {
                            // Roll over time
                            log_message('notice', 'ROLLOVER ' . $newStat['domain'] . ': ' . json_encode([$stat->quota_net, $newStat['quota_net']]));
                            $host->addons = max(0, $host->addons - (($newStat['quota_net'] / 1024 / 1024) - ($plan->net * 1024 / 12)));
                            (new VirtualMinShell())->adjustBandwidthHosting(
                                ($host->addons + ($plan->net * 1024 / 12)),
                                $host->domain,
                                $server->alias
                            );
                            (new HostModel())->save($host);
                        }
                        $stat->fill($newStat);
                    }
                    (new HostStatModel())->replace($stat->toRawArray());
                    $expired = time() >= strtotime($host->expiry_at->getTimestamp());
                    $overDisk = ($domain['Server byte quota used'] ?? 0) > $plan->disk * 1024 * 1024;
                    $overBw = ($domain['Bandwidth byte usage'] ?? 0) > $plan->net * 1024 * 1024 * 1024 / 12 + $host->addons * 1024 * 1024;
                    if (!empty($domain['Disabled'])) {
                        if ($overDisk) {
                            // Disable
                            (new VirtualMinShell())->disableHosting($host->domain, $server->alias, 'Running out Disk Space');
                        } else if ($overBw) {
                            // Disable
                            (new VirtualMinShell())->disableHosting($host->domain, $server->alias, 'Running out Bandwidth');
                        } else if ($expired) {
                            // Disable
                            (new VirtualMinShell())->disableHosting($host->domain, $server->alias, 'host expired');
                        }
                    } else {
                        if (strtotime('-2 weeks', time()) >= strtotime($host->expiry_at)) {
                            // Delete
                            (new VirtualMinShell())->deleteHosting($host->domain, $server->alias);
                            // TODO: Deleted email
                        } else {
                            if (!($expired || $overDisk || $overBw)) {
                                // Enable
                                (new VirtualMinShell())->enableHosting($host->domain, $server->alias);
                            }
                        }
                    }
                }
            }
            $data = (new ServerStat([
                'server_id' => $server->id,
                // php_yaml can't handle 64 bit ints properly
                'metadata' => spyc_load((new VirtualMinShell())->listSystemInfo($server->alias)),
                'updated_at' => date('Y-m-d H:i:s'),
            ]))->toRawArray();
            log_message('notice', json_encode($data));
            (new ServerStatModel())->replace($data);
        }
    }
}
