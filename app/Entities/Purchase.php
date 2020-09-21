<?php

namespace App\Entities;

use App\Models\HostModel;
use CodeIgniter\Entity;

/**
 * @property int $id
 * @property int $host_id
 * @property string $status
 * @property PurchaseMetadata $metadata
 * @property Host $host
 */
class Purchase extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'host_id' => 'integer',
        'status' => 'string',
        'metadata' => 'json',
    ];

    /** @return Host */
    public function getHost()
    {
        return (new HostModel())->find($this->attributes['host_id']);
    }
}