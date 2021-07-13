<?php

namespace App\Commands;

use App\Entities\Host;
use App\Entities\HostStat;
use App\Entities\Server;
use App\Libraries\VirtualMinShell;
use App\Models\HostModel;
use App\Models\HostStatModel;
use App\Models\ServerModel;
use App\Models\ServerStatModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use Config\Services;
use DateTime;
use Symfony\Component\Yaml\Yaml;

/**
 * @codeCoverageIgnore
 */
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
        $vm = new VirtualMinShell();
        /** @var Server */
        foreach ((new ServerModel())->find() as $server) {
            $domains = $vm->listDomainsInfo($server->alias);
            $bandwidths = $vm->listBandwidthInfo($server->alias);
            /** @var Host[] */
            $hosts = (new HostModel())->atServer($server->id)->find();
            foreach ($hosts as $host) {
                if (!($domain = ($domains[$host->domain] ?? '')))
                    continue;
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
                    'bandwidths' => $bandwidths[$host->domain] ?? null,
                    'disabled' => $domain['Disabled'] ?? null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                if (!$stat) {
                    $stat = new HostStat();
                    $stat->fill($newStat);
                } else {
                    if ($stat->quota_net > $newStat['quota_net']) {
                        // Roll over time
                        $newaddons = calculateRemaining($stat->quota_net / 1024 / 1024, $host->addons, $plan->net * 1024 / 12);
                        log_message('notice', 'ROLLOVER ' . $newStat['domain'] . ': ' . json_encode([$stat->quota_net, $newStat['quota_net'], $host->addons, $newaddons]));
                        $host->addons = $newaddons;
                        $vm->adjustBandwidthHost(
                            ($host->addons + ($plan->net * 1024 / 12)),
                            $host->domain,
                            $server->alias
                        );
                    }
                    $stat->fill($newStat);
                }
                (new HostStatModel())->replace($stat->toRawArray());
                $expired = time() >= $host->expiry_at->getTimestamp();
                $overDisk = ($stat->quota_server) > $plan->disk * 1024 * 1024;
                $overBw = ($stat->quota_net) > $plan->net * 1024 * 1024 * 1024 / 12 + $host->addons * 1024 * 1024;
                // CLI::write('INJURY TIME ' . json_encode([$host->domain, $stat->disabled, $expired, $overDisk, $overBw]));
                $login = $host->login;
                Services::request()->setLocale($login->lang);
                if (!$stat->disabled) {
                    if ($overDisk) {
                        // Disable
                        $vm->disableHost($host->domain, $server->alias, 'Running out Disk Space');
                        $host->status = 'suspended';
                        sendEmail($login->email, lang('Email.suspendTitle'), view('email/suspend', [
                            'name' => $login->name,
                            'domain' =>  $host->domain,
                            'date' => date('Y-m-d H:i:s'),
                            'reason' => lang('Email.overDisk'),
                            'solution' => lang('Email.overDiskHint'),
                            'link' => base_url('user/host/detail/' . $host->id),
                        ]));
                    } else if ($overBw) {
                        // Disable
                        $vm->disableHost($host->domain, $server->alias, 'Running out Bandwidth');
                        $host->status = 'suspended';
                        sendEmail($login->email, lang('Email.suspendTitle'), view('email/suspend', [
                            'name' => $login->name,
                            'domain' =>  $host->domain,
                            'date' => date('Y-m-d H:i:s'),
                            'reason' => lang('Email.overBw'),
                            'solution' => lang('Email.overBwHint'),
                            'link' => base_url('user/host/detail/' . $host->id),
                        ]));
                    } else if ($expired) {
                        // Disable
                        $vm->disableHost($host->domain, $server->alias, 'Host is expired');
                        $host->status = 'expired';
                        sendEmail($login->email, lang('Email.suspendTitle'), view('email/suspend', [
                            'name' => $login->name,
                            'domain' =>  $host->domain,
                            'date' => $host->expiry_at->toDateString(),
                            'reason' => lang('Email.expired'),
                            'solution' => lang('Email.expiredHint'),
                            'link' => base_url('user/host/detail/' . $host->id),
                        ]));
                    } else {
                        $diff = (new Time())->difference($host->expiry_at);
                        $rangerem = $diff->getSeconds() <= 0 ? 0 : ($diff->getMonths() > 0 ? 0 : ($diff->getWeeks() > 0 ? 1 : 2));

                        if ($rangerem > 0 && $host->notification < $rangerem) {
                            sendEmail($login->email, lang('Email.remindTitle'), view('email/remind', [
                                'name' => $login->name,
                                'domain' =>  $host->domain,
                                'remaining' => humanize($host->expiry_at),
                                'expiry' => $host->expiry_at->toDateString(),
                                'reminder' => lang($rangerem == 1 ? 'Email.remind_1' : 'Email.remind_2'),
                                'link' => base_url('user/host/upgrade/' . $host->id),
                            ]));
                            $host->notification = $rangerem;
                        }
                    }
                } else {
                    if ((strtotime('-4 weeks', time()) >= $host->expiry_at->getTimestamp()) || ($stat->quota_server > $plan->disk * 1024 * 1024 * 3)) {
                        if (!$host->purchase) {
                            // Paid hosts should be immune from this, in case error logic happens...
                            $vm->delIpTablesLimit($host->username, $server->alias);
                            $vm->deleteHost($host->domain, $server->alias);
                            $host->status = 'removed';
                        }
                        // TODO: Deleted email
                    } else if (!($expired || $overDisk || $overBw || $host->status == 'banned')) {
                        // Enable
                        $vm->enableHost($host->domain, $server->alias);
                        $host->status = 'active';
                    }
                }
                if ($host->hasChanged()) {
                    (new HostModel())->save($host);
                }
            }
            // $yaml = $vm->listSystemInfo($server->alias);
            // $yaml = Yaml::parse($yaml);
            // $data = [
            //     'server_id' => $server->id,
            //     // php_yaml can't handle 64 bit ints properly
            //     'metadata' => json_encode($yaml),
            //     'updated_at' => date('Y-m-d H:i:s'),
            // ];
            // (new ServerStatModel())->replace($data);
            (new VirtualMinShell())->updateIpTables($server->alias);
        }
    }
}
