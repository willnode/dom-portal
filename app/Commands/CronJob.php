<?php

namespace App\Commands;

use App\Entities\Host;
use App\Entities\HostStat;
use App\Entities\Server;
use App\Libraries\SendGridEmail;
use App\Libraries\VirtualMinShell;
use App\Models\HostModel;
use App\Models\HostStatModel;
use App\Models\ServerModel;
use App\Models\ServerStatModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
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
                if (!$stat->disabled) {
                    if ($overDisk) {
                        // Disable
                        $vm->disableHost($host->domain, $server->alias, 'Running out Disk Space');
                        $host->status = 'suspended';
                        (new SendGridEmail())->send('suspension_email', 'depletion', [[
                            'to' => [[
                                'email' => $login->email,
                                'name' => $login->name,
                            ]],
                            'dynamic_template_data' => [
                                'name' => $login->name,
                                'domain' =>  $host->domain,
                                'date' => date('Y-m-d H:i:s'),
                                'reason' => 'Kehabisan Ruang Disk',
                                'solution' => 'Anda dapat membuka berkas penyimpanan website lalu menghapus beberapa file yang besar. Hosting akan kembali aktif secara otomatis apabila ada cukup ruang satu jam mendatang.',
                                'url' => base_url('user/host/detail/'.$host->id),
                            ]
                        ]]);
                    } else if ($overBw) {
                        // Disable
                        $vm->disableHost($host->domain, $server->alias, 'Running out Bandwidth');
                        $host->status = 'suspended';
                        (new SendGridEmail())->send('suspension_email', 'depletion', [[
                            'to' => [[
                                'email' => $login->email,
                                'name' => $login->name,
                            ]],
                            'dynamic_template_data' => [
                                'name' => $login->name,
                                'domain' =>  $host->domain,
                                'date' => date('Y-m-d H:i:s'),
                                'reason' => 'Kehabisan Bandwidth',
                                'solution' => 'Anda dapat membeli tambahan (add-ons) untuk menutupi kekurangan bandwidth. Hosting akan kembali aktif apabila bandwidth tersedia lagi',
                                'url' => base_url('user/host/detail/'.$host->id),
                            ]
                        ]]);
                    } else if ($expired) {
                        // Disable
                        $vm->disableHost($host->domain, $server->alias, 'host expired');
                        $host->status = 'expired';
                        (new SendGridEmail())->send('suspension_email', 'billing', [[
                            'to' => [[
                                'email' => $login->email,
                                'name' => $login->name,
                            ]],
                            'dynamic_template_data' => [
                                'name' => $login->name,
                                'domain' =>  $host->domain,
                                'date' => $host->expiry_at->toDateString(),
                                'reason' => 'Melewati Batas Kadarluarsa',
                                'solution' => 'Anda dapat memperpanjang batas kadarluarsa sekarang agar tidak menjadi subjek penghapusan data permanen dalam beberapa pekan mendatang.',
                                'url' => base_url('user/host/detail/'.$host->id),
                            ]
                        ]]);
                    } else {
                        $diff = (new Time())->difference($host->expiry_at);
                        $rangerem = $diff->getSeconds() <= 0 ? 0 : ($diff->getMonths() > 0 ? 0 : ($diff->getWeeks() > 0 ? 1 : 2));

                        if ($rangerem > 0 && $host->notification < $rangerem) {
                            (new SendGridEmail())->send('reminder_email', 'billing', [[
                                'to' => [[
                                    'email' => $login->email,
                                    'name' => $login->name,
                                ]],
                                'dynamic_template_data' => [
                                    'name' => $login->name,
                                    'type' => 'Hosting',
                                    'domain' =>  $host->domain,
                                    'remaining' => humanize($host->expiry_at),
                                    'date' => $host->expiry_at->toDateString(),
                                    'repeat' => $rangerem == 1 ? 'Pesan ini akan diulang lagi apabila anda belum memperbarui ekspirasi 1 minggu dari jatuh tempo' : 'Pesan ini adalah peringatan terakhir sebelum pembelian anda jatuh tempo.',
                                    'extend_url' => base_url('user/host/upgrade/'.$host->id),
                                ]
                            ]]);
                            $host->notification = $rangerem;
                        }
                    }
                } else {
                    if ((strtotime('-4 weeks', time()) >= $host->expiry_at->getTimestamp()) || ($stat->quota_server > $plan->disk * 1024 * 1024 * 3)) {
                        if (!$host->purchase) {
                            // Paid hosts should be immune from this, in case error logic happens...
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
            $yaml = $vm->listSystemInfo($server->alias);
            $yaml = Yaml::parse($yaml);
            $data = [
                'server_id' => $server->id,
                // php_yaml can't handle 64 bit ints properly
                'metadata' => json_encode($yaml),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            (new ServerStatModel())->replace($data);
        }
    }
}
