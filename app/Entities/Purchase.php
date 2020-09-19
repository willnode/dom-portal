<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int $id
 * @property int $host_id
 * @property string $status
 * @property string $challenge
 * @property mixed[] $metadata
 */
class Purchase extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'host_id' => 'integer',
        'status' => 'string',
        'challenge' => 'string',
        'metadata' => 'json',
    ];
}