<?php

namespace App\Entities;

use App\Models\HostModel;
use CodeIgniter\Entity;

/**
 * @property int $id
 * @property int $host_id
 * @property string $domain
 * @property string $template
 * @property string $result
 * @property Host $host
 */
class HostDeploy extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'host_id' => 'integer',
        'domain' => 'string',
        'template' => 'string',
        'result' => 'string',
    ];

    /** @return Host */
    public function getHost()
    {
        return (new HostModel())->find($this->attributes['host_id']);
    }
}