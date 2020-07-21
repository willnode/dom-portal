<?php

namespace App\Commands;

use App\Models\VirtualMinShell;
use CodeIgniter\CLI\BaseCommand;
use Config\Database;

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
        $db = Database::connect();
        foreach ($db->table('slaves')->get()->getResult() as $slave) {
            $domains = (new VirtualMinShell())->listDomainsInfo($slave->slave_alias);
            $bandwidths = (new VirtualMinShell())->listBandwidthInfo($slave->slave_alias);
            $hostings = $db->table('hosting__display')->getWhere([
                'hosting_slave' => $slave->slave_id
            ])->getResult();
            foreach ($hostings as $hosting) {
                if ($domain = $domains[$hosting->domain_name]) {
                    $db->table('hosting__stat')->replace([
                        'hosting_id' => $hosting->hosting_id,
                        'identifier' => $domain['ID'],
                        'password' => $domain['Password'],
                        'quota_server' => $domain['Server byte quota used'],
                        'quota_user' => $domain['User byte quota used'],
                        'quota_db' => $domain['Databases byte size'],
                        'quota_net' => $domain['Bandwidth byte usage'],
                        'features' => $domain['Features'],
                        'bandwidths' => json_encode($bandwidths[$hosting->domain_name] ?? null),
                        'disabled' => $domain['Disabled'] ?? null,
                    ]);
                    if (!isset($domain['Disabled'])) {
                        if ($domain['Server byte quota used'] > $hosting->plan_disk * 1024 * 1024) {
                            // Disable
                            (new VirtualMinShell())->disableHosting($hosting->domain_name, $hosting->slave_alias, 'Quota exceeded');
                        }
                        if ($domain['Bandwidth byte usage'] > $hosting->plan_net * 1024 * 1024 * 1024) {
                            // Disable
                            (new VirtualMinShell())->disableHosting($hosting->domain_name, $hosting->slave_alias, 'Bandwidth exceeded');
                        }
                        if (time() >= strtotime($hosting->purchase_expired)) {
                            // Disable
                            (new VirtualMinShell())->disableHosting($hosting->domain_name, $hosting->slave_alias, 'Hosting expired');
                        }
                    } else {
                        if (strtotime('-2 weeks', time()) >= strtotime($hosting->purchase_expired)) {
                            // Delete
                            (new VirtualMinShell())->deleteHosting($hosting->domain_name, $hosting->slave_alias);
                        } else {
                            if (time() < strtotime($hosting->purchase_expired)) {
                                if ($domain['Bandwidth byte usage'] < $hosting->plan_net * 1024 * 1024 * 1024) {
                                    if ($domain['Server byte quota used'] > $hosting->plan_disk * 1024 * 1024) {
                                        // Enable
                                        (new VirtualMinShell())->enableHosting($hosting->domain_name, $hosting->slave_alias);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
