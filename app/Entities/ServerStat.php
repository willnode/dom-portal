<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int $server_id
 * @property mixed $metadata
 * @property Time $created_at
 * @property Time $updated_at
 */
class ServerStat extends Entity
{
    protected $casts = [
        'server_id' => 'integer',
        'metadata' => 'json',
    ];
}