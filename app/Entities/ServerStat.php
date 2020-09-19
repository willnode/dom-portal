<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int $server_id
 * @property float $cpu_avg
 * @property int $ram_used
 * @property int $ram_total
 * @property int $ram_cache
 * @property int $swap_used
 * @property int $swap_total
 * @property int $disk_free
 * @property int $disk_total
 * @property int $httpd
 * @property int $fpm
 * @property int $bind
 * @property int $sshd
 * @property int $mysqld
 * @property int $postgres
 * @property Time $created_at
 * @property Time $updated_at
 */
class ServerStat extends Entity
{
    protected $casts = [
        'server_id' => 'integer',
        'cpu_avg' => 'float',
        'ram_used' => 'integer',
        'ram_total' => 'integer',
        'ram_cache' => 'integer',
        'swap_used' => 'integer',
        'swap_total' => 'integer',
        'disk_free' => 'integer',
        'disk_total' => 'integer',
        'httpd' => 'integer',
        'fpm' => 'integer',
        'bind' => 'integer',
        'sshd' => 'integer',
        'mysqld' => 'integer',
        'postgres' => 'integer',
    ];
}