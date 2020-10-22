<?php

namespace App\Entities;

use App\Models\DomainModel;
use App\Models\HostModel;
use CodeIgniter\Entity;

/**
 * @property int $id
 * @property int|null $host_id
 * @property int|null $domain_id
 * @property string $status
 * @property PurchaseMetadata $metadata
 * @property Host|null $host
 * @property Domain|null $domain
 */
class Purchase extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'host_id' => '?integer',
        'domain_id' => '?integer',
        'status' => 'string',
    ];

    /** @return Host */
    public function getHost()
    {
        $id = $this->attributes['host_id'];
        return $id ? (new HostModel())->find($id) : null;
    }

     /** @return Domain */
     public function getDomain()
     {
         $id = $this->attributes['domain_id'];
         return $id ? (new DomainModel())->find($id) : null;
     }

    public function getMetadata()
    {
        return new PurchaseMetadata(json_decode($this->attributes['metadata'], true));
    }

    /** @param PurchaseMetadata $x */
    public function setMetadata($x)
    {
        $this->attributes['metadata'] = json_encode($x->toRawArray());
    }
}
