<?php

namespace App\Entities;

use CodeIgniter\Entity;

/**
 * @property int id
 * @property int hosting_id
 * @property string status
 * @property string challenge
 * @property mixed[] metadata
 */
class Purchase extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'hosting_id' => 'integer',
        'status' => 'string',
        'challenge' => 'string',
        'metadata' => 'json-array',
    ];
}