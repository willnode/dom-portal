<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int $id
 * @property string $alias
 * @property string $ip
 * @property string $domain
 * @property int $scheme_id
 * @property int $capacity
 * @property int $public
 */
class Server extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'alias' => 'string',
        'ip' => 'string',
        'domain' => 'string',
        'capacity' => 'integer',
        'public' => 'integer',
    ];
}