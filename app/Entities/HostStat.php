<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int $host_id
 * @property string $domain
 * @property string $identifier
 * @property string $password
 * @property int $quota_server
 * @property int $quota_user
 * @property int $quota_db
 * @property int $quota_net
 * @property int $features
 * @property string $disabled
 * @property string[] bandwidths
 * @property Time $created_at
 * @property Time $updated_at
 */
class HostStat extends Entity
{
    protected $casts = [
        'host_id' => 'integer',
        'domain' => 'string',
        'identifier' => 'string',
        'password' => 'string',
        'quota_server' => 'integer',
        'quota_user' => 'integer',
        'quota_db' => 'integer',
        'quota_net' => 'integer',
        'features' => 'integer',
        'disabled' => 'string',
        'bandwidths' => 'json-array',
    ];
}